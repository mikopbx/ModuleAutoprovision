<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 9 2020
 */

namespace Modules\ModuleAutoprovision\Lib;

use MikoPBX\Common\Models\Extensions;
use MikoPBX\Common\Models\Sip;
use MikoPBX\Core\Asterisk\AGI;
use MikoPBX\Core\System\MikoPBXConfig;
use MikoPBX\Core\System\Network;
use MikoPBX\Core\System\Util;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionDevice;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionUsers;
use Phalcon\Di\Injectable;

class Autoprovision extends Injectable
{
    protected string $tempDir;
    protected MikoPBXConfig $mikoPBXConfig;

    public function __construct()
    {
        $this->tempDir       = (string)$this->di->getShared('config')->path('core.tempDir');
        $this->mikoPBXConfig = new MikoPBXConfig();
    }

    /**
     * Generates the vendor-specific provisioning config file for the phone identified by $req_data['mac'].
     *
     * @param array $req_data Provisioning request payload (must include 'mac' and 'vendor').
     * @return string Absolute path to the generated config, or '' if the phone is unknown.
     */
    public function generateConfigPhone(array $req_data): string
    {
        $mac = (string)($req_data['mac'] ?? '');
        if ($mac === '') {
            return '';
        }

        $phoneData = ModuleAutoprovisionDevice::findFirst([
            'mac = :mac:',
            'bind' => ['mac' => $mac],
        ]);
        if ($phoneData === null) {
            return '';
        }

        $sipData  = [];
        $userData = ModuleAutoprovisionUsers::find([
            'id_phone = :id_phone:',
            'bind' => ['id_phone' => $phoneData->id],
        ]);
        foreach ($userData as $row) {
            $exten = Extensions::findFirst([
                'userid = :userid: AND type = :type:',
                'bind' => ['userid' => $row->userid, 'type' => 'SIP'],
            ]);
            if ($exten === null) {
                continue;
            }
            $sip = Sip::findFirst([
                'extension = :extension:',
                'bind' => ['extension' => $exten->number],
            ]);
            if ($sip === null) {
                continue;
            }
            $sipData[$row->line] = [
                'extension' => $sip->extension,
                'secret'    => $sip->secret,
                'callerid'  => $exten->callerid,
            ];
        }

        if (empty($sipData)) {
            $defPeer = [
                'extension' => AutoprovisionConf::SIP_USER,
                'secret'    => AutoprovisionConf::getSipSecret(),
                'callerid'  => AutoprovisionConf::SIP_USER,
            ];
            $sipData['1'] = $defPeer;
            if (($req_data['model'] ?? '') === 'W52P') {
                // W52P DECT base station: configure 5 SIP accounts using the default peer.
                $sipData['2'] = $defPeer;
                $sipData['3'] = $defPeer;
                $sipData['4'] = $defPeer;
                $sipData['5'] = $defPeer;
            }
        }

        $confManager = match ($req_data['vendor'] ?? '') {
            'yealink' => new AutoprovisionYealink(),
            'fanvil'  => new AutoprovisionFanvil(),
            'snom'    => new AutoprovisionSnom(),
            default   => null,
        };
        if ($confManager === null) {
            return '';
        }

        return $confManager->generateConfig($req_data, $sipData);
    }

    /**
     * Sends a SIP NOTIFY with Event: check-sync;reboot=true to the given phone.
     */
    public function clientNotifyReboot(string $ipPhone, int $portPhone, string $eth): void
    {
        if (!filter_var($ipPhone, FILTER_VALIDATE_IP) || $portPhone <= 0 || $portPhone > 65535) {
            return;
        }

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            return;
        }

        $net          = new Network();
        $ethInterface = $net->getInterface($eth);
        $ipPbx        = (string)($ethInterface['ipaddr'] ?? '');
        $portPbx      = (string)$this->mikoPBXConfig->getGeneralSettings('SIPPort');
        $phoneUser    = AutoprovisionConf::SIP_USER;

        $msg = "NOTIFY sip:{$phoneUser}@{$ipPhone}:{$portPhone};ob SIP/2.0\r\n" .
            "Via: SIP/2.0/UDP {$ipPbx}:{$portPbx};branch=z9hG4bK12fd4e5c;rport\r\n" .
            "Max-Forwards: 70\r\n" .
            "From: \"asterisk\" <sip:asterisk@{$ipPbx}>;tag=as54cd2be9\r\n" .
            "To: <sip:{$phoneUser}@{$ipPhone}:{$portPhone};ob>\r\n" .
            "Contact: <sip:asterisk@{$ipPbx}:{$portPbx}>\r\n" .
            "Call-ID: 4afab6ce2bff0be11a4af41064340242@{$ipPbx}:{$portPbx}\r\n" .
            "CSeq: 102 NOTIFY\r\n" .
            "User-Agent: mikopbx\r\n" .
            "Allow: INVITE, ACK, CANCEL, OPTIONS, BYE, REFER, SUBSCRIBE, NOTIFY, INFO, PUBLISH, MESSAGE\r\n" .
            "Supported: replaces, timer\r\n" .
            "Subscription-State: terminated\r\n" .
            "Event: check-sync;reboot=true\r\n" .
            "Content-Length: 0\r\n\n";

