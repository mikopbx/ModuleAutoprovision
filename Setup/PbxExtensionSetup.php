<?php

declare(strict_types=1);
/*
 * MikoPBX - free phone system for small business
 * Copyright © 2017-2024 Alexey Portnov and Nikolay Beketov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace Modules\ModuleAutoprovision\Setup;

use MikoPBX\Common\Models\Extensions;
use MikoPBX\Common\Models\PbxSettings;
use MikoPBX\Core\System\Processes;
use MikoPBX\Core\System\Util;
use MikoPBX\Modules\Setup\PbxExtensionSetupBase;
use Modules\ModuleAutoprovision\Lib\TemplateSeeder;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\Templates;
use Throwable;

class PbxExtensionSetup extends PbxExtensionSetupBase
{
    private const LOG_TAG               = 'ModuleAutoprovision';
    private const APPLICATION_CALLERID  = 'Autoprovision application';

    /**
     * Creates the module settings table from model annotations, seeds defaults,
     * generates a random SIP secret, and registers the module's dialplan extension.
     */
    public function installDB(): bool
    {
        $result = $this->createSettingsTableByModelsAnnotations();
        if (!$result) {
            return false;
        }

        $this->db->begin();

        $settings = ModuleAutoprovision::findFirst() ?? new ModuleAutoprovision();

        if (!empty($settings->extension)) {
            $pattern = $settings->extension;
        } else {
            $extensionLength     = (int)PbxSettings::getValueByKey('PBXInternalExtensionLength');
            $extension           = Util::getExtensionX($extensionLength);
            $freeAppNumber       = Extensions::getNextFreeApplicationNumber();
            $pattern             = "*{$freeAppNumber}*{$extension}";
            $settings->extension = $pattern;
        }

        if (empty($settings->sip_secret)) {
            // Cryptographically random SIP secret. Stored only in the DB.
            $settings->sip_secret = bin2hex(random_bytes(16));
        }

        if (empty($settings->http_port)) {
            // Unprivileged port that doesn't clash with WEBPort/WEBHTTPSPort or SIP.
            $settings->http_port = '8480';
        }

        $result = $settings->save();

        if ($result) {
            $result = $this->installDefaultTemplates();
        }

        $extensionRow = Extensions::findFirst([
            'number = :number:',
            'bind' => ['number' => $pattern],
        ]);
        if ($extensionRow === null) {
            $extensionRow                    = new Extensions();
            $extensionRow->number            = $pattern;
            $extensionRow->type              = 'MODULES';
            $extensionRow->callerid          = self::APPLICATION_CALLERID;
            $extensionRow->public_access     = '0';
            $extensionRow->show_in_phonebook = '0';
            $result                          = $result && $extensionRow->save();
        }

        if ($result) {
            $this->db->commit();
        } else {
            $this->db->rollback();
            Util::sysLogMsg(self::LOG_TAG, 'Failed to seed the Extensions table.');
            return false;
        }

        return $this->registerNewModule();
    }

    /**
     * Seeds vendor-specific example templates on a fresh install only.
     *
     * Skips entirely when the Templates table already contains rows so upgrades
     * never overwrite user-authored data. Admins can re-seed on demand via the
     * "Load examples" button on the Templates tab, which calls TemplateSeeder
     * directly (with its own per-name idempotency).
     */
    private function installDefaultTemplates(): bool
    {
        try {
            if ((int)Templates::count() > 0) {
                return true;
            }
        } catch (Throwable $e) {
            Util::sysLogMsg(self::LOG_TAG, 'Failed to inspect Templates table: ' . $e->getMessage());
            return false;
        }

        $report = TemplateSeeder::seed();
        return empty($report['failed']);
    }

    /**
     * Copies module files into the system tree, grants execute permission to AGI scripts,
     * and ensures the firmware repository directory tree exists.
     */
    public function installFiles(): bool
    {
        // Use escapeshellarg to defend against unexpected characters in moduleDir.
        Processes::mwExec('chmod +x ' . escapeshellarg($this->moduleDir . '/agi-bin') . '/*');
        parent::installFiles();
        $this->ensureFirmwareDir();
        return true;
    }

    /**
     * Creates the firmware repository under <moduleDir>/firmware/<vendor>/.
     *
     * Module dir can move between MikoPBX versions (USB key reseat, factory reset),
     * so the upload action also re-creates the dir on every call as a cheap defence.
     */
    private function ensureFirmwareDir(): void
    {
        $base = $this->moduleDir . '/firmware';
        foreach (['', '/yealink', '/snom', '/fanvil', '/grandstream', '/htek'] as $suffix) {
            $path = $base . $suffix;
            if (!is_dir($path) && !@mkdir($path, 0755, true) && !is_dir($path)) {
                Util::sysLogMsg(self::LOG_TAG, "Failed to create firmware dir: {$path}");
            }
        }
    }

    /**
     * Removes the module's Extensions row and optionally drops the settings table.
     *
     * @param bool $keepSettings If true, preserves the module's data tables.
     */
    public function unInstallDB($keepSettings = false): bool
    {
        $result = true;

        $extensionRow = null;
        try {
            $settings = ModuleAutoprovision::findFirst();
            if ($settings !== null && !empty($settings->extension)) {
                $extensionRow = Extensions::findFirst([
                    'number = :number:',
                    'bind' => ['number' => $settings->extension],
                ]);
            }
        } catch (Throwable) {
            $extensionRow = Extensions::findFirst([
                'callerid = :callerid:',
                'bind' => ['callerid' => self::APPLICATION_CALLERID],
            ]);
        }

        if ($extensionRow !== null) {
            $result = $result && $extensionRow->delete();
        }

        if ($result) {
            $result = $result && parent::unInstallDB($keepSettings);
        }

        return $result;
    }
}
