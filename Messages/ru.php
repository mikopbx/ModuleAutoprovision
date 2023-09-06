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

return [
    'repModuleAutoprovision'              => 'Модуль - %repesent%',
    'mo_ModuleAutoprovision'              => 'Модуль автоматической настройки телефонов',
    'BreadcrumbModuleAutoprovision'       => 'Модуль автоматической настройки телефонов',
    'SubHeaderModuleAutoprovision'        => 'Помощь в настройке SIP телефонов',
    'mod_Autoprovision_Extension'         => 'Шаблон внутреннего номера',
    'mod_Autoprovision_pbx_host'          => 'Адрес сервера для регистрации телефонов',
    'mod_Autoprovision_mac_black'         => 'Черный список MAC адресов телефонов',
    'mod_Autoprovision_mac_white'         => 'Белый список MAC адресов телефонов',
    'mod_Autoprovision_additional_params' => 'Дополнительные параметры',
    'mod_Autoprovision_header'            => 'Если модуль включен, то на АТС становится доступна учетная запись SIP "<b>apv-miko-pbx</b>".
<br>Для автоматической настройки телефона необходимо сбросить его к заводским настройками.
<br>Если телефон подключается к АТС впервые, то он будет зарегистрирован на учетной записи "<b>apv-miko-pbx</b>".
<br>Для настройки телефона необходимо с него позвонить на номер "<b>%extension%</b>", где XXX - это внутренний номер на АТС.
<br><br>
Автонастройка возможна только для локальной сети предприятия, для телефонов <b>Yealink, Snom, Fanvil</b>.'
];