        socket_sendto($sock, $msg, strlen($msg), 0, $ipPhone, $portPhone);
        socket_close($sock);
    }

    /**
     * AGI handler executed by the autoprovision dialplan context.
     * Looks up the calling phone, binds its MAC to the dialing extension,
     * and triggers a reboot so the phone re-fetches its config.
     */
    public function StartAGIProvision(): void
    {
        $agi = new AGI();
        $row = (string)$agi->get_variable('PT1C_VIA', true);
        // Match an IPv4 address with an optional :port suffix. The dot is escaped properly.
        preg_match_all('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}):?(\d*)/m', $row, $matches);
        if (empty($matches[1][0])) {
            return;
        }
        $ip   = $matches[1][0];
        $port = (int)($matches[2][0] ?: 0);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return;
        }

        $agiExtension = (string)($agi->request['agi_extension'] ?? '');
        $tmpData      = explode('*', $agiExtension);
        $sipId        = (string)array_pop($tmpData);
        $agi->noop("{$agiExtension}  {$sipId}");

        $exten = Extensions::findFirst([
            'number = :number:',
            'bind' => ['number' => $sipId],
        ]);
        if ($exten === null) {
            $agi->set_variable('PROVISION_STATUS', 'EXTEN_NOT_FOUND');
            return;
        }

        // Populate ARP table by pinging the phone (escape the IP to avoid shell injection).
        $safeIp = escapeshellarg($ip);
        exec("timeout -t 1 ping {$safeIp} -c 1");

        $arp = Util::which('arp');
        $awk = Util::which('awk');
        exec("{$arp} -D {$safeIp} -n | {$awk}  '{ print \$4 \" \" \$7}' 2>&1", $out);

        [$mac, $eth] = array_pad(explode(' ', $out[0] ?? ''), 2, '');
        $mac = str_replace(':', '', $mac);
        $agi->noop('arp - ' . implode('', $out) . '.');

        $phoneData = null;
        if ($eth !== '' && $mac !== '') {
            $phoneData = ModuleAutoprovisionDevice::findFirst([
                'mac = :mac:',
                'bind' => ['mac' => $mac],
            ]);
        }
        $agi->noop("eth - {$eth}; mac - {$mac}");
        if ($phoneData === null) {
            // The phone may have changed its IP — fall back to ARP-table lookup by host.
            $phoneData = ModuleAutoprovisionDevice::findFirst([
                'host = :host:',
                'bind' => ['host' => $ip],
            ]);
        }

        if ($phoneData === null || empty($phoneData->mac)) {
            // Log and let fail2ban handle repeated provisioning attempts from unknown phones.
            $agi->set_variable('PROVISION_STATUS', 'PHONE_NOT_FOUND');
            return;
        }

        if (stripos((string)$phoneData->manufacturer_model, 'W52P') !== false) {
            // For a DECT handset, the line number comes from the Call-ID header.
            $callId = (string)$agi->get_variable('SIP_HEADER(Call-ID)', true);
            $agi->noop("call_id - {$callId}.");
            // Line numbers start at 0 — Call-ID format: "0_47643482@172.16.32.59".
            $params = explode(' ', $callId);
            $line   = (count($params) > 1) ? ((int)$params[0] + 1) : 1;
        } else {
            $line = 1;
        }

        $provUser = ModuleAutoprovisionUsers::findFirst([
            'id_phone = :id_phone: AND line = :line:',
            'bind' => ['id_phone' => $phoneData->id, 'line' => (string)$line],
        ]);
        if ($provUser === null) {
            $provUser           = new ModuleAutoprovisionUsers();
            $provUser->id_phone = $phoneData->id;
        }
        $provUser->line   = (string)$line;
        $provUser->userid = $exten->userid;
        $provUser->save();

        $agi->set_variable('PROVISION_STATUS', 'OK');

        // Reboot the phone so it pulls the freshly generated config.
        $this->clientNotifyReboot($ip, $port, $eth);
    }

    /**
     * Parses an INI-like blob of vendor-specific overrides keyed by section name.
     *
     * The input may be base64-encoded. We only treat it as base64 when the strict_types
     * round-trip succeeds and the decoded payload contains an INI section marker — otherwise
     * arbitrary base64-shaped strings would be silently rewritten.
     *
     * @param string $manualAttributes Either raw INI text or its base64-encoded form.
     * @return array<string, string> Map of section name to raw section body.
     */
    public static function parseIniSettings(string $manualAttributes): array
    {
        $decoded = base64_decode($manualAttributes, true);
        if ($decoded !== false && base64_encode($decoded) === $manualAttributes && str_contains($decoded, '[')) {
            $manualAttributes = $decoded;
        }

        $tmpArr = explode("\n", $manualAttributes);
        foreach ($tmpArr as &$row) {
            $row = trim($row);
            $pos = strpos($row, ']');
            if ($pos !== false && strpos($row, '[') === 0) {
                $row = "\n" . substr($row, 0, $pos);
            }
        }
        unset($row);
        $manualAttributes = implode("\n", $tmpArr);

        $manualData = [];
        $sections   = explode("\n[", str_replace(']', '', $manualAttributes));
        foreach ($sections as $section) {
            $dataRows    = explode("\n", trim($section));
            $sectionName = trim($dataRows[0] ?? '');
            if ($sectionName === '') {
                continue;
            }
            unset($dataRows[0]);
            $manualData[$sectionName] = implode("\n", $dataRows);
        }
        return $manualData;
    }
}
