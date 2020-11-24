<?php
/**
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolay Beketov, 5 2020
 *
 */

namespace Modules\ModuleAutoprovision\Lib;


use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;

class AutoprovisionFanvil extends Autoprovision implements ConfManager
{
    /**
     * Создает конфигурационный файл для Fanvil телефона.
     *
     * @param $req_data
     * @param $sip_peers
     *
     * @return string
     */
    public function generateConfig($req_data, $sip_peers): string
    {
        if (($sip_peers['1'] ?? false) === false) {
            return '';
        }
        $sip_peer = $sip_peers['1'];


        $filename             = "{$this->tempDir}/{$req_data['mac']}.txt";
        $sip_port             = $this->mikoPBXConfig->getGeneralSettings('SIPPort');
        $voice_mail_extension = $this->mikoPBXConfig->getGeneralSettings('VoicemailExten');

        /** @var ModuleAutoprovision $settings */
        $settings = ModuleAutoprovision::findFirst();
        $s        = self::parseIniSettings($settings->additional_params);

        $cfg_arr   = [];
        $cfg_arr[] = '<<VOIP CONFIG FILE>>Version:2.0002';
        $cfg_arr[] = ($s['fanvil'] ?? '') . "\n";
        $cfg_arr[] = '<SIP CONFIG MODULE>';
        $cfg_arr[] = '--SIP Line List--  :';
        $cfg_arr[] = 'SIP1 Enable Reg    :1';
        $cfg_arr[] = 'SIP1 Phone Number  :' . $sip_peer['extension'];
        $cfg_arr[] = 'SIP1 Display Name  :' . $sip_peer['callerid'];
        $cfg_arr[] = 'SIP1 Sip Name      :' . $sip_peer['extension'];
        $cfg_arr[] = 'SIP1 Register Addr :' . $req_data['ip_srv'];
        $cfg_arr[] = 'SIP1 Register Port :' . $sip_port;
        $cfg_arr[] = 'SIP1 Register User :' . $sip_peer['extension'];
        $cfg_arr[] = 'SIP1 Register Pswd :' . $sip_peer['secret'];
        $cfg_arr[] = 'SIP1 MWI Num       :' . $voice_mail_extension;
        $cfg_arr[] = 'SIP1 Proxy User    :';
        $cfg_arr[] = 'SIP1 Proxy Pswd    :';
        $cfg_arr[] = 'SIP1 Proxy Addr    :';
        $cfg_arr[] = ($s['fanvil-sip'] ?? '') . "\n";

        // Configure the type of SIP header(s) to carry the caller ID;
        // 4-PAI-RPID-FROM
        $cfg_arr[] = '<TELE CONFIG MODULE>';
        $cfg_arr[] = 'SIP1 Caller Id Type:4';
        $cfg_arr[] = 'P1 Enable Intercom :1';
        $cfg_arr[] = 'P1 Intercom Mute   :0';
        $cfg_arr[] = 'P1 Intercom Tone   :1';
        $cfg_arr[] = 'P1 Intercom Barge  :1';
        $cfg_arr[] = ($s['fanvil-tele'] ?? '') . "\n";

        $cfg_arr[] = '<AUTOUPDATE CONFIG MODULE>';
        $cfg_arr[] = 'PNP Enable         :1';
        $cfg_arr[] = 'PNP IP             :224.0.1.75';
        $cfg_arr[] = 'PNP Port           :5060';
        $cfg_arr[] = 'PNP Transport      :0';
        $cfg_arr[] = 'PNP Interval       :1';
        $cfg_arr[] = ($s['fanvil-autoupdate'] ?? '') . "\n";
        $cfg_arr[] = '<<END OF FILE>>';

        file_put_contents($filename, implode("\n", $cfg_arr));

        return $filename;
    }
}