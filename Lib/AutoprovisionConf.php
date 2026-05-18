<?php

declare(strict_types=1);
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */

namespace Modules\ModuleAutoprovision\Lib;

use MikoPBX\Common\Models\FirewallRules;
use MikoPBX\Core\System\Configs\NginxConf;
use MikoPBX\Core\Workers\Cron\WorkerSafeScriptsCore;
use MikoPBX\Core\Workers\Libs\WorkerModelsEvents\Actions\ReloadFirewallAction;
use MikoPBX\Core\Workers\Libs\WorkerModelsEvents\Actions\ReloadNginxAction;
use MikoPBX\Core\Workers\WorkerModelsEvents;
use MikoPBX\Modules\Config\ConfigClass;
use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleAutoprovision\Lib\RestAPI\Controllers\GetController;
use MikoPBX\Core\System\{PBX, Processes, System, Util};
use Modules\ModuleAutoprovision\Models\{ModuleAutoprovision};

class AutoprovisionConf extends ConfigClass
{
    public const SIP_USER     = 'apv-miko-pbx';
    public const BASE_URI     = '/pbxcore/api/autoprovision-http';

    // Fallback port used when settings haven't been seeded yet (e.g. between
    // schema creation and PbxExtensionSetup::installDB() finishing). Same value
    // is written into the DB as the default at install time.
    public const DEFAULT_HTTP_PORT = 8480;

