<?php
/**
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolay Beketov, 5 2020
 *
 */

namespace Modules\ModuleAutoprovision\Lib;

require_once 'Globals.php';

use MikoPBX\Common\Handlers\CriticalErrorsHandler;
use MikoPBX\Common\Models\PbxExtensionModules;
use MikoPBX\Core\System\BeanstalkClient;
use MikoPBX\Core\System\MikoPBXConfig;
use MikoPBX\Core\System\Network;
use MikoPBX\Core\System\Processes;
use MikoPBX\Core\System\System;
use MikoPBX\Core\System\Util;
use MikoPBX\Core\Workers\WorkerBase;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionDevice;

class WorkerProvisioningServerPnP extends WorkerBase
{
    public const BROAD_CAST_IP = '224.0.1.75';
    private string $url;
    private BeanstalkClient $client_queue;
    private array $interfaces;
    private string $pbx_version;
    private string $class_name = 'WorkerProvisioningServerPnP';
    private array $mac_white = [];
    private array $mac_black = [];
    private string $requests_dir;
    private bool $debug;

    public function getSettings($debug = false):void
    {
        $mikoPBXConfig = new MikoPBXConfig();
        $network = new Network();

        $data = ModuleAutoprovision::findFirst();
        $this->debug       = ($debug === true);
        $http_port         = $mikoPBXConfig->getGeneralSettings('WEBPort');
        $this->pbx_version = $mikoPBXConfig->getGeneralSettings('PBXVersion');
        $this->interfaces  = $network->getInterfacesNames();

        $protocol  = 'http';
        $this->url = "$protocol://$data->pbx_host:$http_port/pbxcore/api/autoprovision";

        $re = '/\w{2}:?\w{2}:?\w{2}:?\w{2}:?\w{2}:?\w{2}/m';
        preg_match_all($re, strtolower(str_replace(':', '', $data->mac_white)), $this->mac_white, PREG_SET_ORDER);
        if (count($this->mac_white) > 0) {
            $this->mac_white = array_merge(...$this->mac_white);
        }

        preg_match_all($re, strtolower(str_replace(':', '', $data->mac_black)), $this->mac_black, PREG_SET_ORDER);
        if (count($this->mac_black) > 0) {
            $this->mac_black = array_merge(...$this->mac_black);
        }
        $this->requests_dir = System::getLogDir() . '/' . $this->class_name . '/requests';
        if ( !file_exists($this->requests_dir) &&
             !mkdir($this->requests_dir, 0777, true) &&
             !is_dir($this->requests_dir)) {
            $this->requests_dir = '';
        }
    }

    /**
     * Worker Entry point
     * @param mixed $params
     */
    public function start($params): void
    {
        $this->getSettings();
        $result = PbxExtensionModules::findFirstByUniqid("ModuleAutoprovision");
        if ($result !== null && $result->disabled === '1') {
            Processes::processWorker('', '', __CLASS__, 'stop');
            return;
        }
        $action = $params[1]??'';
        // Общий воркер статует всегда все скрипты с компндой start
        if ($action === 'socket_server' || $action === 'start') {
            $this->client_queue = new BeanstalkClient();
            $this->client_queue->subscribe('ping_' . self::class, [$this, 'pingCallBack']);
            $this->listen();
        } elseif ($action === 'socket_client') {
            $ip   = $params[2] ?? '127.0.0.1';
            $port = (integer)($params[3] ?? 5062);
            $mac  = str_replace(':', '', $params[4] ?? '0015657322ff');
            self::testSocketClient($ip, $port, $mac);
        } elseif ($action === 'socket_client_notify') {
            $ip_pbx     = $params[2] ?? '127.0.0.1';
            $port_pbx   = (integer)($params[3] ?? 5060);
            $ip_phone   = $params[4] ?? '172.16.32.138';
            $port_phone = (integer)($params[5] ?? 5062);
            self::socketClientNotify($ip_pbx, $port_pbx, $ip_phone, $port_phone);
        } elseif ($action === 'help') {
            echo "\n";
            echo 'php -f WorkerProvisioningServerPnP.php socket_client_notify <IP_PBX> <PORT_SIP_PBX> <IP_PHONE> <PORT_PHONE>' . "\n";
            echo 'php -f WorkerProvisioningServerPnP.php socket_client_notify 172.16.32.153 5060 172.16.32.148 5060' . "\n";
            echo "\n";
            echo 'php -f WorkerProvisioningServerPnP.php socket_server' . "\n";
            echo "\n";
            echo 'php -f WorkerProvisioningServerPnP.php socket_client <IP_PHONE> <PORT_PHONE> <MAC_PHONE>' . "\n";
            echo 'php -f WorkerProvisioningServerPnP.php socket_client 172.16.156.223 5062 0015657322f1' . "\n";
            echo "\n";
        }
    }


