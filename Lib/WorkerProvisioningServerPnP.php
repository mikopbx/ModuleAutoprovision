<?php

declare(strict_types=1);
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
use MikoPBX\Core\System\SystemMessages;
use MikoPBX\Core\Workers\WorkerBase;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionDevice;

class WorkerProvisioningServerPnP extends WorkerBase
{
    public const BROAD_CAST_IP = '224.0.1.75';

    // Syslog program tag for the PnP worker. Operators can run
    // `grep -E 'autoprovision-(pnp|http)' /storage/usbdisk1/mikopbx/log/system/messages`
    // to see the full provisioning pipeline (multicast + HTTP) in one place.
    public const LOG_TAG = 'autoprovision-pnp';
    private string $url;
    private BeanstalkClient $client_queue;
    private array $interfaces;
    private string $pbx_version;
    private string $class_name = 'WorkerProvisioningServerPnP';
    private array $mac_white = [];
    private array $mac_black = [];
    private string $requests_dir;
    private bool $debug;
    private bool $verbose_syslog = false;

    public function getSettings($debug = false):void
    {
        $mikoPBXConfig = new MikoPBXConfig();
        $network = new Network();

        $data = ModuleAutoprovision::findFirst();
        $this->debug       = ($debug === true);
        // The module serves provisioning on its own nginx server-block to bypass
        // the global HTTPS redirect — see AutoprovisionConf::createNginxServers().
        $http_port         = AutoprovisionConf::getHttpPort();
        $this->pbx_version = $mikoPBXConfig->getGeneralSettings('PBXVersion');
        $this->interfaces  = $network->getInterfacesNames();

        // First-run safety: settings row may not exist yet (fresh install, mid-upgrade).
        $pbxHost          = (string)($data->pbx_host ?? '');
        $macWhiteRaw      = (string)($data->mac_white ?? '');
        $macBlackRaw      = (string)($data->mac_black ?? '');
        $additionalParams = (string)($data->additional_params ?? '');

        $protocol  = 'http';
        $this->url = "$protocol://$pbxHost:$http_port/pbxcore/api/autoprovision";

        $re = '/\w{2}:?\w{2}:?\w{2}:?\w{2}:?\w{2}:?\w{2}/m';

        preg_match_all($re, strtolower(str_replace(':', '', $macWhiteRaw)), $this->mac_white, PREG_SET_ORDER);
        if (count($this->mac_white) > 0) {
            $this->mac_white = array_merge(...$this->mac_white);
        }

        preg_match_all($re, strtolower(str_replace(':', '', $macBlackRaw)), $this->mac_black, PREG_SET_ORDER);
        if (count($this->mac_black) > 0) {
            $this->mac_black = array_merge(...$this->mac_black);
        }
        $this->requests_dir = System::getLogDir() . '/' . $this->class_name . '/requests';
        if ( !file_exists($this->requests_dir) &&
             !mkdir($this->requests_dir, 0777, true) &&
             !is_dir($this->requests_dir)) {
            $this->requests_dir = '';
        }

        // Opt-in per-packet syslog noise: `[debug]\nverbose = 1` inside additional_params.
        $this->verbose_syslog = $this->parseVerboseFlag($additionalParams);
    }

    /**
     * Parses additional_params for a `[debug] verbose = <truthy>` toggle.
     * Default off so production hosts don't flood the system message log.
     */
    private function parseVerboseFlag(string $additionalParams): bool
    {
        if ($additionalParams === '') {
            return false;
        }
        // additional_params can be base64-encoded INI (see Autoprovision::parseIniSettings).
        $decoded = base64_decode($additionalParams, true);
        $candidate = ($decoded !== false && str_contains($decoded, '[')) ? $decoded : $additionalParams;
        $parsed = @parse_ini_string($candidate, true, INI_SCANNER_TYPED);
        if (!is_array($parsed) || !isset($parsed['debug']['verbose'])) {
            return false;
        }
        return (bool)$parsed['debug']['verbose'];
    }

