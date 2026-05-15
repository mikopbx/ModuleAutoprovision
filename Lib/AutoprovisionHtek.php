<?php

declare(strict_types=1);
/**
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolai Beketov, 5 2026
 *
 */

namespace Modules\ModuleAutoprovision\Lib;

use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;

class AutoprovisionHtek extends Autoprovision implements ConfManager
{
    /**
     * Создаёт конфигурационный файл для Htek (Hanlong UC-серии) телефона.
     *
     * Htek использует key=value формат, синтаксически близкий к Yealink, но
     * с другим набором ключей (account.N.* / sip.* префиксы).
     *
     * @param array $req_data Параметры запроса (требуется 'mac', 'ip_srv').
     * @param array $sip_peers SIP-аккаунты, индексированные по номеру линии.
     * @return string Путь к сгенерированному файлу.
     */
    public function generateConfig($req_data, $sip_peers): string
    {
        /** @var ModuleAutoprovision $settings */
        $settings = ModuleAutoprovision::findFirst();
        $s        = self::parseIniSettings($settings->additional_params);

        $filename = "{$this->tempDir}/cfg{$req_data['mac']}.cfg";

        $sipPort            = $this->mikoPBXConfig->getGeneralSettings('SIPPort');
        $voiceMailExtension = $this->mikoPBXConfig->getGeneralSettings('VoicemailExten');

        $cfg = "#!version:1.0.0.1\r\n";
        foreach ($sip_peers as $line => $sipPeer) {
            $cfg .= "account.{$line}.active = 1\r\n";
            $cfg .= "account.{$line}.label = MikoPBX ({$sipPeer['extension']})\r\n";
            $cfg .= "account.{$line}.display_name = {$sipPeer['callerid']}\r\n";
            $cfg .= "account.{$line}.auth_name = {$sipPeer['extension']}\r\n";
            $cfg .= "account.{$line}.user_name = {$sipPeer['extension']}\r\n";
            $cfg .= "account.{$line}.password = {$sipPeer['secret']}\r\n";
            $cfg .= "account.{$line}.sip_server.host = {$req_data['ip_srv']}\r\n";
            $cfg .= "account.{$line}.sip_server.port = {$sipPort}\r\n";
            $cfg .= "account.{$line}.transport = 0\r\n";
            $cfg .= "account.{$line}.voice_mail.number = {$voiceMailExtension}\r\n";

            $cfg .= "account.{$line}.codec.1.enable = 1\r\n";
            $cfg .= "account.{$line}.codec.1.payload_type = PCMU\r\n";
            $cfg .= "account.{$line}.codec.1.priority = 1\r\n";
        }

        // Отключаем DHCP option 66, чтобы телефон не уходил на чужой провижн.
        $cfg .= "auto_provision.dhcp_option.enable = 0\r\n";

        // Intercom / paging — те же дефолты, что у Yealink.
        $cfg .= "features.intercom.allow = 1\r\n";
        $cfg .= "features.intercom.mute = 0\r\n";
        $cfg .= "features.intercom.tone = 1\r\n";

        $featureAttendedTransfer = $this->mikoPBXConfig->getGeneralSettings('PBXFeatureAttendedTransfer');
        $cfg .= "features.dtmf.transfer = {$featureAttendedTransfer}\r\n";

        // Htek auto-image URL — the Htek firmware server config key.
        $firmwareUrl = (string)($req_data['firmware_url'] ?? '');
        if ($firmwareUrl !== '') {
            $cfg .= "auto_image_url = {$firmwareUrl}\r\n";
        }

        $cfg .= ($s['htek'] ?? '') . "\n";
        file_put_contents($filename, $cfg);

        return $filename;
    }
}
