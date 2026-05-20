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
use MikoPBX\Core\System\SystemMessages;
use Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Repository as FirmwareRepository;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionDevice;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionUsers;
use Modules\ModuleAutoprovision\Models\Templates;
use Modules\ModuleAutoprovision\Models\TemplatesUsers;
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

        // Resolve {FIRMWARE_URL} once and surface it to every vendor generator
        // through $req_data so the per-vendor classes emit the correct config
        // key ('firmware.url' for Yealink, '<AUTOUPDATE>FirmwareUpgrade' for
        // Fanvil, P192/P237 for Grandstream, 'auto_image_url' for Htek, etc.).
        // Empty string when no matching firmware row exists — generators must
        // suppress the line in that case.
        $req_data['firmware_url'] = FirmwareRepository::resolveFirmwareUrl(
            (string)($req_data['vendor'] ?? ''),
            isset($req_data['model']) ? (string)$req_data['model'] : null
        );

        $confManager = match ($req_data['vendor'] ?? '') {
            'yealink'     => new AutoprovisionYealink(),
            'fanvil'      => new AutoprovisionFanvil(),
            'snom'        => new AutoprovisionSnom(),
            'grandstream' => new AutoprovisionGrandstream(),
            'htek'        => new AutoprovisionHtek(),
            default       => null,
        };
        if ($confManager === null) {
            return '';
        }

        return $confManager->generateConfig($req_data, $sipData);
    }

    /**
     * Renders the per-MAC `TemplatesUsers`-bound config for `$mac` and returns
     * the body as a string, or null when no template is mapped to the MAC.
     *
     * Shared by both the HTTP per-MAC endpoint (`GetController::getConfigStatic`)
     * and the TFTP server (`WorkerTftpServer::resolveFile`) so the two channels
     * produce byte-identical output for the same MAC. The legacy
     * `generateConfigPhone()` multi-account flow remains the fallback when no
     * mapping exists.
     *
     * Placeholder pipeline (order matters):
     *   1. `{PBX_HOST}` / `{FIRMWARE_URL}` — generic, evaluated once.
     *   2. `{SIP_NUM}` / `{SIP_USER_NAME}` / `{SIP_PASS}` — per-user, looked up
     *      from `TemplatesUsers.userId` -> `Extensions` + `Sip`.
     */
    public static function renderTemplateConfig(string $mac, string $userAgent, ?string $vendorHint = null): ?string
    {
        $mac = strtolower(trim($mac));
        if ($mac === '') {
            return null;
        }
        $di = \Phalcon\Di\Di::getDefault();
        if ($di === null) {
            return null;
        }
        $manager = $di->get('modelsManager');

        $parameters = [
            'models'     => [
                'TemplatesUsers' => TemplatesUsers::class,
            ],
            'conditions' => ':mac: LIKE TemplatesUsers.mac',
            'bind'       => ['mac' => $mac],
            'columns'    => [
                'id'       => 'TemplatesUsers.id',
                'template' => 'Templates.template',
                'userId'   => 'TemplatesUsers.userId',
            ],
            'limit'      => 1,
            'joins'      => [
                'TemplatesUsers' => [
                    0 => Templates::class,
                    1 => 'Templates.id = TemplatesUsers.templateId',
                    2 => 'Templates',
                    3 => 'LEFT',
                ],
            ],
        ];
        $row = $manager->createBuilder($parameters)->getQuery()->execute()->toArray();
        if (empty($row)) {
            return null;
        }
        $template = (string)($row[0]['template'] ?? '');
        if ($template === '') {
            return null;
        }

        // Lookup SIP credentials for the bound user (matches the per-MAC branch in
        // GetController::getConfigStatic).
        $sipParameters = [
            'models'     => [
                'Extensions' => Extensions::class,
            ],
            'conditions' => ':userid: = Extensions.userid',
            'bind'       => ['userid' => $row[0]['userId']],
            'columns'    => [
                '{SIP_USER_NAME}' => 'Extensions.callerid',
                '{SIP_NUM}'       => 'Extensions.number',
                '{SIP_PASS}'      => 'Sip.secret',
            ],
            'ORDER'      => 'mac DESC',
            'limit'      => 1,
            'joins'      => [
                'Extensions' => [
                    0 => Sip::class,
                    1 => 'Extensions.number = Sip.extension',
                    2 => 'Sip',
                    3 => 'LEFT',
                ],
            ],
        ];
        $sipRow = $manager->createBuilder($sipParameters)->getQuery()->execute()->toArray();
        $sipData = ['{SIP_NUM}' => '', '{SIP_USER_NAME}' => '', '{SIP_PASS}' => ''];
        if (!empty($sipRow)) {
            $sipData = $sipRow[0];
        }

        // Vendor/model lookup for {FIRMWARE_URL}. Resolution order:
        //   1. User-Agent header (HTTP path — phones always include their vendor
        //      string there).
        //   2. Device record's manufacturer_model (populated by PnP discovery).
        //   3. Caller-supplied $vendorHint (TFTP path: WorkerTftpServer infers it
        //      from the filename prefix before PnP has recorded the device).
        // Without (3), a TFTP request for a manually-mapped MAC whose device row
        // has no manufacturer_model yet would yield vendor='' and {FIRMWARE_URL}=''
        // even though the HTTP path could resolve it. That would re-introduce the
        // HTTP/TFTP byte-identity drift this renderer exists to prevent.
        $vendor = self::detectVendorFromUserAgent($userAgent);
        $model  = null;
        $device = ModuleAutoprovisionDevice::findFirst([
            'mac = :mac:',
            'bind' => ['mac' => $mac],
        ]);
        if ($device !== null && !empty($device->manufacturer_model)) {
            $mm = (string)$device->manufacturer_model;
            if ($vendor === '') {
                $mmLower = strtolower($mm);
                foreach (array_keys(FirmwareRepository::VENDOR_EXTENSIONS) as $candidate) {
                    if (str_contains($mmLower, $candidate)) {
                        $vendor = $candidate;
                        break;
                    }
                }
            }
            $model = self::extractModel($mm);
        }
        if ($vendor === '' && $vendorHint !== null) {
            $hint = strtolower(trim($vendorHint));
            if ($hint !== '' && isset(FirmwareRepository::VENDOR_EXTENSIONS[$hint])) {
                $vendor = $hint;
            }
        }

        return self::applyGenericPlaceholders($template, $vendor, $model, $sipData);
    }

    /**
     * Resolves the host phones should reach the PBX on. Operator-supplied value in
     * m_ModuleAutoprovision.pbx_host wins; if blank, falls back to the first
     * non-loopback IPv4 of an up network interface so a fresh install works without
     * the operator filling that field. Returns '' only when the PBX has no usable
     * interface — at which point the broken NOTIFY URL is the least of the worries.
     */
    public static function resolvePbxHost(): string
    {
        $settings   = ModuleAutoprovision::findFirst();
        $configured = $settings !== null ? trim((string)($settings->pbx_host ?? '')) : '';
        if ($configured !== '') {
            return $configured;
        }
        $net = new Network();
        foreach ($net->getInterfacesNames() as $iface) {
            $info = $net->getInterface($iface);
            $ip   = trim((string)($info['ipaddr'] ?? ''));
            if ($ip !== '' && $ip !== '127.0.0.1' && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return '';
    }

    /**
     * Looks up SIP credentials of the user mapped to $mac via m_TemplatesUsers
     * and returns the substitution array (`{SIP_NUM}` etc.) ready for str_replace,
     * or an empty array if no mapping exists. Extracted from renderTemplateConfig
     * so the URI-template HTTP branch can reuse it for source-IP autodetection.
     *
     * @return array<string,string>
     */
    public static function lookupSipDataForMac(string $mac): array
    {
        $mac = strtolower(trim($mac));
        if ($mac === '') {
            return [];
        }
        $di = \Phalcon\Di\Di::getDefault();
        if ($di === null) {
            return [];
        }
        $manager = $di->get('modelsManager');

        $mapping = $manager->createBuilder([
            'models'     => ['TemplatesUsers' => TemplatesUsers::class],
            'conditions' => ':mac: LIKE TemplatesUsers.mac',
            'bind'       => ['mac' => $mac],
            'columns'    => ['userId' => 'TemplatesUsers.userId'],
            'limit'      => 1,
        ])->getQuery()->execute()->toArray();
        if (empty($mapping) || empty($mapping[0]['userId'])) {
            return [];
        }

        $sipRow = $manager->createBuilder([
            'models'     => ['Extensions' => Extensions::class],
            'conditions' => ':userid: = Extensions.userid',
            'bind'       => ['userid' => $mapping[0]['userId']],
            'columns'    => [
                '{SIP_USER_NAME}' => 'Extensions.callerid',
                '{SIP_NUM}'       => 'Extensions.number',
                '{SIP_PASS}'      => 'Sip.secret',
            ],
            'limit'      => 1,
            'joins'      => [
                'Extensions' => [Sip::class, 'Extensions.number = Sip.extension', 'Sip', 'LEFT'],
            ],
        ])->getQuery()->execute()->toArray();
        if (empty($sipRow)) {
            return [];
        }
        return [
            '{SIP_USER_NAME}' => (string)($sipRow[0]['{SIP_USER_NAME}'] ?? ''),
            '{SIP_NUM}'       => (string)($sipRow[0]['{SIP_NUM}'] ?? ''),
            '{SIP_PASS}'      => (string)($sipRow[0]['{SIP_PASS}'] ?? ''),
        ];
    }

    /**
     * Substitutes the generic ({PBX_HOST}, {FIRMWARE_URL}) and per-user ({SIP_*})
     * placeholders into a template body. Generic pass runs first so a template
     * authoring {FIRMWARE_URL} inside a {SIP_*} block still renders correctly.
     *
     * @param array<string,string> $sipReplacements
     */
    public static function applyGenericPlaceholders(
        string $template,
        string $vendor,
        ?string $model,
        array $sipReplacements = []
    ): string {
        $generic = [
            '{FIRMWARE_URL}' => FirmwareRepository::resolveFirmwareUrl($vendor, $model),
            '{PBX_HOST}'     => self::resolvePbxHost(),
        ];
        $rendered = strtr($template, $generic);
        if (!empty($sipReplacements)) {
            $rendered = str_replace(
                array_keys($sipReplacements),
                array_values($sipReplacements),
                $rendered
            );
        }
        return $rendered;
    }

    /**
     * Best-effort vendor detection from the phone's User-Agent header.
     */
    public static function detectVendorFromUserAgent(string $userAgent): string
    {
        $ua = strtolower($userAgent);
        foreach (['yealink', 'snom', 'fanvil', 'grandstream', 'htek'] as $vendor) {
            if ($ua !== '' && str_contains($ua, $vendor)) {
                return $vendor;
            }
        }
        return '';
    }

    /**
     * Extracts the model name from `ModuleAutoprovisionDevice.manufacturer_model`.
     * Mirrors GetController::extractModel — kept here so the TFTP path doesn't
     * have to depend on the HTTP controller.
     */
    public static function extractModel(string $manufacturerModel): ?string
    {
        $raw = trim($manufacturerModel);
        if ($raw === '') {
            return null;
        }
        $slash = strpos($raw, '/');
        if ($slash !== false) {
            $tail = trim(substr($raw, $slash + 1));
            return $tail === '' ? null : $tail;
        }
        $parts = preg_split('/\s+/', $raw) ?: [];
        if ($parts !== [] && in_array(strtolower($parts[0]), ['yealink', 'snom', 'fanvil', 'grandstream', 'htek'], true)) {
            array_shift($parts);
        }
        $tail = trim(implode(' ', $parts));
        return $tail === '' ? null : $tail;
    }

    /**
     * Sends a SIP NOTIFY with Event: check-sync;reboot=true to the given phone.
     */
    public function clientNotifyReboot(string $ipPhone, int $portPhone, string $eth): void
    {
        if (!filter_var($ipPhone, FILTER_VALIDATE_IP) || $portPhone <= 0 || $portPhone > 65535) {
            return;
        }

        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            SystemMessages::sysLogMsg(
                WorkerProvisioningServerPnP::LOG_TAG,
                "clientNotifyReboot: socket_create(SOCK_DGRAM) failed: " . socket_strerror(socket_last_error())
                    . " (target $ipPhone:$portPhone)",
                LOG_ERR
            );
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

        $sent = @socket_sendto($sock, $msg, strlen($msg), 0, $ipPhone, $portPhone);
        if ($sent === false) {
            SystemMessages::sysLogMsg(
                WorkerProvisioningServerPnP::LOG_TAG,
                "clientNotifyReboot: socket_sendto $ipPhone:$portPhone failed: "
                    . socket_strerror(socket_last_error($sock)),
                LOG_ERR
            );
        } else {
            SystemMessages::sysLogMsg(
                WorkerProvisioningServerPnP::LOG_TAG,
                "clientNotifyReboot: check-sync NOTIFY sent to $ipPhone:$portPhone via $eth",
                LOG_NOTICE
            );
        }
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
        // Skip when ARP failed to resolve the interface — without $eth we'd build a
        // NOTIFY with an empty Via/From host, and Network::getInterface('') is noisy.
        if ($eth !== '') {
            $this->clientNotifyReboot($ip, $port, $eth);
        }
    }

    /**
     * Parses an INI-like blob of vendor-specific overrides keyed by section name.
     *
     * The input may be base64-encoded. We only treat it as base64 when the strict_types
     * round-trip succeeds and the decoded payload contains an INI section marker — otherwise
     * arbitrary base64-shaped strings would be silently rewritten.
     *
     * @param string|null $manualAttributes Either raw INI text or its base64-encoded
     *                                       form. The Phalcon model returns NULL for
     *                                       unpopulated additional_params columns, so
     *                                       we accept null and treat it as "no overrides".
     * @return array<string, string> Map of section name to raw section body.
     */
    public static function parseIniSettings(?string $manualAttributes): array
    {
        if ($manualAttributes === null || $manualAttributes === '') {
            return [];
        }
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
