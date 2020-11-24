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

class AutoprovisionSnom extends Autoprovision implements ConfManager
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

        $filename             = "{$this->tempDir}/{$req_data['mac']}.xml";
        $voice_mail_extension = $this->mikoPBXConfig->getGeneralSettings('VoicemailExten');

        $cfg = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $cfg .= '<settings>' . "\n";
        $cfg .= ($s['snom'] ?? '') . "\n";
        $cfg .= '    <phone-settings>' . "\n";
        foreach ($sip_peers as $line => $sip_peer) {
            $cfg .= "        <user_pname idx=\"{$line}\" perm=\"RW\">{$sip_peer['extension']}</user_pname>\n";
            $cfg .= "        <user_name idx=\"{$line}\" perm=\"RW\">{$sip_peer['extension']}</user_name>\n";
            $cfg .= "        <user_realname idx=\"{$line}\" perm=\"RW\">{$sip_peer['callerid']}</user_realname>\n";
            $cfg .= "        <user_pass idx=\"{$line}\" perm=\"RW\">{$sip_peer['secret']}</user_pass>\n";
            $cfg .= "        <user_host idx=\"{$line}\" perm=\"RW\">{$settings->pbx_host}</user_host>\n";
            $cfg .= "        <user_srtp idx=\"{$line}\" perm=\"RW\">off</user_srtp>\n";
            $cfg .= "        <user_mailbox idx=\"{$line}\" perm=\"RW\">{$voice_mail_extension}</user_mailbox>\n";
            $cfg .= '        <user_dp_str idx="' . $line . '" perm="RW">!([^#]%2b)#!sip:\1@\d!d</user_dp_str>' . "\n";
            $cfg .= '        <contact_source_sip_priority idx="INDEX" perm="PERMISSIONFLAG">PAI RPID FROM</contact_source_sip_priority>' . "\n";
        }
        $cfg .= "        <answer_after_policy perm=\"RW\">idle</answer_after_policy>\n";

        $cfg .= ($s['snom-phone-settings'] ?? '') . "\n";
        $cfg .= '    </phone-settings>' . "\n";
        $cfg .= '</settings>' . "\n";

        file_put_contents($filename, $cfg);

        return $filename;
    }
}