    /**
     * Функция тестирования запущенного PnP сервера. Отправляет SIP запрос на провижинг.
     *
     * @param $ip
     * @param $port
     * @param $mac
     */
    public static function testSocketClient($ip, $port, $mac): void
    {
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_bind($sock, $ip, $port);

        $resive_sock = @socket_create(AF_INET, SOCK_RAW, SOL_UDP);
        socket_bind($resive_sock, $ip, $port);

        $msg = "SUBSCRIBE sip:MAC$mac@".self::BROAD_CAST_IP." SIP/2.0\r\n" .
            "Via: SIP/2.0/UDP $ip:$port.;branch=z9hG4bK42054260\r\n" .
            "From: <sip:MAC$mac@".self::BROAD_CAST_IP.">;tag=42054258\r\n" .
            "To: <sip:MAC$mac@".self::BROAD_CAST_IP.">\r\n" .
            "Call-ID: 42054258@$ip\r\n" .
            "CSeq: 1 SUBSCRIBE\r\n" .
            "Contact: <sip:MAC$mac@$ip:$port>\r\n" .
            "Max-Forwards: 70\r\n" .
            "User-Agent: Yealink SIP-T21P 34.72.14.6\r\n" .
            "Expires: 0\r\n" .
            'Event: ua-profile;profile-type="device";vendor="Yealink";model="T21D";version="34.72.14.6"' . "\r\n" .
            "Accept: application/url\r\n" .
            "Content-Length: 0\r\n\n";
        /*
        $msg =  "SUBSCRIBE sip:MAC%3a{$mac}@miko.ru SIP/2.0"."\r\n".
                "Via: SIP/2.0/UDP {$ip}:{$port};rport"."\r\n".
                "From: <sip:MAC%3a{$mac}@miko.ru>;tag=1145111611"."\r\n".
                "To: <sip:MAC%3a{$mac}@miko.ru>"."\r\n".
                'Call-ID: 1913994428@{$ip}'."\r\n".
                'CSeq: 1 SUBSCRIBE'."\r\n".
                'Event: ua-profile;profile-type="device";vendor="snom";model="snomD120";version="10.1.39.11"'."\r\n".
                'Expires: 0'."\r\n".
                'Accept: application/url'."\r\n".
                "Contact: <sip:{$ip}:{$port}>"."\r\n".
                'User-Agent: snomD120/10.1.39.11'."\r\n".
                'Content-Length: 0'."\r\n\n";
        //*/

        $len = strlen($msg);
        socket_sendto($sock, $msg, $len, 0, self::BROAD_CAST_IP, 5060);
        socket_close($sock);

        do {
            if (socket_recv($resive_sock, $packet, 65536, 0)) {
                // Получаем данные пакета.
                $ihl      = ord($packet[0]) & 0xf;
                $payload  = substr($packet, $ihl << 2);
                $row_data = trim(substr($payload, 8));
                // Парсим.
                $rows = explode("\n", $row_data);
                if (count($rows) < 4) {
                    continue;
                }

                echo "\n$row_data\n\n";
                $method = explode(' ', $rows[0])[0];

                if ('NOTIFY' === $method) {
                    break;
                }
            }
        } while (true);
        socket_close($resive_sock);
    }

