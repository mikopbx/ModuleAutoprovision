<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib;

require_once 'Globals.php';

use MikoPBX\Common\Handlers\CriticalErrorsHandler;
use MikoPBX\Common\Models\PbxExtensionModules;
use MikoPBX\Core\System\BeanstalkClient;
use MikoPBX\Core\System\Processes;
use MikoPBX\Core\System\SystemMessages;
use MikoPBX\Core\Workers\WorkerBase;
use Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Repository as FirmwareRepository;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionDevice;

/**
 * Pure-PHP TFTP read-only server (RFC 1350 + 2347/2348 blksize/tsize).
 *
 * Serves two kinds of payloads:
 *   1. Phone configs generated on-demand by {@see Autoprovision::generateConfigPhone()}
 *      for filenames that contain a 12-hex-char MAC (Yealink "<mac>.cfg",
 *      Snom "<mac>.xml", Fanvil "f<mac>.cfg", ...).
 *   2. Firmware blobs straight from disk under <moduleDir>/firmware/<vendor>/.
 *
 * Read-only by design: WRQs are rejected with TFTP error 4 (illegal operation).
 *
 * Concurrency: single-process socket_select over the listening socket plus one
 * ephemeral session socket per active transfer. No fork() — keeps the worker
 * supervisable by WorkerSafeScriptsCore and avoids zombie reaping. Memory-leak
 * defences:
 *   - Hard cap MAX_SESSIONS rejects new RRQs when at capacity.
 *   - Per-session SESSION_TIMEOUT closes idle sockets and removes file handles.
 *   - blksize is clamped at MAX_BLKSIZE so a malicious client cannot allocate
 *     a 64 KiB-per-block window times MAX_SESSIONS.
 *   - File reads stream via fread() of one block at a time; never load the
 *     whole firmware blob into PHP memory.
 *   - Temp files produced by generateConfigPhone() are unlinked on session end
 *     (graceful or timeout). A register_shutdown_function purges anything left
 *     behind if PHP exits unexpectedly.
 */
class WorkerTftpServer extends WorkerBase
{
    public const LOG_TAG               = 'autoprovision-tftp';
    public const PORT                  = 69;
    public const DEFAULT_BLKSIZE       = 512;
    public const MAX_BLKSIZE           = 8192;
    public const SESSION_TIMEOUT       = 10;
    public const MAX_RETRIES           = 5;
    public const MAX_SESSIONS          = 32;
    public const SELECT_TIMEOUT_USEC   = 200_000;

    // TFTP opcodes (RFC 1350 §5)
    private const OP_RRQ   = 1;
    private const OP_WRQ   = 2;
    private const OP_DATA  = 3;
    private const OP_ACK   = 4;
    private const OP_ERROR = 5;
    private const OP_OACK  = 6;

    // TFTP error codes (RFC 1350 §5)
    private const ERR_NOT_DEFINED   = 0;
    private const ERR_FILE_NOT_FOUND = 1;
    private const ERR_ACCESS        = 2;
    private const ERR_ILLEGAL_OP    = 4;
    private const ERR_OPT_REFUSED   = 8;

    /** @var \Socket|null Main listening socket on UDP/{@see self::PORT}. */
    private $mainSocket = null;

    /**
     * @var array<string, array{
     *     id: string,
     *     sock: \Socket,
     *     client_ip: string,
     *     client_port: int,
     *     filename: string,
     *     fh: resource|null,
     *     tempfile: string|null,
     *     blksize: int,
     *     tsize: int|null,
     *     block_num: int,
     *     last_block_data: string,
     *     last_block_size: int,
     *     last_activity: int,
     *     retries: int,
     *     awaiting_oack_ack: bool
     * }>
     */
    private array $sessions = [];

    private BeanstalkClient $client_queue;
    private bool $stopping = false;
    private string $firmwareBase = '';

