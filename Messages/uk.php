<?php
return [
    'mod_Autoprovision_additional_params' => 'Додаткові параметри',
    'mod_Autoprovision_mac_white' => 'Білий список MAC адрес телефонів',
    'mod_Autoprovision_mac_black' => 'Блакитний список MAC адрес телефонів',
    'mod_Autoprovision_pbx_host' => 'Адреса сервера для реєстрації телефонів',
    'mod_Autoprovision_Extension' => 'Внутрішній номер шаблон',
    'SubHeaderModuleAutoprovision' => 'Допомога в налаштуванні SIP телефонів',
    'BreadcrumbModuleAutoprovision' => 'Модуль автоматичного налаштування телефонів',
    /**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
    'repModuleAutoprovision' => 'Модуль - %repesent%',
    'mo_ModuleAutoprovision' => 'Модуль автоматичного налаштування телефонів',
    'mod_Autoprovision_header' => 'Якщо модуль увімкнено, то на АТС стає доступний обліковий запис SIP "apv-miko-pbx</b>".
<br>Для автоматичного налаштування телефону необхідно скинути його до заводських налаштувань.
<br>Якщо телефон підключається до АТС вперше, то він буде зареєстрований на обліковому записі "apv-miko-pbx".
<br>Для налаштування телефону необхідно з нього зателефонувати на номер "<b>%extension%</b>", де XXX - це внутрішній номер на АТС.
<br><br>
Автоналаштування можливе лише для локальної мережі підприємства, для телефонів <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_phone_settings_title' => 'Установки телефонів',
    'mod_Autoprovision_phone_templates' => 'Шаблони налаштувань',
    'mod_Autoprovision_general_settings' => 'Налаштування URI',
    'mod_Autoprovision_pnp' => 'PnP Налаштування',
    'mod_Autoprovision_addNew' => 'Додати',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Шаблон',
    'mod_Autoprovision_phone_settings_user' => 'Співробітник',
    'mod_Autoprovision_phone_settings_mac' => 'MAC Адреса',
    'mod_Autoprovision_template_name' => 'Найменування',
    'mod_Autoprovision_search_tags' => 'Пошук...',
    'mod_Autoprovision_edit_template' => 'Редагування шаблону',
    'mod_Autoprovision_end_edit_template' => 'Завершити редагування',
    'mod_Autoprovision_other_pbx' => 'Телефонна книга',
    'mod_Autoprovision_other_pbx_name' => 'Найменування АТС',
    'mod_Autoprovision_other_pbx_address' => 'Мережева адреса АТС',
    'mod_Autoprovision_templates_header' => 'При описі шаблону можна використовувати параметри: <b>{SIP_USER_NAME}</b> - ім\'я співробітника <b>{SIP_NUM}</b> - внутрішній номер (логін) <b>{SIP_PASS}</b> - пароль',
    'mod_Autoprovision_templates_users_header' => 'При описі MAC адреси допускається використовувати символ <b>%</b> - що означає "будь-який набір символів" <br>
Шаблон <b>805e0c67%</b> буде відповідати <b>805e0c670001</b> та <b>805e0c670002</b>',
];