    /**
     * Отправка на телефон запроса на перезагрузку.
     *
     * @param $ip_pbx
     * @param $port_pbx
     * @param $ip_phone
     * @param $port_phone
     */
    public static function socketClientNotify($ip_pbx, $port_pbx, $ip_phone, $port_phone): void
    {
        $phone_user = AutoprovisionConf::SIP_USER;
        $sock       = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        $msg = "NOTIFY sip:$phone_user@$ip_phone:$port_phone;ob SIP/2.0\r\n" .
            "Via: SIP/2.0/UDP $ip_pbx:$port_pbx;branch=z9hG4bK12fd4e5c;rport\r\n" .
            "Max-Forwards: 70\r\n" .
            "From: \"asterisk\" <sip:asterisk@$ip_pbx>;tag=as54cd2be9\r\n" .
            "To: <sip:$phone_user@$ip_phone:$port_phone;ob>\r\n" .
            "Contact: <sip:asterisk@$ip_pbx:$port_pbx>\r\n" .
            "Call-ID: 4afab6ce2bff0be11a4af41064340242@$ip_pbx:$port_pbx\r\n" .
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
     * Запуск сервера.
     *
     * @return bool
     */
    public function listen(): bool
    {
        $sock = @socket_create(AF_INET, SOCK_RAW, SOL_UDP);
        if ($sock) {
            socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
            $options = ['group' => self::BROAD_CAST_IP];
            foreach ($this->interfaces as $eth) {
                $options['interface'] = $eth;
                socket_set_option($sock, IPPROTO_IP, MCAST_JOIN_GROUP, $options);
            }
        } else {
            return false;
        }
        $res = socket_bind($sock, self::BROAD_CAST_IP, 5060);
        if ( ! $res) {
            return false;
        }

        do {
            if (socket_recv($sock, $packet, 10240, 0)) {
                // Получаем данные пакета.
                $ihl      = ord($packet[0]) & 0xf;
                $payload  = substr($packet, $ihl << 2);
                $row_data = trim(substr($payload, 8));
                // Парсим.
                $headers = $this->parse($row_data);
                if (count($headers) > 0) {
                    // Отправляем ответ с настройками.
                    $this->send_response($headers);
                }
            }
            $this->client_queue->wait(); // instead of sleep
        } while (true);
    }

    /**
     * @param $row_data
     *
     * @return array|null
     */
    public function parse($row_data): array
    {
        $this->verbose("\n $row_data \n");
        $rows = explode("\n", $row_data);
        if (empty($rows)){
            return [];
        }
        $method = explode(' ', $rows[0])[0];
        if ('SUBSCRIBE' !== $method) {
            return [];
        }
        $headers = [
            'mac'        => '',
            'phone_ip'   => '',
            'phone_port' => '5060',
            'vendor'     => '',
            'model'      => '',
        ];
        // В перовй строке смотрим имя SIP сообещния и MAC адрес телефона.
        $headers['method'] = $method;

        $pos_start      = strpos($rows[0], '@') - 12;
        $headers['mac'] = strtolower(substr($rows[0], $pos_start, 12));

        if (count($this->mac_white) > 0 && ! in_array($headers['mac'], $this->mac_white, true)) {
            // Если есть белый список, то черный не используем.
            // Провижить можно только белый список.
            return [];
        }

        if (count($this->mac_black) > 0 && in_array($headers['mac'], $this->mac_black, true)) {
            // Если белый список пуст, то телефоны из черного списка провижить нельзя.
            return [];
        }

        unset($rows[0]);
        foreach ($rows as $row) {
            $row    = trim($row);
            $h_name = explode(':', $row)[0];
            if ('Via' === $h_name) {
                // Ищем строку вида 172.16.156.1:53582.
                preg_match_all('/\d+.\d+.\d+.\d+:?\d*/m', $row, $matches, PREG_SET_ORDER);
                if (!empty($matches) && count($matches[0]) === 1) {
                    $res                   = explode(':', $matches[0][0]);
                    $headers['phone_ip']   = $res[0];
                    $headers['phone_port'] = $res[1];
                }
                $headers[$h_name] = $row;
            } elseif ('From' === $h_name) {
                $headers[$h_name] = $row;
            } elseif ('Call-ID' === $h_name) {
                $headers[$h_name] = $row;
            } elseif ('Event' === $h_name) {
                $event_data = [];
                // Event: ua-profile;profile-type="device";vendor="Yealink";model="T21D";version="34.72.14.6"
                $res_params = explode(';', strtolower($row));
                foreach ($res_params as $res_param) {
                    $arr_param = preg_split('/:\s|=/m', $res_param, -1, PREG_SPLIT_NO_EMPTY);
                    if ( ! in_array($arr_param[0], ['vendor', 'model', 'version'])) {
                        continue;
                    }
                    $event_data[$arr_param[0]] = str_replace('"', '', $arr_param[1]);
                }
                $headers[$h_name]         = $event_data;
                $headers["OLD_$h_name"] = $row;
            } elseif ('To' === $h_name) {
                $headers[$h_name] = $row;
            }
        }
        // Наполним таблицу ARP.
        exec("timeout -t 1 ping {$headers['phone_ip']} -c 1 ");
        // Анализируем MAC адрес устройства.
        exec("busybox arp -D {$headers['phone_ip']} -n | /bin/busybox awk  '{ print $4 }' 2>&1", $out);
        $real_mac = $out[0] ?? '';
        $real_mac = str_replace(':', '', $real_mac);
        if ($real_mac !== $headers['mac']) {
            Util::sysLogMsg(
                $this->class_name,
                'The mac address of the device does not match the address in the sip request r_mac: ' . $real_mac . ' mac: ' . $headers['mac'],
                LOG_NOTICE
            );
        }
        if ( ! empty($headers['mac']) && ! empty($headers['phone_ip'])) {
            Util::sysLogMsg(
                $this->class_name,
                "Request provisiong from ip: {$headers['phone_ip']}; phone: {$headers['Event']['vendor']} {$headers['Event']['model']}; mac=" . $real_mac,
                LOG_NOTICE
            );
            try {
                $data = json_encode($headers, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }catch (\Exception $e){
                $data = [];
            }
            file_put_contents($this->requests_dir . '/' . $headers['mac'], $data);
        }

        if ( ! empty($headers['mac']) && ! empty($headers['phone_ip'])) {
            /** @var ModuleAutoprovisionDevice $res */
            /** @var ModuleAutoprovisionDevice $devise */
            $devises = ModuleAutoprovisionDevice::find("host='{$headers['phone_ip']}'");
            foreach ($devises as $devise) {
                $devise->host = '';
                $devise->save();
            }

            $res = ModuleAutoprovisionDevice::findFirst("mac='{$headers['mac']}'");
            if ($res === null) {
                $res                     = new ModuleAutoprovisionDevice();
                $res->mac                = $headers['mac'];
                $res->port               = $headers['phone_port'];
                $res->host               = $headers['phone_ip'];
                $res->manufacturer_model = $headers['Event']['vendor'] . ' / ' . $headers['Event']['model'];
                $res->save();
            } elseif ($res->mac !== $headers['mac'] || $res->port !== $headers['phone_port']) {
                $res->mac                = $headers['mac'];
                $res->port               = $headers['phone_port'];
                $res->host               = $headers['phone_ip'];
                $res->manufacturer_model = $headers['Event']['vendor'] . ' / ' . $headers['Event']['model'];
                $res->save();
            }
        }

        return $headers;
    }

    /**
     * Вывод отладочных сообщений.
     *
     * @param $msg
     */
    private function verbose($msg): void
    {
        if ($this->debug) {
            echo($msg);
        }
    }

    /**
     * Отправка SIP ответов на телефон.
     *
     * @param $headers
     */
    public function send_response($headers): void
    {
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $msg  = "SIP/2.0 200 OK\r\n" .
            "{$headers['Via']}\r\n" .
            "Contact: <sip:{$headers['phone_ip']}:{$headers['phone_port']};transport=udp;handler=dum>\r\n" .
            "{$headers['To']}\r\n" .
            "{$headers['From']}\r\n" .
            "{$headers['Call-ID']}\r\n" .
            "CSeq: 1 {$headers['method']}\r\n" .
            "Expires: 0\r\n" .
            "Content-Length: 0\r\n";

        $this->sendToHost($sock, $headers['phone_ip'], (int)$headers['phone_port'], $msg);
        $this->verbose("\n" . $msg);

        $provisionUrl = "$this->url/getcfg?mac={$headers['mac']}&" . http_build_query($headers['Event']);
        $provisionUrl .= '&solt=' . md5($headers['mac'] . getmypid());

        $msg = "NOTIFY sip:{$headers['phone_ip']}:{$headers['phone_port']} SIP/2.0\r\n" .
            "{$headers['Via']}\r\n" .
            "Max-Forwards: 20\r\n" .
            "Contact: <sip:{$headers['phone_ip']}:{$headers['phone_port']};transport=udp;handler=dum>\r\n" .
            "{$headers['To']}\r\n" .
            "{$headers['From']}\r\n" .
            "{$headers['Call-ID']}\r\n" .
            "CSeq: 3 NOTIFY\r\n" .
            "Content-Type: application/url\r\n" .
            "Subscription-State: terminated;reason=timeout\r\n" .
            "Event: ua-profile;profile-type=\"device\";vendor=\"MIKO\";model=\"$this->class_name\";version=\"$this->pbx_version\"\r\n" .
            'Content-Length: ' . strlen($provisionUrl) . "\r\n" .
            "\r\n" .
            $provisionUrl;

        $this->verbose("\n" . $msg);
        $this->sendToHost($sock, $headers['phone_ip'], (int)$headers['phone_port'], $msg);
        socket_close($sock);
    }

    /**
     * Отправка SIP сообщения на устрйоство.
     *
     * @param $sock
     * @param $ip
     * @param $port
     * @param $msg
     */
    private function sendToHost($sock, $ip, $port, $msg): void
    {
        $len = strlen($msg);
        if (@socket_connect($sock, $ip, $port)) {
            try {
                $result = socket_sendto($sock, $msg, $len, 0, $ip, $port);
                if ( ! $result) {
                    usleep(50000);
                    socket_sendto($sock, $msg, $len, 0, $ip, $port);
                }
            } catch (\Throwable $e) {
                Util::sysLogMsg($this->class_name, $e->getMessage(), LOG_ERR);
            }
        } else {
            Util::sysLogMsg($this->class_name, "Host lookup failed $ip:$port...", LOG_ERR);
        }
    }

}


// Start worker process
$workerClassname = WorkerProvisioningServerPnP::class;
if (isset($argv) && count($argv) > 1) {
    cli_set_process_title($workerClassname);
    try {
        $worker = new $workerClassname();
        $worker->start($argv);
    } catch (\Throwable $e) {
        CriticalErrorsHandler::handleExceptionWithSyslog($e);
    }
}