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

return [
    'repModuleAutoprovision' => 'Модуль - %represent%',
    'fw_moduleautoprovisionDescription' => 'Автопровіжн — роздача конфігурацій IP-телефонам через HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Виділений TCP-порт, який модуль ModuleAutoprovision обслуговує по чистому HTTP (без редиректу на HTTPS).<br>IP-телефони забирають свої конфігураційні файли з цього порту під час автоналаштування.<br>Відкривайте доступ лише з локальної мережі, до якої підключені телефони.',
    'fw_AutoprovisionTftpPortDescription' => 'Автопровіжн — TFTP-сервер (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 обслуговується вбудованим у модуль TFTP-сервером на чистому PHP.<br>Використовують телефони та прошивки, які надають перевагу DHCP option 66 (TFTP) перед мультикастовим PnP — історично Snom, деякі прошивки Fanvil, а також маршрутизовані мережі, де мультикаст не перетинає межу L3.<br><b>Протокол відкритим текстом:</b> відкривайте порт лише в сегменті LAN, де знаходяться телефони.',
    'mo_ModuleAutoprovision' => 'Модуль автоматичного налаштування телефонів',
    'BreadcrumbModuleAutoprovision' => 'Модуль автоматичного налаштування телефонів',
    'SubHeaderModuleAutoprovision' => 'Допомога в налаштуванні SIP телефонів',
    'mod_Autoprovision_Extension' => 'Внутрішній номер шаблон',
    'mod_Autoprovision_pbx_host' => 'Адреса сервера для реєстрації телефонів',
    'mod_Autoprovision_http_port' => 'TCP-порт автопровіжн (HTTP)',
    'mod_Autoprovision_http_port_hint' => 'Окремий TCP-порт модуля для віддачі конфігурацій по чистому HTTP — не підпадає під глобальне перенаправлення на HTTPS. Телефони повинні звертатися до PBX на цьому порту; правило файрвола додається автоматично.',
    'mod_Autoprovision_mac_black' => 'Блакитний список MAC адрес телефонів',
    'mod_Autoprovision_mac_white' => 'Білий список MAC адрес телефонів',
    'mod_Autoprovision_additional_params' => 'Додаткові параметри',
    'mod_Autoprovision_tftp_enabled' => 'Увімкнути TFTP-провіжн (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Запускає вбудований TFTP-сервер на чистому PHP (UDP/69), який роздає ті ж самі per-MAC конфігурації, що й HTTP-канал, а також прошивки з вкладки «Прошивки».<br>Корисно, якщо мультикастовий PnP заблоковано (типово для офісних WiFi і маршрутизованих мереж) або телефон надає перевагу DHCP option 66 (Snom, деякі прошивки Fanvil).<br>Без зовнішніх бінарників — працює всередині воркера модуля. <b>Протокол відкритим текстом</b>: вмикайте лише у довіреній LAN. Правило firewall для UDP/69 відкривається автоматично.',
    'mod_Autoprovision_phone_settings_title' => 'Установки телефонів',
    'mod_Autoprovision_phone_templates' => 'Шаблони налаштувань',
    'mod_Autoprovision_general_settings' => 'Налаштування URI',
    'mod_Autoprovision_pnp' => 'PnP Налаштування',
    'mod_Autoprovision_addNew' => 'Додати',
    'mod_Autoprovision_load_examples' => 'Завантажити приклади шаблонів',
    'mod_Autoprovision_load_examples_hint' => 'Додає вбудовані приклади шаблонів для Yealink, Fanvil, Snom, Grandstream і Htek. Шаблони з уже існуючими назвами пропускаються, тому кнопку можна натискати повторно без ризику дублікатів.',
    'mod_Autoprovision_load_examples_installed' => 'Приклади шаблонів встановлено',
    'mod_Autoprovision_load_examples_already_present' => 'Усі приклади шаблонів вже встановлено.',
    'mod_Autoprovision_load_examples_failed' => 'Не вдалося встановити приклади шаблонів',
    'mod_Autoprovision_load_examples_partial' => 'Частина прикладів шаблонів не встановилася',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'На сторінці є незбережені зміни. Завантаження прикладів перезавантажить сторінку та скасує їх. Продовжити?',
    'mod_Autoprovision_load_examples_post_only' => 'Потрібен POST-запит.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Шаблон',
    'mod_Autoprovision_phone_settings_user' => 'Співробітник',
    'mod_Autoprovision_phone_settings_mac' => 'MAC Адреса',
    'mod_Autoprovision_template_name' => 'Найменування',
    'mod_Autoprovision_search_tags' => 'Пошук...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Редагування шаблону',
    'mod_Autoprovision_end_edit_template' => 'Завершити редагування',
    'mod_Autoprovision_other_pbx' => 'Телефонна книга',
    'mod_Autoprovision_other_pbx_name' => 'Найменування АТС',
    'mod_Autoprovision_other_pbx_address' => 'Мережева адреса АТС',
    'mod_Autoprovision_templates_header' => 'При описі шаблону можна використовувати параметри: <b>{SIP_USER_NAME}</b> - ім\'я співробітника <b>{SIP_NUM}</b> - внутрішній номер (логін) <b>{SIP_PASS}</b> - пароль',
    'mod_Autoprovision_other_pbx_header' => '<b>Увага!</b> Телефонна книга має бути доступна для кожної PBX за URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Перелічіть усі адреси PBX, з яких потрібно отримати телефонну книгу.<br>',
    'mod_Autoprovision_templates_users_header' => 'При описі MAC адреси допускається використовувати символ <b>%</b> - що означає "будь-який набір символів" <br>
Шаблон <b>805e0c67%</b> буде відповідати <b>805e0c670001</b> та <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Увага!</b> Усі URI будуються відносно базового значення <b>/pbxcore/api/autoprovision-http</b><br>В описі URI допускається використання символу <b>%</b> — означає „будь-який набір символів“.<br>URI <b>/%/%/test.cfg</b> відповідатиме <b>/1/2/test.cfg</b> та <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Якщо модуль увімкнено, то на АТС стає доступний обліковий запис SIP "apv-miko-pbx</b>".
<br>Для автоматичного налаштування телефону необхідно скинути його до заводських налаштувань.
<br>Якщо телефон підключається до АТС вперше, то він буде зареєстрований на обліковому записі "apv-miko-pbx".
<br>Для налаштування телефону необхідно з нього зателефонувати на номер "<b>%extension%</b>", де XXX - це внутрішній номер на АТС.
<br><br>
Автоналаштування можливе лише для локальної мережі підприємства, для телефонів <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Прошивки',
    'mod_Autoprovision_firmware_header' => 'Завантажте файли прошивок, які PBX роздаватиме телефонам по HTTP з провіжн-порту. У шаблонах використовуйте плейсхолдер <b>{FIRMWARE_URL}</b>, щоб вставити URL для завантаження.',
    'mod_Autoprovision_firmware_drop_hint' => 'Перетягніть файл прошивки сюди або натисніть, щоб вибрати',
    'mod_Autoprovision_firmware_browse' => 'Вибрати файл',
    'mod_Autoprovision_firmware_vendor' => 'Виробник',
    'mod_Autoprovision_firmware_model' => 'Модель',
    'mod_Autoprovision_firmware_version' => 'Версія',
    'mod_Autoprovision_firmware_notes' => 'Нотатки',
    'mod_Autoprovision_firmware_filename' => 'Файл',
    'mod_Autoprovision_firmware_size' => 'Розмір',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Редагування метаданих прошивки',
    'mod_Autoprovision_firmware_save' => 'Зберегти',
    'mod_Autoprovision_firmware_cancel' => 'Скасувати',
];
