<?php

declare(strict_types=1);
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */

namespace Modules\ModuleAutoprovision\Lib;

use MikoPBX\Core\Workers\Cron\WorkerSafeScriptsCore;
use MikoPBX\Modules\Config\ConfigClass;
use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleAutoprovision\Lib\RestAPI\Controllers\GetController;
use MikoPBX\Core\System\{PBX, Processes, System, Util};
use Modules\ModuleAutoprovision\Models\{ModuleAutoprovision};

class AutoprovisionConf extends ConfigClass
{
    public const SIP_USER     = 'apv-miko-pbx';
    public const BASE_URI     = '/pbxcore/api/autoprovision-http';

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
     * Returns module workers to start it at WorkerSafeScript
     */
    public function getModuleWorkers(): array
    {
        return [
            [
                'type'   => WorkerSafeScriptsCore::CHECK_BY_BEANSTALK,
                'worker' => WorkerProvisioningServerPnP::class,
            ],
        ];
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
     */
    private function reloadProvisioning(): PBXApiResult
    {
        PBX::dialplanReload();
        PBX::sipReload();
        $res          = new PBXApiResult();
        $res->success = true;
        return $res;
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
        System::invokeActions(['cron' => 0]);
        $workerPath = Util::getFilePathByClassName(WorkerProvisioningServerPnP::class);
        $phpPath    = Util::which('php');
        Processes::mwExec(escapeshellarg($phpPath) . ' -f ' . escapeshellarg($workerPath));
    }
}