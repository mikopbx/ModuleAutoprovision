<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */

namespace Modules\ModuleAutoprovision\Lib;

use MikoPBX\Modules\Config\ConfigClass;
use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use MikoPBX\Core\System\{PBX, Processes, System, Util};
use Modules\ModuleAutoprovision\Models\{ModuleAutoprovision};

class AutoprovisionConf extends ConfigClass
{
    public const SIP_USER = 'apv_askozia';
    public const SIP_SECRET = 'apv_askozia_secret';

    private $sip_user;
    private $sip_secret;

    /**
     * Настройки для текущего класса.
     */
    public function getSettings(): void
    {
        $this->sip_user = self::SIP_USER;
        $this->sip_secret = self::SIP_SECRET;
    }


    /**
     *  Process CoreAPI requests under root rights
     *
     * @param array $request
     *
     * @return PBXApiResult
     * @throws \Exception
     */
    public function moduleRestAPICallback(array $request): PBXApiResult
    {
        $res = new PBXApiResult();
        $res->processor = __METHOD__;
        $res->success = false;
        $res->data = $request;

        $filename    = '';
        $need_delete = false;
        $action = strtoupper($request['action']);
        switch ($action){
            case 'GETCFG':
                $autoprovision = new Autoprovision();
                $filename    = $autoprovision->generateConfigPhone($request);
                $need_delete = true;
                break;
            case 'GETIMG':
                $filename = "{$this->moduleDir}/assets/img/{$request['file']}";
                break;
            default:
                $res->success = false;
                $res->messages[] = 'API action not found in moduleRestAPICallback ModuleAutoprovision;';
        }
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
            'username' => $this->sip_user,
            'password' => $this->sip_secret,
        ];
        $conf    .= "[{$this->sip_user}] \n";
        $conf    .= Util::overrideConfigurationArray($options, null, 'auth');

        $options = [
            'type'              => 'aor',
            'qualify_frequency' => '60',
            'qualify_timeout'   => '5',
            'max_contacts'      => '100',
        ];
        $conf    .= "[{$this->sip_user}] \n";
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
            'callerid'             => "{$this->sip_user} <{$this->sip_user}>",
            'language'             => $lang,
            'device_state_busy_at' => 1,
            'aors'                 => $this->sip_user,
            'auth'                 => $this->sip_user,
            'outbound_auth'        => $this->sip_user,
        ];
        // ---------------- //
        $conf .= "[{$this->sip_user}] \n";
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
        $exten = $settings->extension;

        $ext_conf = "\n[autoprovision-internal]\n";
        // Настройка телефона на конкретный exten.
        $ext_conf .= "exten => _{$exten}!,1,NoOp(Try autoprovision) \n\t";
        $ext_conf .= 'same => n,Set(PT1C_VIA=${PJSIP_HEADER(read,Via,1)})' . "\n";
        $ext_conf .= "same => n,AGI({$this->moduleDir}/agi-bin/ModuleAutoprovisionAGI.php)\n";

        return $ext_conf;
    }


    /**
     * Добавление задач в crond.
     *
     * @param $tasks
     */
    public function createCronTasks(&$tasks): void
    {
        if ( ! is_array($tasks)) {
            return;
        }
        $workerPath = Util::getFilePathByClassName(WorkerProvisioningServerPnP::class);
        $phpPath = Util::which('php');
        $tasks[]      = "*/1 * * * * {$phpPath} -f {$workerPath} > /dev/null 2> /dev/null\n";
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
        $phpPath = Util::which('php');
        Processes::mwExec("{$phpPath} -f {$workerPath}");
    }

}