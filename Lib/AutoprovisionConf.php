<?php
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
    public const SIP_USER   = 'apv-miko-pbx';
    public const SIP_SECRET = 'apv-miko-pbx';

    /**
     * Returns module workers to start it at WorkerSafeScript
     * @return array
     */
    public function getModuleWorkers(): array
    {
        return [
            [
                'type'           => WorkerSafeScriptsCore::CHECK_BY_PID_NOT_ALERT,
                'worker'         => WorkerProvisioningServerPnP::class,
            ],
        ];
    }

    /**
     *  Process CoreAPI requests under root rights
     *
     * @param array $request
     *
     * @return PBXApiResult An object containing the result of the API call.
     */
    public function moduleRestAPICallback(array $request): PBXApiResult
    {
        $res = new PBXApiResult();
        $res->processor = __METHOD__;

        $action = $request['action']??'';
        $data   = $request['data']??[];
        if(method_exists($this, $action)){
            $res = $this->$action($data);
        }else{
            $res->success = false;
            $res->data = $request;
        }
        return $res;
    }

    public function getProvisionConfig($request):PBXApiResult{
        /** @var PBXApiResult $res */
        $res = new PBXApiResult();

        $autoprovision = new Autoprovision();
        $filename    = $autoprovision->generateConfigPhone($request);
        $need_delete = true;
        if (file_exists($filename)) {
            $res->success = true;
            $res->data = [
                'filename'    => $filename,
                'fpassthru'   => true,
                'need_delete' => $need_delete,
            ];
        }
        return $res;
    }
    public function getImgFile($request):PBXApiResult{
        $res = new PBXApiResult();
        $filename = "$this->moduleDir/assets/img/{$request['file']}";
        if (file_exists($filename)) {
            $res->success = true;
            $res->data = [
                'filename'    => $filename,
                'fpassthru'   => true,
                'need_delete' => false,
            ];
        }
        return $res;
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
        return [
            [GetController::class, 'getConfig', '/pbxcore/api/autoprovision/getcfg', 'get', '/', true],
            [GetController::class, 'getImg', '/pbxcore/api/autoprovision/getimg', 'get', '/', true],
        ];
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
            'password' => self::SIP_SECRET,
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
        if ($settings === null) {
            return '';
        }
        $ext_conf = PHP_EOL."[autoprovision-internal]".PHP_EOL;
        // Настройка телефона на конкретный exten.
        $ext_conf .= "exten => _$settings->extension!,1,NoOp(Try autoprovision)".PHP_EOL."\t";
        $ext_conf .= 'same => n,Set(PT1C_VIA=${PJSIP_HEADER(read,Via,1)})'.PHP_EOL;
        $ext_conf .= "same => n,AGI($this->moduleDir/agi-bin/ModuleAutoprovisionAGI.php)".PHP_EOL;
        return $ext_conf;
    }

    /**
     * Process after enable action in web interface
     *
     * @return void
     */
    public function onAfterModuleEnable(): void
    {
        PBX::dialplanReload();
        PBX::sipReload();
        System::invokeActions(['cron' => 0]);
        $workerPath = Util::getFilePathByClassName(WorkerProvisioningServerPnP::class);
        $phpPath    = Util::which('php');
        Processes::mwExec("$phpPath -f $workerPath");
    }
}