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

use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;

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
    'mod_Autoprovision_phone_settings_title' => 'Настройки телефонов',
    'mod_Autoprovision_phone_templates'      => 'Шаблоны настроек',
    'mod_Autoprovision_general_settings'     => 'Настройки URI',
    'mod_Autoprovision_pnp'                 => 'PnP Настройка',
    'mod_Autoprovision_addNew'              => 'Добавить',
    'mod_Autoprovision_templates_uri_uri'   => 'URI',
    'mod_Autoprovision_templates_uri_template'   => 'Шаблон',
    'mod_Autoprovision_phone_settings_user' => 'Сотрудник',
    'mod_Autoprovision_phone_settings_mac'  => 'MAC Адрес',
    'mod_Autoprovision_template_name'  => 'Наименование',
    'mod_Autoprovision_search_tags'  => 'Поиск...',
    'mod_Autoprovision_filter_posts'  => '',
    'mod_Autoprovision_edit_template'  => 'Редактирование шаблона',
    'mod_Autoprovision_end_edit_template'  => 'Завершить редактирование',
    'mod_Autoprovision_other_pbx'  => 'Телефонная книга',
    'mod_Autoprovision_other_pbx_name'  => 'Наименование АТС',
    'mod_Autoprovision_other_pbx_address'  => 'Сетевой адрес АТС',
    'mod_Autoprovision_templates_header'  => 'При описании шаблона допускается использовать параметры: <b>{SIP_USER_NAME}</b> - имя сотрудника <b>{SIP_NUM}</b>  - внутренний номер (логин) <b>{SIP_PASS}</b>  - пароль',
    'mod_Autoprovision_other_pbx_header'  => '<b>Внимание!</b> Телефонная книга должна быть доступна для каждой АТС по URI <b>'.AutoprovisionConf::BASE_URI.'/phonebook</b><br> 
Перечислите все адреса, АТС, с которых необходимо получить телефонную книгу. <br> ',

    'mod_Autoprovision_templates_users_header'  => 'При описании MAC адреса допускается использовать символ <b>%</b> - означающий "любой набор символов" <br>
Шаблон <b>805e0c67%</b> будет соответстовать <b>805e0c670001</b> и <b>805e0c670002</b>',

    'mod_Autoprovision_templates_uri_header'=> '<b>Внимание!</b> Все URI строятся относительно базового значения <b>'.AutoprovisionConf::BASE_URI.'</b><br> 
При описании URI допускается использовать символ <b>%</b> - означающий "любой набор символов" <br>
URI <b>/%/%/test.cfg</b> будет соответстовать <b>/1/2/test.cfg</b>  и <b>/test/test3/test.cfg</b>',

    'mod_Autoprovision_header'            => 'Если модуль включен, то на АТС становится доступна учетная запись SIP "<b>apv-miko-pbx</b>"
<br>Для автоматической настройки телефона необходимо сбросить его к заводским настройками.
<br>Если телефон подключается к АТС впервые, то он будет зарегистрирован на учетной записи "<b>apv-miko-pbx</b>".
<br>Для настройки телефона необходимо с него позвонить на номер "<b>%extension%</b>", где XXX - это внутренний номер на АТС.
<br><br>
Автонастройка возможна только для локальной сети предприятия, для телефонов <b>Yealink, Snom, Fanvil</b>.'
];