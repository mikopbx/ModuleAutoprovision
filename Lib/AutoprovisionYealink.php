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

class AutoprovisionYealink extends Autoprovision implements ConfManager
{
    /**
     * Создает конфигурационный файл для Yealink телефона.
     *
     * @param $req_data
     * @param $sip_peers
     *
     * @return string
     */
    public function generateConfig($req_data, $sip_peers): string
    {
        /** @var ModuleAutoprovision $settings */
        $settings = ModuleAutoprovision::findFirst();
        $s        = self::parseIniSettings($settings->additional_params);

        $filename = "{$this->tempDir}/{$req_data['mac']}.txt";

        $sip_port             = $this->mikoPBXConfig->getGeneralSettings('SIPPort');
        $web_port             = $this->mikoPBXConfig->getGeneralSettings('WEBPort');
        $voice_mail_extension = $this->mikoPBXConfig->getGeneralSettings('VoicemailExten');
        $cfg                  = "#!version:1.0.0.1\r\n";
        foreach ($sip_peers as $line => $sip_peer) {
            // Enable or disable the account1, 0-Disabled (default), 1-Enabled;
            $cfg .= "account.{$line}.enable = 1\r\n";
            // Configure the label displayed on the LCD screen for account1.
            $cfg .= "account.{$line}.label = Askozia ({$sip_peer['extension']})\r\n";
            // Configure the display name of account1.
            $cfg .= "account.{$line}.display_name = {$sip_peer['callerid']}\r\n";
            // Configure the username and password for register authentication.
            $cfg .= "account.{$line}.auth_name = {$sip_peer['extension']}\r\n";
            $cfg .= "account.{$line}.user_name = {$sip_peer['extension']}\r\n";
            $cfg .= "account.{$line}.password = {$sip_peer['secret']}\r\n";
            $cfg .= "account.{$line}.sip_server_host = {$req_data['ip_srv']}\r\n";
            $cfg .= "account.{$line}.sip_server_port = {$sip_port}\r\n";

            $cfg .= "account.{$line}.transport = 0\r\n";
            $cfg .= "account.{$line}.codec.1.enable = 1\r\n";
            $cfg .= "account.{$line}.codec.1.payload_type = PCMU\r\n";
            $cfg .= "account.{$line}.codec.1.priority = 1\r\n";
            $cfg .= "account.{$line}.codec.1.rtpmap = 0\r\n";
            // Configure the type of SIP header(s) to carry the caller ID;
            // 0-FROM (default), 1-PAI 2-PAI-FROM, 3-PRID-PAI-FROM, 4-PAI-RPID-FROM, 5-RPID-FROM;
            $cfg .= "account.{$line}.cid_source = 4\r\n";

            // Configure the voice mail number of account1.
            $cfg .= "voice_mail.number.{$line} = {$voice_mail_extension}\r\n";
        }

        if ($req_data['model'] === 't21d') {
            $cfg .= "phone_setting.lcd_logo.mode=0\r\n";
        } elseif ($req_data['model'] === 'w52p') {
            // DECT база. Тут настройка особенная, несколько SIP аккаунтов - 5шт.
            // handset.1.name
            foreach ($sip_peers as $line => $sip_peer) {
                $cfg .= "handset.{$line}.name={$sip_peer['callerid']}\r\n";
            }
        } elseif ($req_data['model'] === 't19d') {
            $cfg .= "phone_setting.lcd_logo.mode=0\r\n";
        } elseif ($req_data['model'] === 'sip-t28p') {
            $path2img = '/pbxcore/api/modules/ModuleAutoprovision/getimg?file=logo-yealink-236x82.dob';
            $cfg      .= "lcd_logo.url = http://{$req_data['ip_srv']}:{$web_port}{$path2img}\r\n";
            $cfg      .= "phone_setting.lcd_logo.mode = 2\r\n";
        } else {
            $cfg .= "phone_setting.lcd_logo.mode=0\r\n";
        }
        $cfg .= "auto_provision.dhcp_option.enable = 0\r\n";

        $cfg .= "features.intercom.allow = 1\r\n";
        $cfg .= "features.intercom.mute = 0\r\n";
        $cfg .= "features.intercom.tone = 1\r\n";
        $cfg .= "features.intercom.barge = 1\r\n";

        // Configure DTMF sequences. It can be consisted of digits, alphabets, * and #.
        $featureAttendedTransfer = $this->mikoPBXConfig->getGeneralSettings('PBXFeatureAttendedTransfer');
        $cfg                     .= "features.dtmf.transfer = {$featureAttendedTransfer}\r\n";
        // Enable or disable the phone to send DTMF sequences during
        // a call when pressing the transfer soft key or the TRAN key; 0-Disabled (default), 1-Enabled;
        $cfg .= "features.dtmf.replace_tran = 1\r\n";
        // Enable or disable the headset prior feature; 0-Disabled (default), 1-Enabled;
        $cfg .= "features.headset_prior = 1\r\n";

        $cfg .= ($s['yealink'] ?? '') . "\n";
        file_put_contents($filename, $cfg);

        return $filename;
    }
}