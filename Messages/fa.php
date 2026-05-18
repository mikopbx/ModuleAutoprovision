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
    'repModuleAutoprovision' => 'Autoprovision module - %represent%',
    'fw_moduleautoprovisionDescription' => 'تأمین خودکار — تحویل پیکربندی تلفن از طریق HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'پورت TCP اختصاصی که توسط ModuleAutoprovision روی HTTP ساده ارائه می‌شود (بدون تغییر مسیر به HTTPS).<br>تلفن‌های IP فایل‌های پیکربندی خود را هنگام تأمین خودکار از این پورت دریافت می‌کنند.<br>دسترسی را فقط برای شبکه محلی که تلفن‌ها در آن قرار دارند باز کنید.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — سرور TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 توسط سرور TFTP داخلی نوشته‌شده با PHP خالص ارائه می‌شود.<br>توسط تلفن‌ها و فریم‌ورهایی استفاده می‌شود که DHCP option 66 (TFTP) را به PnP مالتی‌کست ترجیح می‌دهند — تاریخی Snom، برخی فریم‌ورهای Fanvil و شبکه‌های مسیریابی‌شده که مالتی‌کست از مرز L3 عبور نمی‌کند.<br><b>پروتکل متن ساده:</b> پورت را تنها در بخش LAN که تلفن‌ها در آن قرار دارند باز کنید.',
    'mo_ModuleAutoprovision' => 'The autoprovision module',
    'BreadcrumbModuleAutoprovision' => 'The autoprovision module',
    'SubHeaderModuleAutoprovision' => 'Bulk ip-phones setup',
    'mod_Autoprovision_Extension' => 'Provision pattern command',
    'mod_Autoprovision_pbx_host' => 'The PBX DNS name',
    'mod_Autoprovision_http_port' => 'پورت HTTP اتو-پروویژن',
    'mod_Autoprovision_http_port_hint' => 'پورت TCP اختصاصی ماژول برای ارائه پیکربندی از طریق HTTP خالص — تحت تأثیر هدایت سراسری به HTTPS نیست. تلفن‌ها باید روی این پورت با PBX ارتباط برقرار کنند؛ قانون فایروال به‌صورت خودکار افزوده می‌شود.',
    'mod_Autoprovision_mac_black' => 'Black MAC address list',
    'mod_Autoprovision_mac_white' => 'White MAC address list',
    'mod_Autoprovision_additional_params' => 'Additional settings',
    'mod_Autoprovision_tftp_enabled' => 'فعال‌سازی پروویژن از طریق TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'سرور TFTP داخلی نوشته‌شده با PHP خالص را روی UDP/69 راه‌اندازی می‌کند که همان پیکربندی‌های هر-MAC مانند کانال HTTP و همچنین فایل‌های فریم‌ور از زبانه «فریم‌ور» را ارائه می‌دهد.<br>زمانی مفید است که PnP مالتی‌کست مسدود باشد (در WiFi دفاتر و شبکه‌های مسیریابی‌شده معمول است) یا تلفن DHCP option 66 را ترجیح می‌دهد (Snom، برخی فریم‌ورهای Fanvil).<br>هیچ باینری خارجی نیاز نیست — درون worker ماژول اجرا می‌شود. <b>پروتکل متن ساده</b>: فقط در LAN مورد اعتماد فعال کنید. قانون فایروال برای UDP/69 خودکار باز می‌شود.',
    'mod_Autoprovision_phone_settings_title' => 'Phone settings',
    'mod_Autoprovision_phone_templates' => 'Settings templates',
    'mod_Autoprovision_general_settings' => 'URI Settings',
    'mod_Autoprovision_pnp' => 'PnP Settings',
    'mod_Autoprovision_addNew' => 'Add',
    'mod_Autoprovision_load_examples' => 'بارگذاری قالب‌های نمونه',
    'mod_Autoprovision_load_examples_hint' => 'قالب‌های نمونه داخلی برای Yealink، Fanvil، Snom، Grandstream و Htek را اضافه می‌کند. قالب‌هایی با نام‌های موجود رد می‌شوند، بنابراین می‌توان دکمه را بدون خطر ایجاد تکراری چندبار کلیک کرد.',
    'mod_Autoprovision_load_examples_installed' => 'قالب‌های نمونه نصب شدند',
    'mod_Autoprovision_load_examples_already_present' => 'همه قالب‌های نمونه قبلاً نصب شده‌اند.',
    'mod_Autoprovision_load_examples_failed' => 'نصب قالب‌های نمونه ناموفق بود',
    'mod_Autoprovision_load_examples_partial' => 'بخشی از قالب‌های نمونه نصب نشدند',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'تغییرات ذخیره‌نشده در صفحه وجود دارد. بارگذاری نمونه‌ها صفحه را بارگذاری مجدد می‌کند و آن‌ها را از بین می‌برد. ادامه می‌دهید؟',
    'mod_Autoprovision_load_examples_post_only' => 'درخواست POST لازم است.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Sample',
    'mod_Autoprovision_phone_settings_user' => 'Employee',
    'mod_Autoprovision_phone_settings_mac' => 'MAC Address',
    'mod_Autoprovision_template_name' => 'Name',
    'mod_Autoprovision_search_tags' => 'Search...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Editing a template',
    'mod_Autoprovision_end_edit_template' => 'Finish editing',
    'mod_Autoprovision_other_pbx' => 'Phone book',
    'mod_Autoprovision_other_pbx_name' => 'Name of the telephone exchange',
    'mod_Autoprovision_other_pbx_address' => 'PBX network address',
    'mod_Autoprovision_templates_header' => 'When describing a template, you can use the following parameters: <b>{SIP_USER_NAME}</b> - employee name <b>{SIP_NUM}</b> - internal number (login) <b>{SIP_PASS}</b> - password',
    'mod_Autoprovision_other_pbx_header' => '<b>توجه!</b> دفترچه تلفن باید برای هر PBX در URI <b>/pbxcore/api/autoprovision-http/phonebook</b> در دسترس باشد<br>تمام آدرس‌های PBX‌هایی را که باید دفترچه تلفن از آن‌ها دریافت شود فهرست کنید.<br>',
    'mod_Autoprovision_templates_users_header' => 'When describing a MAC address, it is allowed to use the symbol <b>%</b> - meaning "any set of characters" <br>