    private const ALLOWED_IMG_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'bmp', 'ico', 'dob'];

    /**
     * Returns the SIP secret for the autoprovision peer.
     * Stored in m_ModuleAutoprovision.sip_secret and generated at install time.
     */
    public static function getSipSecret(): string
    {
        $settings = ModuleAutoprovision::findFirst();
        $secret   = $settings->sip_secret ?? '';
        if ($secret === '') {
            // Fallback for legacy DB rows without the column populated yet.
            return self::SIP_USER;
        }
        return $secret;
    }

    /**
     * Returns the TCP port served by the module's dedicated nginx server-block.
     *
     * Falls back to {@see self::DEFAULT_HTTP_PORT} when the value in the DB is
     * missing or invalid — keeps the firewall rule and the nginx block in sync
     * even on partially-upgraded installs.
     */
    public static function getHttpPort(): int
    {
        $settings = ModuleAutoprovision::findFirst();
        $raw      = trim((string)($settings->http_port ?? ''));
        if ($raw === '') {
            return self::DEFAULT_HTTP_PORT;
        }
        $port = filter_var($raw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1024, 'max_range' => 65535],
        ]);
        return $port === false ? self::DEFAULT_HTTP_PORT : $port;
    }

    /**
     * Returns module workers to start it at WorkerSafeScript
     *
     * The TFTP worker is registered only when the admin has switched it on —
     * keeping it out of the supervisor list when disabled prevents the binary
     * from binding UDP/69 (it's privileged on most distros) and avoids the
     * per-cycle re-check WorkerSafeScripts does on every entry.
     */
    public function getModuleWorkers(): array
    {
        $workers = [
            [
                'type'   => WorkerSafeScriptsCore::CHECK_BY_BEANSTALK,
                'worker' => WorkerProvisioningServerPnP::class,
            ],
        ];
        if (self::isTftpEnabled()) {
            $workers[] = [
                'type'   => WorkerSafeScriptsCore::CHECK_BY_BEANSTALK,
                'worker' => WorkerTftpServer::class,
            ];
        }
        return $workers;
    }

    /**
     * True when the admin toggled the pure-PHP TFTP provisioning channel on.
     * Persisted as a string flag in m_ModuleAutoprovision.tftp_enabled.
     */
    public static function isTftpEnabled(): bool
    {
        $settings = ModuleAutoprovision::findFirst();
        return $settings !== null && ((string)$settings->tftp_enabled) === '1';
    }

    /**
     * Process CoreAPI requests under root rights.
     * Only methods in {@see self::REST_ACTIONS} are dispatchable.
     */
    public function moduleRestAPICallback(array $request): PBXApiResult
    {
        $res            = new PBXApiResult();
        $res->processor = __METHOD__;

        $action = $request['action'] ?? '';
        $data   = $request['data'] ?? [];

        $res = match ($action) {
            'getProvisionConfig' => $this->getProvisionConfig($data),
            'getImgFile'         => $this->getImgFile($data),
            'reload'             => $this->reloadProvisioning(),
            default              => (function () use ($request) {
                $r          = new PBXApiResult();
                $r->success = false;
                $r->data    = ['error' => 'Unknown action', 'request' => $request];
                return $r;
            })(),
        };

        return $res;
    }

    /**
     * Reloads dialplan + SIP after the settings page saved new configuration.
     * Invoked from the JS layer via /pbxcore/api/modules/ModuleAutoprovision/reload.
     *
     * Also refreshes the firewall rows + nginx server-block in case the admin
     * changed http_port — without this, the new port wouldn't open in iptables
     * nor be picked up by the dedicated nginx listener until the next module
     * enable cycle.
     */
    private function reloadProvisioning(): PBXApiResult
    {
        $this->syncFirewallPort();
        PBX::dialplanReload();
        PBX::sipReload();
        WorkerModelsEvents::invokeAction(ReloadNginxAction::class);
        WorkerModelsEvents::invokeAction(ReloadFirewallAction::class);
        // Stop the TFTP worker if the admin just turned the feature off; the
        // supervisor will respawn it on the next tick if isTftpEnabled() flips
        // back on. Without this, the listener would stay bound to UDP/69 until
        // the next module enable cycle.
        if (!self::isTftpEnabled()) {
            Processes::processWorker('', '', WorkerTftpServer::class, 'stop');
        } else {
            System::invokeActions(['cron' => 0]);
        }

        $res          = new PBXApiResult();
        $res->success = true;
        return $res;
    }

    /**
     * Brings the existing FirewallRules rows in line with the current http_port.
     *
     * The rows are first seeded by PbxExtensionState::enableFirewallSettings on
     * module enable; once they exist this method keeps them in sync with the
     * user-edited port so {@see ReloadFirewallAction} regenerates iptables with
     * the correct value. New rows are only created if none exist yet (fallback
     * for upgrades from versions that never had this hook).
     */
    private function syncFirewallPort(): void
    {
        $port  = (string)self::getHttpPort();
        $rules = FirewallRules::findByCategory('MODULEAUTOPROVISION');
        if ($rules->count() === 0) {
            return;
        }
        foreach ($rules as $rule) {
            if ($rule->portfrom === $port && $rule->portto === $port) {
                continue;
            }
            $rule->portfrom    = $port;
            $rule->portto      = $port;
            $rule->portFromKey = 'AutoprovisionHttpPort';
            $rule->portToKey   = 'AutoprovisionHttpPort';
            $rule->save();
        }
    }

    private function getProvisionConfig(array $request): PBXApiResult
    {
        $res           = new PBXApiResult();
        $autoprovision = new Autoprovision();
        $filename      = $autoprovision->generateConfigPhone($request);
        if ($filename !== '' && file_exists($filename)) {
            $res->success = true;
            $res->data    = [
                'fpassthru' => [
                    'filename'     => $filename,
                    'content_type' => 'text/plain',
                    'need_delete'  => true,
                ],
            ];
        }
        return $res;
    }

    private function getImgFile(array $request): PBXApiResult
    {
        $res = new PBXApiResult();

        $requested = (string)($request['file'] ?? '');
        // Strip any path component to prevent traversal.
        $basename  = basename($requested);
        if ($basename === '' || $basename !== $requested) {
            return $res;
        }

        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_IMG_EXTENSIONS, true)) {
            return $res;
        }

        $imgDir   = realpath($this->moduleDir . '/public/assets/img');
        $filename = $imgDir === false ? '' : realpath($imgDir . '/' . $basename);
        if ($filename === false || $filename === '' || !str_starts_with($filename, $imgDir . DIRECTORY_SEPARATOR)) {
            return $res;
        }

        if (file_exists($filename)) {
            $res->success = true;
            $res->data    = [
                'fpassthru' => [
                    'filename'     => $filename,
                    'content_type' => $this->guessImageMime($filename),
                    'need_delete'  => false,
                ],
            ];
        }
        return $res;
    }

    private function guessImageMime(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return match ($ext) {
            'png'          => 'image/png',
            'jpg', 'jpeg'  => 'image/jpeg',
            'gif'          => 'image/gif',
            'svg'          => 'image/svg+xml',
            'webp'         => 'image/webp',
            'bmp'          => 'image/bmp',
            'ico'          => 'image/x-icon',
            default        => 'application/octet-stream',
        };
    }

    /**
     * Returns array of additional routes for PBXCoreREST interface from module
     *
     * [ControllerClass, ActionMethod, RequestTemplate, HttpMethod, RootUrl, NoAuth ]
     *
     * @return array
     * @example
     *  [[GetController::class, 'callAction', '/pbxcore/api/backup/{actionName}', 'get', '/', false],
     */
    public function getPBXCoreRESTAdditionalRoutes(): array
    {
        $routes = [
            [GetController::class, 'getConfig',      '/pbxcore/api/autoprovision/getcfg', 'get', '/', true],
            [GetController::class, 'getImg',         '/pbxcore/api/autoprovision/getimg', 'get', '/', true],
        ];
        $uri = "";
        for ($i = 1; $i <= 35; $i++) {
            $uri .= "/{p$i}";
            $routes[] = [GetController::class, 'getConfigStatic', self::BASE_URI.$uri, 'get', '/', true];
        }
        return $routes;
    }

    /**
     * Генератор сеции пиров для pjsip.conf
     *
     *
     * @return string
     */
    public function generatePeersPj(): string
    {
        $conf = '';
        $lang = $this->generalSettings['PBXLanguage'];

        $options = [
            'type'     => 'auth',
            'username' => self::SIP_USER,
            'password' => self::getSipSecret(),
        ];
        $conf    .= "[".self::SIP_USER."] \n";
        $conf    .= Util::overrideConfigurationArray($options, null, 'auth');

        $options = [
            'type'              => 'aor',
            'qualify_frequency' => '60',
            'qualify_timeout'   => '5',
            'max_contacts'      => '100',
        ];
        $conf    .= "[".self::SIP_USER."] \n";
        $conf    .= Util::overrideConfigurationArray($options, null, 'aor');

        $options = [
            'type'                 => 'endpoint',
            'transport'            => 'transport-udp',
            'context'              => 'autoprovision-internal',
            // 'disallow'  => 'all',
            'allow'                => 'all',
            'rtp_symmetric'        => 'yes',
            'force_rport'          => 'yes',
            'rewrite_contact'      => 'yes',
            'ice_support'          => 'yes',
            'direct_media'         => 'no',
            'callerid'             => self::SIP_USER." <".self::SIP_USER.">",
            'language'             => $lang,
            'device_state_busy_at' => 1,
            'aors'                 => self::SIP_USER,
            'auth'                 => self::SIP_USER,
            'outbound_auth'        => self::SIP_USER,
        ];
        // ---------------- //
        $conf .= "[".self::SIP_USER."] \n";
        $conf .= Util::overrideConfigurationArray($options, null, 'endpoint');

        return $conf;
    }

    /**
     * Подключаем контекст настройки телефона.
     *
     * @return string
     */
    public function getIncludeInternal(): string
    {
        // Генерация внутреннего номерного плана.
        return "include => autoprovision-internal \n";
    }

    /**
     * Генерация дополнительных контекстов.
     *
     * @return string
     */
    public function extensionGenContexts(): string
    {
        $settings = ModuleAutoprovision::findFirst();
        if ($settings === null || empty($settings->extension)) {
            return '';
        }
        $extension = (string)$settings->extension;
        $extConf   = PHP_EOL . '[autoprovision-internal]' . PHP_EOL;
        // Pattern-match the configured exten and hand control to the AGI script.
        $extConf  .= "exten => _{$extension}!,1,NoOp(Try autoprovision)" . PHP_EOL . "\t";
        $extConf  .= 'same => n,Set(PT1C_VIA=${PJSIP_HEADER(read,Via,1)})' . PHP_EOL;
        $extConf  .= "same => n,AGI({$this->moduleDir}/agi-bin/ModuleAutoprovisionAGI.php)" . PHP_EOL;
        return $extConf;
    }

    /**
     * Runs after the module is enabled. Reloads dialplan + SIP, restarts cron,
     * and kicks off the provisioning worker so the multicast listener becomes active.
     */
    public function onAfterModuleEnable(): void
    {
        PBX::dialplanReload();
        PBX::sipReload();
        // System::invokeActions(['cron'=>0]) re-runs the supervisor which picks
        // up every entry returned by getModuleWorkers() — including the TFTP
        // worker when the admin has flipped the toggle on — so we don't need
        // to spawn it explicitly here.
        System::invokeActions(['cron' => 0]);
        $workerPath = Util::getFilePathByClassName(WorkerProvisioningServerPnP::class);
        $phpPath    = Util::which('php');
        Processes::mwExec(escapeshellarg($phpPath) . ' -f ' . escapeshellarg($workerPath));
    }

    /**
     * Builds the dedicated nginx server-block that serves provisioning configs
     * over plain HTTP on {@see self::getHttpPort()}.
     *
     * Phones generally cannot follow the admin UI's HTTPS redirect — a separate
     * listener bypasses it without patching the core nginx template. The block
     * exposes only {@see self::BASE_URI}/* and the small assets endpoint used
     * by the module (`/pbxcore/api/autoprovision/getimg`); the rest of /pbxcore
     * stays behind the main listener so this port does not become an admin
     * back-door.
     */
    public function createNginxServers(): string
    {
        $port        = self::getHttpPort();
        $baseUri     = self::BASE_URI;
        $firmwareDir = $this->moduleDir . '/firmware';

        // Server-block is isolated (no shared `~ \.php$` handler), so we route
        // each accepted URI to a single internal location that talks to PHP-FPM
        // directly. Explicit `last` flag re-runs location matching after the
        // rewrite, landing on the `= /pbxcore/index.php` internal location.
        //
        // NginxConf::buildServerBlock() embeds this content via preg_replace,
        // whose replacement argument interprets `$N` as backreferences. Without
        // the leading backslash, `$1` would be consumed and the rewrite would
        // produce `_url=/` — every PnP fetch landing on a 404. The `\\\$1`
        // heredoc sequence yields the literal `\$1` in $content, which the
        // preg_replace pass then emits as `$1` in the final nginx config.
        //
        // The /firmware/ alias serves phone firmware blobs straight from disk;
        // proxying them through PHP would OOM the worker on a 40 MB Yealink T5x
        // .rom file. Read-only by design — uploads go through the v3 REST path.
        $content = <<<NGINX
    location ^~ {$baseUri}/ {
        rewrite ^/pbxcore/(.*)\$ /pbxcore/index.php?_url=/\\\$1 last;
    }

    location ~ ^/pbxcore/api/autoprovision/(getcfg|getimg)\$ {
        rewrite ^/pbxcore/(.*)\$ /pbxcore/index.php?_url=/\\\$1 last;
    }

    location ^~ /firmware/ {
        alias {$firmwareDir}/;
        autoindex off;
        add_header Cache-Control "public, max-age=3600";
        # Log every phone fetch through the OS syslog under the autoprovision_firmware
        # tag. nginx forbids "-" in syslog tag names (only [A-Za-z0-9_]) — using
        # the dashed variant prevents the server-block from loading, so the
        # provisioning port silently fails to bind on boot.
        access_log syslog:server=unix:/dev/log,tag=autoprovision_firmware combined;
        limit_except GET HEAD { deny all; }
    }

    location = /pbxcore/index.php {
        internal;
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /usr/www/sites/pbxcore/index.php;
    }

    # Everything else on this port is intentionally inaccessible.
    location / {
        return 404;
    }
NGINX;

        return NginxConf::buildServerBlock($port, false, $content);
    }

    /**
     * Opens the provisioning port in the firewall so phones can reach it.
     *
     * Returned as a single allow rule under category "Autoprovision". The core
     * IptablesConf layer picks this up via {@see PBXConfModulesProvider} hooks
     * (see GET_DEFAULT_FIREWALL_RULES).
     */
    public function getDefaultFirewallRules(): array
    {
        $port = (string)self::getHttpPort();
        $rules = [
            [
                'portfrom' => $port,
                'portto'   => $port,
                'protocol' => 'tcp',
                'name'     => 'AutoprovisionHttpPort',
            ],
        ];
        // TFTP rides UDP/69 (RFC 1350). The firewall row is always seeded so
        // toggling the feature on later doesn't require a re-install; the
        // worker is what actually opens the listener.
        $rules[] = [
            'portfrom' => '69',
            'portto'   => '69',
            'protocol' => 'udp',
            'name'     => 'AutoprovisionTftpPort',
        ];
        return [
            'ModuleAutoprovision' => [
                'rules'     => $rules,
                'action'    => 'allow',
                'shortName' => 'Autoprovision',
            ],
        ];
    }
}