<?php
/*
 * MikoPBX - free phone system for small business
 * Copyright © 2017-2023 Alexey Portnov and Nikolay Beketov
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

/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */

namespace Modules\ModuleAutoprovision\Setup;

use MikoPBX\Common\Models\Extensions;
use MikoPBX\Core\System\Processes;
use MikoPBX\Core\System\Util;
use MikoPBX\Common\Models\PbxSettings;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use MikoPBX\Modules\Setup\PbxExtensionSetupBase;
use Throwable;

class PbxExtensionSetup extends PbxExtensionSetupBase
{
    /**
     * Создает структуру для хранения настроек модуля в своей модели
     * и заполняет настройки по-умолчанию если таблицы не было в системе
     * см (unInstallDB)
     *
     * Регистрирует модуль в PbxExtensionModules
     *
     * @return bool результат установки
     */
    public function installDB(): bool
    {
        $result = $this->createSettingsTableByModelsAnnotations();

        if ($result) {
            // Выполним начальное заполнение настроек.
            $this->db->begin();
            $result   = true;
            $settings = ModuleAutoprovision::findFirst();
            if ( ! $settings) {
                $settings = new ModuleAutoprovision();
            }

            if ( ! empty($settings->extension)) {
                $pattern = $settings->extension;
            } else {
                $extensionLength     = PbxSettings::getValueByKey('PBXInternalExtensionLength');
                $extension           = Util::getExtensionX($extensionLength);
                $freeAppNumber       = Extensions::getNextFreeApplicationNumber();
                $pattern             = "*{$freeAppNumber}*{$extension}";
                $settings->extension = $pattern;
            }
            $result = $result && $settings->save();

            $data = Extensions::findFirst('number="' . $pattern . '"');
            if ( ! $data) {
                $data                    = new Extensions();
                $data->number            = $pattern;
                $data->type              = 'MODULES';
                $data->callerid          = 'Autoprovision application';
                $data->public_access     = 0;
                $data->show_in_phonebook = 0;
                $result                  = $result && $data->save();
            }

            if ($result) {
                $this->db->commit();
            } else {
                $this->db->rollback();
                Util::sysLogMsg('update_system_config', 'Error: Failed to update table the Extensions table.');
            }
        }
        // Регаем модуль в PBX Extensions
        if ($result) {
            $result = $this->registerNewModule();
        }

        return $result;
    }

    /**
     * Выполняет копирование необходимых файлов, в папки системы
     *
     * @return bool результат установки
     */
    public function installFiles(): bool
    {
        Processes::mwExec("chmod +x {$this->moduleDir}/agi-bin/*");
        parent::installFiles();
        return true;
    }

    /**
     * Удаляет запись о модуле из PbxExtensionModules
     * Удаляет свою модель
     *
     * @param  $keepSettings - оставляет таблицу с данными своей модели
     *
     * @return bool результат очистки
     */
    public function unInstallDB($keepSettings = false): bool
    {
        $result = true;

        // Удалим запись Extension для модуля
        $data = false;
        try {
            $settings = ModuleAutoprovision::findFirst();
            if ($settings) {
                $data = Extensions::findFirst('number="' . $settings->extension . '"');
            }
        } catch (Throwable $exception) {
            $data = Extensions::findFirst('callerid="Autoprovision application"');
        }
        if ($data) {
            $result = $result && $data->delete();
        }


        // Удалим допоплнительные таблицы
        if ($result) {
            $result = $result && parent::unInstallDB($keepSettings);
        }

        return $result;
    }

}