The template <b>805e0c67%</b> will match <b>805e0c670001</b> and <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>توجه!</b> همه URI‌ها نسبت به مقدار پایه <b>/pbxcore/api/autoprovision-http</b> ساخته می‌شوند<br>هنگام توصیف URI می‌توان از نماد <b>%</b> استفاده کرد — به معنای «هر مجموعه‌ای از کاراکترها».<br>URI <b>/%/%/test.cfg</b> با <b>/1/2/test.cfg</b> و <b>/test/test3/test.cfg</b> مطابقت دارد',
    'mod_Autoprovision_header' => 'If the module is enabled, the SIP account "<b>apv-miko-pbx</b>" becomes available on the PBX.
<br>To automatically configure your phone, you need to reset it to factory settings.
<br>If the phone connects to the PBX for the first time, it will be registered to the "<b>apv-miko-pbx</b>" account.
<br>To configure the phone, you need to call "<b>%extension%</b>" from it, where XXX is the internal number on the PBX.
<br><br>
Autoconfiguration is possible only for the local network of the enterprise, for phones <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'فریم‌ورها',
    'mod_Autoprovision_firmware_header' => 'فایل‌های فریم‌وری را بارگذاری کنید که PBX از طریق پورت پروویژن HTTP به تلفن‌ها ارائه می‌دهد. در قالب‌ها از placeholder <b>{FIRMWARE_URL}</b> برای درج URL دانلود استفاده کنید.',
    'mod_Autoprovision_firmware_drop_hint' => 'یک فایل فریم‌ور را اینجا بکشید یا برای انتخاب کلیک کنید',
    'mod_Autoprovision_firmware_browse' => 'انتخاب فایل',
    'mod_Autoprovision_firmware_vendor' => 'سازنده',
    'mod_Autoprovision_firmware_model' => 'مدل',
    'mod_Autoprovision_firmware_version' => 'نسخه',
    'mod_Autoprovision_firmware_notes' => 'یادداشت‌ها',
    'mod_Autoprovision_firmware_filename' => 'فایل',
    'mod_Autoprovision_firmware_size' => 'اندازه',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'ویرایش متاداده فریم‌ور',
    'mod_Autoprovision_firmware_save' => 'ذخیره',
    'mod_Autoprovision_firmware_cancel' => 'لغو',
];