    /**
     * Worker Entry point
     * @param mixed $argv
     */
    public function start($argv): void
    {
        $this->getSettings();
        $result = PbxExtensionModules::findFirstByUniqid("ModuleAutoprovision");
        if ($result !== null && $result->disabled === '1') {
            Processes::processWorker('', '', __CLASS__, 'stop');
            return;
        }
        $action = $argv[1]??'';
        // Общий воркер статует всегда все скрипты с компндой start
        if ($action === 'socket_server' || $action === 'start') {
            $this->client_queue = new BeanstalkClient();
            // Subscribe to the canonical ping tube name (camelized in WorkerBase).
            // The previous literal `'ping_' . self::class` produced a different
            // string than `makePingTubeName()` builds, so SafeScripts' pings
            // landed in a tube nobody read and the worker was restarted every
            // cycle (40s "processed more than 36 seconds" warning loop).
            $this->client_queue->subscribe(
                $this->makePingTubeName(self::class),
                [$this, 'pingCallBack']
            );
            $this->listen();
        } elseif ($action === 'socket_client') {
            $ip   = $argv[2] ?? '127.0.0.1';
            $port = (int)($argv[3] ?? 5062);
            $mac  = str_replace(':', '', $argv[4] ?? '0015657322ff');
            self::testSocketClient($ip, $port, $mac);
        } elseif ($action === 'socket_client_notify') {
            $ip_pbx     = $argv[2] ?? '127.0.0.1';
            $port_pbx   = (int)($argv[3] ?? 5060);
            $ip_phone   = $argv[4] ?? '172.16.32.138';
            $port_phone = (int)($argv[5] ?? 5062);
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
        SystemMessages::sysLogMsg(
            self::LOG_TAG,
            sprintf(
                'PnP listener starting: pid=%d interfaces=[%s] mac_white=%d mac_black=%d verbose=%d',
                getmypid(),
                implode(',', $this->interfaces),
                count($this->mac_white),
                count($this->mac_black),
                (int)$this->verbose_syslog
            ),
            LOG_NOTICE
        );

        $sock = @socket_create(AF_INET, SOCK_RAW, SOL_UDP);
        if (!$sock) {
            $err = socket_strerror(socket_last_error());
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                "socket_create(SOCK_RAW) failed: $err. PnP discovery is OFF - phones will not receive provisioning URL.",
                LOG_ERR
            );
            return false;
        }
        socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
        $options = ['group' => self::BROAD_CAST_IP];
        foreach ($this->interfaces as $eth) {
            $options['interface'] = $eth;
            // Clear errno so previous failures don't bleed into this iteration's strerror.
            socket_clear_error($sock);
            $joined = @socket_set_option($sock, IPPROTO_IP, MCAST_JOIN_GROUP, $options);
            if ($joined) {
                SystemMessages::sysLogMsg(
                    self::LOG_TAG,
                    "Joined multicast group " . self::BROAD_CAST_IP . " on interface $eth",
                    LOG_NOTICE
                );
            } else {
                $err = socket_strerror(socket_last_error($sock));
                SystemMessages::sysLogMsg(
                    self::LOG_TAG,
                    "MCAST_JOIN_GROUP failed on interface $eth: $err",
                    LOG_ERR
                );
            }
        }

        if (!@socket_bind($sock, self::BROAD_CAST_IP, 5060)) {
            $err = socket_strerror(socket_last_error($sock));
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                "socket_bind(" . self::BROAD_CAST_IP . ":5060) failed: $err. PnP discovery is OFF.",
                LOG_ERR
            );
            socket_close($sock);
            return false;
        }

        SystemMessages::sysLogMsg(self::LOG_TAG, 'PnP listener ready on ' . self::BROAD_CAST_IP . ':5060', LOG_NOTICE);

        // Swallow the benign EINTR warning that fires when SIGTERM/SIGUSR1
        // interrupts socket_select() — otherwise WorkerBase::shutdownHandler
        // sees it via error_get_last() and emits a noisy LOG_ERR
        // [SHUTDOWN-ERROR] line on every graceful restart.
        set_error_handler(static function (int $errno, string $errstr) {
            if (str_contains($errstr, 'Interrupted system call')) {
                return true;
            }
            return false;
        }, E_WARNING);

