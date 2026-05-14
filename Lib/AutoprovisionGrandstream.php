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

class AutoprovisionGrandstream extends Autoprovision implements ConfManager
{
    /**
     * Создаёт конфигурационный XML для Grandstream телефона.
     *
     * Формат: gs_provision XML c P-кодами. Линия 1 настраивается полностью;
     * расширенные параметры и многолинейная конфигурация передаются через
     * INI-секцию [grandstream] в additional_params.
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

        $filename = "{$this->tempDir}/cfg{$req_data['mac']}.xml";

        $sipPort             = $this->mikoPBXConfig->getGeneralSettings('SIPPort');
        $voiceMailExtension  = $this->mikoPBXConfig->getGeneralSettings('VoicemailExten');

        $sipPeer = $sip_peers['1'] ?? null;

        $cfg  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $cfg .= "<gs_provision version=\"1\">\n";
        $cfg .= "    <mac>" . strtoupper($req_data['mac']) . "</mac>\n";
        $cfg .= "    <config version=\"1\">\n";

        if ($sipPeer !== null) {
            // Account 1: enable + register
            $cfg .= "        <P271>1</P271>\n";
            $cfg .= "        <P270>" . htmlspecialchars("MikoPBX ({$sipPeer['extension']})", ENT_XML1) . "</P270>\n";
            $cfg .= "        <P3>" . htmlspecialchars($sipPeer['callerid'], ENT_XML1) . "</P3>\n";
            $cfg .= "        <P47>" . htmlspecialchars($req_data['ip_srv'], ENT_XML1) . "</P47>\n";
            $cfg .= "        <P63>{$sipPort}</P63>\n";
            $cfg .= "        <P35>" . htmlspecialchars($sipPeer['extension'], ENT_XML1) . "</P35>\n";
            $cfg .= "        <P36>" . htmlspecialchars($sipPeer['extension'], ENT_XML1) . "</P36>\n";
            $cfg .= "        <P34>" . htmlspecialchars($sipPeer['secret'], ENT_XML1) . "</P34>\n";
            $cfg .= "        <P33>" . htmlspecialchars((string)$voiceMailExtension, ENT_XML1) . "</P33>\n";
        }

        // Отключаем boot-time DHCP option 66 / PnP, чтобы телефон не
        // переподписывался на чужой провижн-сервер после первой настройки.
        $cfg .= "        <P1359>0</P1359>\n";

        $extra = trim($s['grandstream'] ?? '');
        if ($extra !== '') {
            $cfg .= $extra . "\n";
        }

        $cfg .= "    </config>\n";
        $cfg .= "</gs_provision>\n";

        file_put_contents($filename, $cfg);

        return $filename;
    }
}