    public function start($argv): void
    {
        $module = PbxExtensionModules::findFirstByUniqid('ModuleAutoprovision');
        if ($module !== null && $module->disabled === '1') {
            Processes::processWorker('', '', __CLASS__, 'stop');
            return;
        }

        $settings = ModuleAutoprovision::findFirst();
        if ($settings === null || ((string)$settings->tftp_enabled) !== '1') {
            // Operator turned the feature off — exit silently. WorkerSafeScripts
            // will not respawn workers whose enable flag is false (the supervisor
            // checks getModuleWorkers() each cycle).
            return;
        }

        // Ensure the firmware tree exists before resolveFile() starts calling
        // realpath() against it — without this, an admin who enabled TFTP
        // before any firmware was uploaded would see every static-file lookup
        // bail at the realpath-of-base step (defence in depth for the
        // path-traversal check in fileInfoIfExists()).
        FirmwareRepository::ensureBaseDir();
        $this->firmwareBase = FirmwareRepository::baseDir();

        $this->client_queue = new BeanstalkClient();
        $this->client_queue->subscribe('ping_' . self::class, [$this, 'pingCallBack']);

        register_shutdown_function([$this, 'cleanupAllSessions']);

        $this->listen();
    }

    /**
     * Open UDP/69 and pump RRQs / per-session ACKs until SIGTERM.
     */
    private function listen(): void
    {
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                'socket_create(SOCK_DGRAM) failed: ' . socket_strerror(socket_last_error())
                    . ' — TFTP server cannot start',
                LOG_ERR
            );
            return;
        }
        socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
        if (!@socket_bind($sock, '0.0.0.0', self::PORT)) {
            $err = socket_strerror(socket_last_error($sock));
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                'socket_bind(0.0.0.0:' . self::PORT . ') failed: ' . $err
                    . ' — another TFTP server may already be running, or the process lacks CAP_NET_BIND_SERVICE',
                LOG_ERR
            );
            socket_close($sock);
            return;
        }
        $this->mainSocket = $sock;

        SystemMessages::sysLogMsg(
            self::LOG_TAG,
            sprintf(
                'TFTP server ready on 0.0.0.0:%d pid=%d firmware_dir=%s',
                self::PORT,
                getmypid(),
                $this->firmwareBase
            ),
            LOG_NOTICE
        );

        while (!$this->stopping) {
            $read   = [$this->mainSocket];
            foreach ($this->sessions as $s) {
                $read[] = $s['sock'];
            }
            $write   = null;
            $except  = null;
            $n = @socket_select($read, $write, $except, 0, self::SELECT_TIMEOUT_USEC);

            if ($n === false) {
                // Interrupted by signal — loop back and re-check $this->stopping
                // and let the signal handler set worker state.
                $read = [];
            }

            foreach ($read as $sock) {
                if ($sock === $this->mainSocket) {
                    $this->handleIncomingOnMainSocket();
                } else {
                    $this->handleIncomingOnSessionSocket($sock);
                }
            }

            $this->expireIdleSessions();

            // Non-blocking poll of the beanstalk ping tube so the supervisor's
            // keep-alive checks resolve within one select cycle.
            try {
                $this->client_queue->wait(0);
            } catch (\Throwable) {
                // Beanstalk hiccups must not crash the TFTP server.
            }
        }

        $this->cleanupAllSessions();
    }

    /**
     * Read a packet from the listening socket; expected to be RRQ.
     */
    private function handleIncomingOnMainSocket(): void
    {
        $buf  = '';
        $from = '';
        $port = 0;
        $n = @socket_recvfrom($this->mainSocket, $buf, 65535, 0, $from, $port);
        if ($n === false || $n < 4 || $buf === '') {
            return;
        }

        $opcode = (ord($buf[0]) << 8) | ord($buf[1]);
        if ($opcode === self::OP_WRQ) {
            SystemMessages::sysLogMsg(self::LOG_TAG, "WRQ from $from:$port rejected (read-only server)", LOG_NOTICE);
            $this->sendErrorTo($this->mainSocket, $from, $port, self::ERR_ILLEGAL_OP, 'Write not supported');
            return;
        }
        if ($opcode !== self::OP_RRQ) {
            // Spurious packet to port 69 (e.g. stray ACK) — ignore.
            return;
        }

        if (count($this->sessions) >= self::MAX_SESSIONS) {
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                "RRQ from $from:$port refused: session cap MAX_SESSIONS=" . self::MAX_SESSIONS . ' reached',
                LOG_WARNING
            );
            $this->sendErrorTo($this->mainSocket, $from, $port, self::ERR_NOT_DEFINED, 'Server busy');
            return;
        }

        $parsed = $this->parseRrq($buf);
        if ($parsed === null) {
            $this->sendErrorTo($this->mainSocket, $from, $port, self::ERR_NOT_DEFINED, 'Malformed RRQ');
            return;
        }
        [$filename, $mode, $options] = $parsed;

        if (strtolower($mode) !== 'octet' && strtolower($mode) !== 'netascii') {
            $this->sendErrorTo($this->mainSocket, $from, $port, self::ERR_NOT_DEFINED, 'Unsupported mode');
            return;
        }

        $resolved = $this->resolveFile($filename, $from);
        if ($resolved === null) {
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                sprintf('RRQ %s from %s:%d -> file not found', $filename, $from, $port),
                LOG_NOTICE
            );
            $this->sendErrorTo($this->mainSocket, $from, $port, self::ERR_FILE_NOT_FOUND, 'File not found');
            return;
        }

        // Per-session ephemeral socket so the supervisor can keep listening on 69.
        $sessionSock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sessionSock === false) {
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                'socket_create for session failed: ' . socket_strerror(socket_last_error()),
                LOG_ERR
            );
            if ($resolved['tempfile'] && is_file($resolved['path'])) {
                unlink($resolved['path']);
            }
            return;
        }
        @socket_bind($sessionSock, '0.0.0.0', 0);

        $fh = @fopen($resolved['path'], 'rb');
        if ($fh === false) {
            SystemMessages::sysLogMsg(self::LOG_TAG, "fopen failed for {$resolved['path']}", LOG_ERR);
            socket_close($sessionSock);
            if ($resolved['tempfile'] && is_file($resolved['path'])) {
                unlink($resolved['path']);
            }
            $this->sendErrorTo($this->mainSocket, $from, $port, self::ERR_ACCESS, 'Cannot read file');
            return;
        }

        $negotiated = $this->negotiateOptions($options, $resolved['size']);

        $session = [
            'id'                => $this->makeSessionId($from, $port),
            'sock'              => $sessionSock,
            'client_ip'         => $from,
            'client_port'       => $port,
            'filename'          => $filename,
            'fh'                => $fh,
            'tempfile'          => $resolved['tempfile'] ? $resolved['path'] : null,
            'blksize'           => $negotiated['blksize'],
            'tsize'             => $negotiated['tsize'],
            'block_num'         => 0,
            'last_block_data'   => '',
            'last_block_size'   => 0,
            'last_activity'     => time(),
            'retries'           => 0,
            'awaiting_oack_ack' => false,
        ];

        SystemMessages::sysLogMsg(
            self::LOG_TAG,
            sprintf(
                'RRQ %s from %s:%d accepted (size=%d blksize=%d %s)',
                $filename,
                $from,
                $port,
                $resolved['size'],
                $session['blksize'],
                $resolved['tempfile'] ? 'on-demand-config' : 'static-file'
            ),
            LOG_NOTICE
        );

        if (!empty($negotiated['responseOptions'])) {
            $session['awaiting_oack_ack'] = true;
            $this->sendOack($session, $negotiated['responseOptions']);
        } else {
            $this->sendNextBlock($session);
        }

        $this->sessions[$session['id']] = $session;
    }

    /**
     * Read an ACK / ERROR from one of the active per-session sockets.
     *
     * @param \Socket $sock
     */
    private function handleIncomingOnSessionSocket($sock): void
    {
        $session = null;
        $key     = '';
        foreach ($this->sessions as $k => $s) {
            if ($s['sock'] === $sock) {
                $session = $s;
                $key     = $k;
                break;
            }
        }
        if ($session === null) {
            return;
        }

        $buf  = '';
        $from = '';
        $port = 0;
        $n = @socket_recvfrom($sock, $buf, 65535, 0, $from, $port);
        if ($n === false || $n < 4) {
            return;
        }

        // Ignore packets from a different endpoint than the one that opened the
        // session — RFC 1350 §3 says we may send ERROR 5 (unknown TID); easier
        // to drop silently and avoid amplification.
        if ($from !== $session['client_ip'] || $port !== $session['client_port']) {
            return;
        }

        $opcode = (ord($buf[0]) << 8) | ord($buf[1]);
        if ($opcode === self::OP_ERROR) {
            $errCode = $n >= 4 ? ((ord($buf[2]) << 8) | ord($buf[3])) : -1;
            $msg     = $n > 4 ? rtrim(substr($buf, 4), "\0") : '';
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                "Client {$session['client_ip']}:{$session['client_port']} sent ERROR code=$errCode msg='$msg' — closing session",
                LOG_NOTICE
            );
            $this->closeSession($key);
            return;
        }
        if ($opcode !== self::OP_ACK || $n < 4) {
            return;
        }

        $ackBlock = (ord($buf[2]) << 8) | ord($buf[3]);

        if ($session['awaiting_oack_ack']) {
            // Per RFC 2347, the client ACKs the OACK with block 0; then we start
            // with DATA block 1.
            if ($ackBlock === 0) {
                $this->sessions[$key]['awaiting_oack_ack'] = false;
                $this->sendNextBlock($this->sessions[$key]);
            }
            return;
        }

        $expected = $session['block_num'] & 0xFFFF;
        if ($ackBlock !== $expected) {
            // Duplicate or out-of-order ACK — ignore. Timeout path will retry if needed.
            return;
        }

        // ACK for the last block. If the just-acked block was a short read
        // (size < blksize), the transfer is complete.
        if ($session['last_block_size'] < $session['blksize']) {
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                sprintf(
                    'Transfer complete: %s -> %s:%d (%d blocks, last_size=%d)',
                    $session['filename'],
                    $session['client_ip'],
                    $session['client_port'],
                    $session['block_num'],
                    $session['last_block_size']
                ),
                LOG_NOTICE
            );
            $this->closeSession($key);
            return;
        }

        $this->sendNextBlock($this->sessions[$key]);
    }

    /**
     * Drop sessions that have not seen activity for SESSION_TIMEOUT seconds,
     * or that have exhausted retries.
     */
    private function expireIdleSessions(): void
    {
        $now = time();
        foreach ($this->sessions as $key => &$session) {
            if (($now - $session['last_activity']) <= self::SESSION_TIMEOUT) {
                continue;
            }

            if ($session['retries'] >= self::MAX_RETRIES) {
                SystemMessages::sysLogMsg(
                    self::LOG_TAG,
                    sprintf(
                        'Session %s timed out after %d retries (%s -> %s:%d)',
                        $key,
                        $session['retries'],
                        $session['filename'],
                        $session['client_ip'],
                        $session['client_port']
                    ),
                    LOG_NOTICE
                );
                $this->closeSession($key);
                continue;
            }

            $session['retries']++;
            $session['last_activity'] = $now;
            if ($session['awaiting_oack_ack']) {
                // Cannot replay OACK easily — drop and let client re-RRQ.
                $this->closeSession($key);
                continue;
            }
            // Retransmit the last DATA packet.
            $this->sendDataPacket(
                $session['sock'],
                $session['client_ip'],
                $session['client_port'],
                $session['block_num'],
                $session['last_block_data']
            );
        }
        unset($session);
    }

    private function sendNextBlock(array &$session): void
    {
        $session['block_num']++;
        if ($session['fh'] !== null) {
            $offset = ($session['block_num'] - 1) * $session['blksize'];
            if (@fseek($session['fh'], $offset) === -1) {
                $session['last_block_data'] = '';
                $session['last_block_size'] = 0;
            } else {
                $data = @fread($session['fh'], $session['blksize']);
                if ($data === false) {
                    $data = '';
                }
                $session['last_block_data'] = $data;
                $session['last_block_size'] = strlen($data);
            }
        } else {
            $session['last_block_data'] = '';
            $session['last_block_size'] = 0;
        }
        $session['last_activity'] = time();
        $session['retries']       = 0;

        $this->sendDataPacket(
            $session['sock'],
            $session['client_ip'],
            $session['client_port'],
            $session['block_num'],
            $session['last_block_data']
        );
    }

    private function sendDataPacket($sock, string $ip, int $port, int $block, string $data): void
    {
        // The 16-bit block number wraps at 65535 for files larger than 32 MB at
        // blksize=512. Most modern clients accept the wrap silently (RFC 7440
        // option blksize2/windowsize handles this properly, but isn't in scope).
        $pkt = pack('nn', self::OP_DATA, $block & 0xFFFF) . $data;
        @socket_sendto($sock, $pkt, strlen($pkt), 0, $ip, $port);
    }

    private function sendOack(array $session, array $options): void
    {
        $buf = pack('n', self::OP_OACK);
        foreach ($options as $k => $v) {
            $buf .= $k . "\0" . $v . "\0";
        }
        @socket_sendto(
            $session['sock'],
            $buf,
            strlen($buf),
            0,
            $session['client_ip'],
            $session['client_port']
        );
    }

    private function sendErrorTo($sock, string $ip, int $port, int $code, string $message): void
    {
        $pkt = pack('nn', self::OP_ERROR, $code) . $message . "\0";
        @socket_sendto($sock, $pkt, strlen($pkt), 0, $ip, $port);
    }

    /**
     * Parses a TFTP RRQ:
     *   [2-byte opcode][filename\0][mode\0]([opt\0][value\0])*
     *
     * @return array{0:string,1:string,2:array<string,string>}|null
     */
    private function parseRrq(string $buf): ?array
    {
        $parts = explode("\0", substr($buf, 2));
        // explode produces an extra empty trailing element after the final \0.
        if (count($parts) < 3) {
            return null;
        }
        $filename = $parts[0];
        $mode     = $parts[1];
        if ($filename === '' || $mode === '') {
            return null;
        }

        $options = [];
        $i       = 2;
        while ($i + 1 < count($parts)) {
            $name  = $parts[$i];
            $value = $parts[$i + 1];
            if ($name === '') {
                break;
            }
            $options[strtolower($name)] = $value;
            $i += 2;
        }
        return [$filename, $mode, $options];
    }

    /**
     * Decides which RRQ options to acknowledge and clamps blksize to a sane range.
     *
     * @param array<string,string> $clientOptions
     * @return array{blksize:int,tsize:int|null,responseOptions:array<string,string>}
     */
    private function negotiateOptions(array $clientOptions, int $fileSize): array
    {
        $blksize        = self::DEFAULT_BLKSIZE;
        $tsize          = null;
        $responseOptions = [];

        if (isset($clientOptions['blksize'])) {
            $requested = (int)$clientOptions['blksize'];
            if ($requested >= 8 && $requested <= self::MAX_BLKSIZE) {
                $blksize = $requested;
                $responseOptions['blksize'] = (string)$blksize;
            } elseif ($requested > self::MAX_BLKSIZE) {
                $blksize = self::MAX_BLKSIZE;
                $responseOptions['blksize'] = (string)$blksize;
            }
        }
        if (isset($clientOptions['tsize'])) {
            $tsize = $fileSize;
            $responseOptions['tsize'] = (string)$fileSize;
        }
        if (isset($clientOptions['timeout'])) {
            $t = (int)$clientOptions['timeout'];
            // Bounded mirror — we don't actually adjust our session timeout but
            // the client is happy as long as it sees an echo back in OACK.
            if ($t >= 1 && $t <= 60) {
                $responseOptions['timeout'] = (string)$t;
            }
        }
        return ['blksize' => $blksize, 'tsize' => $tsize, 'responseOptions' => $responseOptions];
    }

    /**
     * Maps a requested TFTP filename to either a static firmware file or an
     * on-demand-generated phone config.
     *
     * @return array{path:string,tempfile:bool,size:int}|null
     */
    private function resolveFile(string $rawName, string $clientIp): ?array
    {
        $name = ltrim($rawName, '/');
        if ($name === '' || str_contains($name, "\0") || str_contains($name, '..')) {
            return null;
        }

        // 1) firmware/<vendor>/<file>  or  fw/<vendor>/<file>
        if (preg_match('#^(?:firmware|fw)/([a-z0-9_-]+)/([^/]+)$#i', $name, $m)) {
            $vendor = strtolower($m[1]);
            $file   = FirmwareRepository::sanitizeFilename($m[2]);
            $path   = $this->firmwareBase . '/' . $vendor . '/' . $file;
            return $this->fileInfoIfExists($path);
        }

        // 2) firmware/<file>  -> search every vendor dir
        if (preg_match('#^(?:firmware|fw)/([^/]+)$#i', $name, $m)) {
            $file = FirmwareRepository::sanitizeFilename($m[1]);
            if ($file !== '') {
                $hit = $this->searchAllVendorDirs($file);
                if ($hit !== null) {
                    return $hit;
                }
            }
        }

        // 3) Bare basename — phones often request "T46S-66.86.0.15.rom" with no
        //    path component. Search every vendor dir under the firmware tree.
        if (strpos($name, '/') === false) {
            $bareHit = $this->searchAllVendorDirs(FirmwareRepository::sanitizeFilename($name));
            if ($bareHit !== null) {
                return $bareHit;
            }
        }

        // 4) Per-MAC phone config generated on-demand.
        $mac    = $this->extractMacFromName($name);
        if ($mac === '') {
            return null;
        }
        $vendor = $this->inferVendor($mac, $name);
        if ($vendor === '') {
            return null;
        }

        // Vendor generators read several keys out of $req_data — most importantly
        // `ip_srv` (the SIP/HTTP server host that ends up in `account.N.sip_server_host`)
        // and `model` (used for handset count, logo mode, etc.). The HTTP route fills
        // these from query string parameters; on the TFTP path we synthesise them from
        // the device record and the module settings.
        $settings = ModuleAutoprovision::findFirst();
        $ipSrv    = $settings !== null ? (string)($settings->pbx_host ?? '') : '';
        $model    = '';
        $device   = ModuleAutoprovisionDevice::findFirst([
            'mac = :mac:',
            'bind' => ['mac' => $mac],
        ]);
        if ($device !== null) {
            $mm = (string)$device->manufacturer_model;
            if (str_contains($mm, '/')) {
                $model = strtolower(trim(explode('/', $mm, 2)[1]));
            }
        }

        $autoprov = new Autoprovision();
        $cfgPath  = $autoprov->generateConfigPhone([
            'mac'    => $mac,
            'vendor' => $vendor,
            'model'  => $model,
            'ip_srv' => $ipSrv,
        ]);
        if ($cfgPath === '' || !is_file($cfgPath)) {
            return null;
        }
        $size = (int)@filesize($cfgPath);
        return ['path' => $cfgPath, 'tempfile' => true, 'size' => $size];
    }

    private function searchAllVendorDirs(string $file): ?array
    {
        if ($file === '') {
            return null;
        }
        foreach (array_keys(FirmwareRepository::VENDOR_EXTENSIONS) as $vendor) {
            $path = $this->firmwareBase . '/' . $vendor . '/' . $file;
            $info = $this->fileInfoIfExists($path);
            if ($info !== null) {
                return $info;
            }
        }
        return null;
    }

    private function fileInfoIfExists(string $path): ?array
    {
        $real     = realpath($path);
        $baseReal = realpath($this->firmwareBase);
        // Both realpath calls must succeed for the chroot check below to be
        // meaningful — if $baseReal is false (missing dir), str_starts_with
        // would compare against the literal '/' prefix and silently let any
        // absolute path through. Fail closed.
        if ($real === false || $baseReal === false || $baseReal === '') {
            return null;
        }
        if (!str_starts_with($real, $baseReal . DIRECTORY_SEPARATOR)) {
            return null;
        }
        if (!is_file($real)) {
            return null;
        }
        return ['path' => $real, 'tempfile' => false, 'size' => (int)@filesize($real)];
    }

    /**
     * Pulls a 12-hex-char MAC out of a TFTP filename. Accepts:
     *   - 001565aabbcc.cfg / .xml / .htm
     *   - f001565aabbcc.cfg (Fanvil)
     *   - colons or hyphens between bytes (00:15:65:aa:bb:cc.cfg)
     */
    private function extractMacFromName(string $filename): string
    {
        $base = strtolower(basename($filename));
        // Strip extension if present.
        $dot = strrpos($base, '.');
        $stem = $dot === false ? $base : substr($base, 0, $dot);
        // Strip separators so 00:15:65:... becomes 001565...
        $stem = str_replace([':', '-'], '', $stem);
        if (preg_match('/([0-9a-f]{12})$/', $stem, $m)) {
            return $m[1];
        }
        return '';
    }

    /**
     * Best-effort vendor detection: first consult the device record (PnP-seeded),
     * then fall back to filename hints.
     */
    private function inferVendor(string $mac, string $filename): string
    {
        $device = ModuleAutoprovisionDevice::findFirst([
            'mac = :mac:',
            'bind' => ['mac' => $mac],
        ]);
        if ($device !== null) {
            $mm = strtolower((string)$device->manufacturer_model);
            foreach (array_keys(FirmwareRepository::VENDOR_EXTENSIONS) as $vendor) {
                if (str_contains($mm, $vendor)) {
                    return $vendor;
                }
            }
        }

        $base = strtolower(basename($filename));
        // Fanvil convention: f<MAC>.cfg
        if (preg_match('/^f[0-9a-f]{12}\.cfg$/', $base)) {
            return 'fanvil';
        }
        if (str_ends_with($base, '.cfg')) {
            return 'yealink';
        }
        if (str_ends_with($base, '.xml') || str_ends_with($base, '.htm')) {
            return 'snom';
        }
        return '';
    }

    private function closeSession(string $key): void
    {
        if (!isset($this->sessions[$key])) {
            return;
        }
        $s = $this->sessions[$key];
        if ($s['fh'] !== null && is_resource($s['fh'])) {
            fclose($s['fh']);
        }
        if ($s['tempfile'] !== null && is_file($s['tempfile'])) {
            unlink($s['tempfile']);
        }
        if (is_resource($s['sock'])) {
            socket_close($s['sock']);
        }
        unset($this->sessions[$key]);
    }

    /**
     * Shutdown function — release every socket / file handle / temp file so a
     * crash never leaks them. Idempotent.
     */
    public function cleanupAllSessions(): void
    {
        foreach (array_keys($this->sessions) as $key) {
            $this->closeSession($key);
        }
        if ($this->mainSocket !== null) {
            @socket_close($this->mainSocket);
            $this->mainSocket = null;
        }
    }

    protected function handleSignalUsr1(): void
    {
        // SIGUSR1 is the supervisor's graceful-restart signal; flip the loop
        // flag so listen() exits cleanly, draining active sessions on the way.
        $this->stopping     = true;
        $this->needRestart  = true;
    }

    private function makeSessionId(string $ip, int $port): string
    {
        // (ip, port) uniquely identifies a TFTP client's transaction; collisions
        // would mean two simultaneous RRQs from the exact same source port,
        // which the kernel cannot deliver to two sockets anyway.
        return $ip . ':' . $port;
    }
}

// Worker entry point — matches WorkerProvisioningServerPnP's footer.
$workerClassname = WorkerTftpServer::class;
if (isset($argv) && count($argv) > 1) {
    cli_set_process_title($workerClassname);
    try {
        $worker = new $workerClassname();
        $worker->start($argv);
    } catch (\Throwable $e) {
        CriticalErrorsHandler::handleExceptionWithSyslog($e);
    }
}