        do {
            // Multiplex the raw socket and the beanstalk ping tube via
            // socket_select with a short (1s) timeout. The previous
            // implementation called the blocking socket_recv() in a tight
            // loop and only drained beanstalk *after* a UDP packet landed —
            // on a network with no PnP-broadcasting phones the ping tube
            // could go unread for 30+ seconds, tripping WorkerSafeScripts'
            // 10s warning and the 5s reply timeout, causing needless restart
            // cycles. The short select cap keeps the supervisor's pings
            // healthy while still spending most of the cycle parked in the
            // kernel (one wakeup/sec is negligible).
            $read   = [$sock];
            $write  = null;
            $except = null;
            $n = @socket_select($read, $write, $except, 1, 0);

            if ($n === false) {
                $errNo = socket_last_error();
                // SIGUSR1 / SIGTERM trip EINTR — silent, not an error. Clearing
                // PHP's last-error registry too so WorkerBase::shutdownHandler
                // doesn't report a SHUTDOWN-ERROR on graceful restart.
                if (defined('SOCKET_EINTR') && $errNo === SOCKET_EINTR) {
                    socket_clear_error();
                    error_clear_last();
                } elseif ($errNo !== 0) {
                    SystemMessages::sysLogMsg(
                        self::LOG_TAG,
                        'socket_select() failed: ' . socket_strerror($errNo)
                            . ' (errno=' . $errNo . ')',
                        LOG_ERR
                    );
                    socket_clear_error();
                }
            } elseif ($n > 0 && in_array($sock, $read, true) && socket_recv($sock, $packet, 10240, 0)) {
                // Получаем данные пакета.
                $ihl      = ord($packet[0]) & 0xf;
                $payload  = substr($packet, $ihl << 2);
                $row_data = trim(substr($payload, 8));

                $sourceIp = '';
                // Parse the IPv4 source-address bytes (octets 12..15 of the raw IP header).
                if (strlen($packet) >= 16) {
                    $sourceIp = ord($packet[12]) . '.' . ord($packet[13]) . '.'
                              . ord($packet[14]) . '.' . ord($packet[15]);
                }
                // Source port lives at offset 0..1 of the UDP header (just past the IP header).
                $udpStart = $ihl << 2;
                $sourcePort = 0;
                if (strlen($packet) >= $udpStart + 2) {
                    $sourcePort = (ord($packet[$udpStart]) << 8) | ord($packet[$udpStart + 1]);
                }

                $this->logPacketReceived($sourceIp, $sourcePort, $row_data);

                // Парсим.
                $headers = $this->parse($row_data);
                if (count($headers) > 0) {
                    // Отправляем ответ с настройками.
                    $this->send_response($headers);
                }
            }

            // Drain any pending supervisor ping. wait(0) is non-blocking — if
            // there is nothing in the tube it returns immediately.
            try {
                $this->client_queue->wait(0);
            } catch (\Throwable) {
                // Beanstalk hiccups must not crash the PnP server.
            }
        } while (true);
    }

    /**
     * Per-packet log line, gated entirely behind the verbose flag.
     * Default rsyslog matches `*.*`, so even LOG_INFO would land in /storage/.../messages -
     * skip the call altogether when verbose is off to avoid flooding the global log
     * on networks with many PnP-broadcasting phones.
     */
    private function logPacketReceived(string $sourceIp, int $sourcePort, string $rowData): void
    {
        if (!$this->verbose_syslog) {
            return;
        }
        $firstLine = strtok($rowData, "\n");
        $method = $firstLine !== false ? explode(' ', trim($firstLine))[0] : '';
        SystemMessages::sysLogMsg(
            self::LOG_TAG,
            sprintf('packet from %s:%d method=%s', $sourceIp ?: 'unknown', $sourcePort, $method ?: 'unknown'),
            LOG_NOTICE
        );
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
            // SOCK_RAW receives every UDP datagram on the host, not just SIP — gate this
            // log behind the verbose flag to avoid swamping /storage/.../messages.
            if ($this->verbose_syslog) {
                SystemMessages::sysLogMsg(
                    self::LOG_TAG,
                    "ignoring non-SUBSCRIBE packet: method=$method",
                    LOG_NOTICE
                );
            }
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
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                "rejecting MAC {$headers['mac']}: not in whitelist (whitelist size=" . count($this->mac_white) . ")",
                LOG_NOTICE
            );
            return [];
        }

        if (count($this->mac_black) > 0 && in_array($headers['mac'], $this->mac_black, true)) {
            // Если белый список пуст, то телефоны из черного списка провижить нельзя.
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                "rejecting MAC {$headers['mac']}: blacklisted",
                LOG_NOTICE
            );
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
        // Validate the IP we parsed from the SIP packet before passing it to the shell.
        if (!filter_var($headers['phone_ip'], FILTER_VALIDATE_IP)) {
            return [];
        }
        $safeIp = escapeshellarg($headers['phone_ip']);
        // Populate the ARP table by pinging the phone, then read the MAC back.
        exec("timeout -t 1 ping {$safeIp} -c 1 ");
        exec("busybox arp -D {$safeIp} -n | /bin/busybox awk  '{ print \$4 }' 2>&1", $out);
        $real_mac = $out[0] ?? '';
        $real_mac = str_replace(':', '', $real_mac);
        if ($real_mac !== $headers['mac']) {
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                'The mac address of the device does not match the address in the sip request r_mac: ' . $real_mac . ' mac: ' . $headers['mac'],
                LOG_NOTICE
            );
        }
        if ( ! empty($headers['mac']) && ! empty($headers['phone_ip'])) {
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
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
            $devises = ModuleAutoprovisionDevice::find([
                'host = :host:',
                'bind' => ['host' => $headers['phone_ip']],
            ]);
            foreach ($devises as $devise) {
                $devise->host = '';
                $devise->save();
            }

            $res = ModuleAutoprovisionDevice::findFirst([
                'mac = :mac:',
                'bind' => ['mac' => $headers['mac']],
            ]);
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
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                'send_response: socket_create(SOCK_DGRAM) failed: ' . socket_strerror(socket_last_error()),
                LOG_ERR
            );
            return;
        }
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
        // Do NOT log $provisionUrl - it carries a per-request `solt` token consumed by
        // a no-auth REST endpoint that returns the phone's SIP credentials. Operators only
        // need to know NOTIFY went out, what model it targeted, and whether it landed.
        SystemMessages::sysLogMsg(
            self::LOG_TAG,
            sprintf(
                'NOTIFY sent to %s:%d mac=%s vendor=%s model=%s',
                $headers['phone_ip'],
                (int)$headers['phone_port'],
                $headers['mac'],
                $headers['Event']['vendor'] ?? '',
                $headers['Event']['model'] ?? ''
            ),
            LOG_NOTICE
        );
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
                SystemMessages::sysLogMsg(self::LOG_TAG, $e->getMessage(), LOG_ERR);
            }
        } else {
            SystemMessages::sysLogMsg(self::LOG_TAG, "Host lookup failed $ip:$port...", LOG_ERR);
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