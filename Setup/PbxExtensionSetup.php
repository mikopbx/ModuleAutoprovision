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
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\Templates;
use Modules\ModuleAutoprovision\Models\TemplatesUri;
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
     * Each template demonstrates the placeholder system ({SIP_NUM}, {SIP_USER_NAME},
     * {SIP_PASS}) substituted per device by GetController::getConfigStatic and is
     * mapped to an illustrative URI under "examples/" — admins copy and adapt them
     * to their fleet rather than serving them directly to phones.
     *
     * Skips entirely when the Templates table already contains rows, so upgrades
     * never overwrite user-authored data.
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

        $seeds = [
            ['name' => 'Yealink (example)',     'file' => 'yealink-example.cfg',     'uri' => '/examples/yealink-common.cfg'],
            ['name' => 'Fanvil (example)',      'file' => 'fanvil-example.txt',      'uri' => '/examples/fanvil-common.txt'],
            ['name' => 'Snom (example)',        'file' => 'snom-example.xml',        'uri' => '/examples/snom-common.xml'],
            ['name' => 'Grandstream (example)', 'file' => 'grandstream-example.xml', 'uri' => '/examples/grandstream-common.xml'],
            ['name' => 'Htek (example)',        'file' => 'htek-example.cfg',        'uri' => '/examples/htek-common.cfg'],
        ];

        $templatesDir = __DIR__ . '/templates';
        foreach ($seeds as $seed) {
            $path = $templatesDir . '/' . $seed['file'];
            if (!is_readable($path)) {
                Util::sysLogMsg(self::LOG_TAG, "Seed template not readable: {$path}");
                continue;
            }
            $body = file_get_contents($path);
            if ($body === false) {
                continue;
            }

            $template           = new Templates();
            $template->name     = $seed['name'];
            $template->template = $body;
            if (!$template->save()) {
                Util::sysLogMsg(self::LOG_TAG, "Failed to save seed template '{$seed['name']}'.");
                return false;
            }

            $uri             = new TemplatesUri();
            $uri->uri        = $seed['uri'];
            $uri->templateId = (string)$template->id;
            if (!$uri->save()) {
                Util::sysLogMsg(self::LOG_TAG, "Failed to save URI mapping for '{$seed['name']}'.");
                return false;
            }
        }

        return true;
    }

    /**
     * Copies module files into the system tree and grants execute permission to AGI scripts.
     */
    public function installFiles(): bool
    {
        // Use escapeshellarg to defend against unexpected characters in moduleDir.
        Processes::mwExec('chmod +x ' . escapeshellarg($this->moduleDir . '/agi-bin') . '/*');
        parent::installFiles();
        return true;
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
