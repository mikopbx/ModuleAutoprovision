<?php
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

    protected $tempDir;
    protected $mikoPBXConfig;

    public function __construct()
    {
        $this->tempDir    = $this->di->getShared('config')->path('core.tempDir');
        $this->mikoPBXConfig    = new MikoPBXConfig();
    }

    /**
     * Создание конфига телефона.
     *
     * @param $req_data
     *
     * @return string
     */
    public function generateConfigPhone($req_data): string
    {
        $phone_data = ModuleAutoprovisionDevice::findFirst("mac='{$req_data['mac']}'");
        if ($phone_data === null) {
            return '';
        }
        $sip_data  = [];
        $user_data = ModuleAutoprovisionUsers::find("id_phone='{$phone_data->id}'");
        foreach ($user_data as $data) {
            $exten = Extensions::findFirst("userid='{$data->userid}' AND type='SIP'");
            if ($exten !== null) {
                $sip = Sip::findFirst("extension='$exten->number'");
                if ($sip !== null) {
                    $sip_data[$data->line] = [
                        'extension' => $sip->extension,
                        'secret'    => $sip->secret,
                        'callerid'  => $exten->callerid,
                    ];
                }
            }
        }
        if (empty($sip_data)) {
            $def_peer      = [
                'extension' => AutoprovisionConf::SIP_USER,
                'secret'    => AutoprovisionConf::SIP_SECRET,
                'callerid'  => AutoprovisionConf::SIP_USER,
            ];
            $sip_data['1'] = $def_peer;
            if ($req_data['model'] === 'W52P') {
                // DECT база. Тут настройка особенная, несколько SIP аккаунтов - 5шт.
                $sip_data['2'] = $def_peer;
                $sip_data['3'] = $def_peer;
                $sip_data['4'] = $def_peer;
                $sip_data['5'] = $def_peer;
            }
        }
        switch ($req_data['vendor']) {
            case 'yealink':
                $confManager = new AutoprovisionYealink();
                break;
            case 'fanvil':
                $confManager = new AutoprovisionFanvil();
                break;
            case 'snom':
                $confManager = new AutoprovisionSnom();
                break;
            default:
                return '';
        }

        return $confManager->generateConfig($req_data, $sip_data);
    }



    /**
     * Отправка запроса NOTIFY на телефон.
     *
     * @param $ip_phone
     * @param $port_phone
     * @param $eth
     */
    public function clientNotifyReboot($ip_phone, $port_phone, $eth): void
    {
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        $net        = new Network();
        $eth        = $net->getInterface($eth);
        $ip_pbx     = $eth['ipaddr'];
        $port_pbx   = $this->mikoPBXConfig->getGeneralSettings('SIPPort');
        $phone_user = AutoprovisionConf::SIP_USER;

        $msg = "NOTIFY sip:{$phone_user}@{$ip_phone}:{$port_phone};ob SIP/2.0\r\n" .
            "Via: SIP/2.0/UDP {$ip_pbx}:{$port_pbx};branch=z9hG4bK12fd4e5c;rport\r\n" .
            "Max-Forwards: 70\r\n" .
            "From: \"asterisk\" <sip:asterisk@{$ip_pbx}>;tag=as54cd2be9\r\n" .
            "To: <sip:{$phone_user}@{$ip_phone}:{$port_phone};ob>\r\n" .
            "Contact: <sip:asterisk@{$ip_pbx}:{$port_pbx}>\r\n" .
            "Call-ID: 4afab6ce2bff0be11a4af41064340242@{$ip_pbx}:{$port_pbx}>\r\n" .
            "CSeq: 102 NOTIFY\r\n" .
            "User-Agent: mikopbx\r\n" .
            "Allow: INVITE, ACK, CANCEL, OPTIONS, BYE, REFER, SUBSCRIBE, NOTIFY, INFO, PUBLISH, MESSAGE\r\n" .
            "Supported: replaces, timer\r\n" .
            "Subscription-State: terminated\r\n" .
            "Event: check-sync;reboot=true\r\n" .
            "Content-Length: 0\r\n\n";

        $len = strlen($msg);
        socket_sendto($sock, $msg, $len, 0, $ip_phone, $port_phone);
        socket_close($sock);
    }

    /**
     * AGI скрипт для запроса настройки телефона.
     */
    public function StartAGIProvision(): void
    {
        $agi = new AGI();
        $row = $agi->get_variable('PT1C_VIA', true);
        preg_match_all('/\d+.\d+.\d+.\d+:?\d*/m', $row, $matches, PREG_SET_ORDER);
        if (!empty($matches) && count($matches[0]) === 1) {
            $res = explode(':', $matches[0][0]);
            [$ip, $port] = $res;

            $tmp_data = explode('*', $agi->request['agi_extension']);
            $sip_id   = array_pop($tmp_data);
            $agi->noop("{$agi->request['agi_extension']}  $sip_id");
            /** @var  \MikoPBX\Common\Models\Extensions; $exten */
            $exten = Extensions::findFirst("number='{$sip_id}'");
            if ($exten === null) {
                $agi->set_variable('PROVISION_STATUS', 'EXTEN_NOT_FOUND');
                return;
            }

            $phone_data = null;
            exec("timeout -t 1 ping {$ip} -c 1");
            // Анализируем MAC адрес устройства.
            $arp = Util::which('arp');
            $awk = Util::which('awk');
            exec("$arp -D {$ip} -n | $awk  '{ print $4 \" \" $7}' 2>&1", $out);

            [$mac, $eth] = explode(' ', $out[0] ?? '');
            $mac = str_replace(':', '', $mac);
            // Телефон мог сменить ip адрес. Попробуем получить его из ARP таблицы.
            $agi->noop('arp - ' . implode('', $out) . '.');
            if ( ! empty($eth)) {
                /** @var ModuleAutoprovisionDevice $phone_data */
                // Ищем по mac адресу.
                $phone_data = ModuleAutoprovisionDevice::findFirst("mac='{$mac}'");
            }
            $agi->noop("eth - {$eth}; mac - {$mac}");
            if ( ! $phone_data) {
                // Ищем по IP адресу.
                $phone_data = ModuleAutoprovisionDevice::findFirst("host='{$ip}'");
            }

            if ($phone_data !== null && ! empty($phone_data->mac)) {
                if (false !== stripos($phone_data->manufacturer_model, 'W52P')) {
                    // Для DECT трубки определим номер линии из заголовка Call-ID.
                    $call_id = $agi->get_variable('SIP_HEADER(Call-ID)', true);
                    $agi->noop("call_id - $call_id.");
                    // Номер линии находится до символа "_"
                    // 0_47643482@172.16.32.59
                    $params = explode(' ', $call_id);
                    // Нумерация линий начинается с 0.
                    $line = (count($params) > 1) ? ($params[0] + 1) : 1;
                } else {
                    $line = 1;
                }
                $Auto_Provision_User = ModuleAutoprovisionUsers::findFirst(
                    "id_phone={$phone_data->id} AND line='{$line}'"
                );
                if ($Auto_Provision_User === null) {
                    $Auto_Provision_User           = new ModuleAutoprovisionUsers();
                    $Auto_Provision_User->id_phone = $phone_data->id;
                }
                $Auto_Provision_User->line   = $line;
                $Auto_Provision_User->userid = $exten->userid;
                $Auto_Provision_User->save();

                $agi->set_variable('PROVISION_STATUS', 'OK');

                // Тут все ОК, нужно перезагрузить телефон.
                $this->clientNotifyReboot($ip, $port, $eth);
            } else {
                // Записать в syslog. Обработать fail2ban.
                $agi->set_variable('PROVISION_STATUS', 'PHONE_NOT_FOUND');
            }
        }
    }


    /**
     * Разбор INI конфига
     *
     * @param $manual_attributes
     *
     * @return array
     */
    public static function parseIniSettings($manual_attributes): array
    {
        $tmp_data = base64_decode($manual_attributes);
        if (base64_encode($tmp_data) === $manual_attributes) {
            $manual_attributes = $tmp_data;
        }
        unset($tmp_data);
        // TRIMMING
        $tmp_arr = explode("\n", $manual_attributes);
        foreach ($tmp_arr as &$row) {
            $row = trim($row);
            $pos = strpos($row, ']');
            if ($pos !== false && strpos($row, '[') === 0) {
                $row = "\n" . substr($row, 0, $pos);
            }
        }
        unset($row);
        $manual_attributes = implode("\n", $tmp_arr);
        // TRIMMING END

        $manual_data = [];
        $sections    = explode("\n[", str_replace(']', '', $manual_attributes));
        foreach ($sections as $section) {
            $data_rows    = explode("\n", trim($section));
            $section_name = trim($data_rows[0] ?? '');
            if ( ! empty($section_name)) {
                unset($data_rows[0]);
                $manual_data[$section_name] = implode("\n", $data_rows);
            }
        }
        return $manual_data;
    }

}