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
    'fw_moduleautoprovisionDescription' => 'Autoprovision - אספקת תצורת טלפונים דרך HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'יציאת TCP ייעודית המוגשת על ידי ModuleAutoprovision על גבי HTTP פשוט (ללא הפניית HTTPS).<br>טלפוני IP מורידים מהיציאה הזו את קובצי התצורה שלהם בזמן ההקצאה.<br>פתחו גישה רק לרשת המקומית שבה נמצאים הטלפונים.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — שרת TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 מנוהל על ידי שרת TFTP מובנה במודול, הכתוב ב-PHP טהור.<br>בשימוש על ידי טלפונים וקושחות המעדיפים DHCP option 66 (TFTP) על פני PnP מולטיקאסט — היסטורית Snom, חלק מהקושחות של Fanvil, וכן רשתות מנותבות שבהן מולטיקאסט אינו חוצה גבול L3.<br><b>פרוטוקול טקסט פתוח:</b> פתחו את הפורט רק במקטע ה-LAN שבו ממוקמים הטלפונים.',
    'mo_ModuleAutoprovision' => 'The autoprovision module',
    'BreadcrumbModuleAutoprovision' => 'The autoprovision module',
    'SubHeaderModuleAutoprovision' => 'Bulk ip-phones setup',
    'mod_Autoprovision_Extension' => 'Provision pattern command',
    'mod_Autoprovision_pbx_host' => 'The PBX DNS name',
    'mod_Autoprovision_http_port' => 'פורט HTTP לאוטו-פרוויז\'ן',
    'mod_Autoprovision_http_port_hint' => 'פורט TCP ייעודי של המודול לשליחת קונפיגורציות ב-HTTP טהור — אינו כפוף להפניית HTTPS הגלובלית. הטלפונים חייבים לפנות ל-PBX בפורט זה; חוק הפיירוול נוסף אוטומטית.',
    'mod_Autoprovision_mac_black' => 'Black MAC address list',
    'mod_Autoprovision_mac_white' => 'White MAC address list',
    'mod_Autoprovision_additional_params' => 'Additional settings',
    'mod_Autoprovision_tftp_enabled' => 'אפשר פרוויז\'ן ב-TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'מפעיל שרת TFTP ב-PHP טהור על UDP/69 שמספק את אותן קונפיגורציות לכל MAC כמו ערוץ ה-HTTP, וגם קושחות מהלשונית «קושחות».<br>שימושי כאשר PnP מולטיקאסט חסום (טיפוסי ב-WiFi משרדי ורשתות מנותבות) או כאשר הטלפון מעדיף DHCP option 66 (Snom, חלק מקושחות Fanvil).<br>אין צורך בקבצי הפעלה חיצוניים — רץ בתוך ה-worker של המודול. <b>פרוטוקול טקסט פתוח</b>: הפעילו רק ב-LAN מהימן. חוק הפיירוול ל-UDP/69 נפתח אוטומטית.',
    'mod_Autoprovision_phone_settings_title' => 'Phone settings',
    'mod_Autoprovision_phone_templates' => 'Settings templates',
    'mod_Autoprovision_general_settings' => 'URI Settings',
    'mod_Autoprovision_pnp' => 'PnP Settings',
    'mod_Autoprovision_addNew' => 'Add',
    'mod_Autoprovision_load_examples' => 'טען תבניות לדוגמה',
    'mod_Autoprovision_load_examples_hint' => 'מוסיף תבניות דוגמה מובנות עבור Yealink, Fanvil, Snom, Grandstream ו-Htek. תבניות בשמות הקיימים כבר מדולגות, כך שניתן ללחוץ על הכפתור שוב ושוב ללא סיכון לכפילויות.',
    'mod_Autoprovision_load_examples_installed' => 'תבניות הדוגמה הותקנו',
    'mod_Autoprovision_load_examples_already_present' => 'כל תבניות הדוגמה כבר מותקנות.',
    'mod_Autoprovision_load_examples_failed' => 'ההתקנה של תבניות הדוגמה נכשלה',
    'mod_Autoprovision_load_examples_partial' => 'חלק מתבניות הדוגמה לא הותקנו',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'בעמוד יש שינויים שלא נשמרו. טעינת הדוגמאות תטען מחדש את העמוד ותבטל אותם. להמשיך?',
    'mod_Autoprovision_load_examples_post_only' => 'נדרשת בקשת POST.',
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
    'mod_Autoprovision_other_pbx_header' => '<b>שימו לב!</b> ספר הטלפונים חייב להיות נגיש בכל PBX ב-URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>פרטו את כל כתובות ה-PBX שמהן יש לאחזר את ספר הטלפונים.<br>',
    'mod_Autoprovision_templates_users_header' => 'When describing a MAC address, it is allowed to use the symbol <b>%</b> - meaning "any set of characters" <br>
The template <b>805e0c67%</b> will match <b>805e0c670001</b> and <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>שימו לב!</b> כל ה-URI נבנים יחסית לערך הבסיס <b>/pbxcore/api/autoprovision-http</b><br>בעת תיאור URI ניתן להשתמש בסמל <b>%</b> — שמשמעו „כל סדרת תווים“.<br>ה-URI <b>/%/%/test.cfg</b> יתאים ל-<b>/1/2/test.cfg</b> ול-<b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'If the module is enabled, the SIP account "<b>apv-miko-pbx</b>" becomes available on the PBX.
<br>To automatically configure your phone, you need to reset it to factory settings.
<br>If the phone connects to the PBX for the first time, it will be registered to the "<b>apv-miko-pbx</b>" account.
<br>To configure the phone, you need to call "<b>%extension%</b>" from it, where XXX is the internal number on the PBX.
<br><br>
Autoconfiguration is possible only for the local network of the enterprise, for phones <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'קושחות',
    'mod_Autoprovision_firmware_header' => 'העלו קבצי קושחה שה-PBX יספק לטלפונים ב-HTTP מפורט הפרוויז\'ן. השתמשו במחזיק מקום <b>{FIRMWARE_URL}</b> בתבניות שלכם להזרקת כתובת ההורדה.',
    'mod_Autoprovision_firmware_drop_hint' => 'גררו לכאן קובץ קושחה או לחצו לבחירה',
    'mod_Autoprovision_firmware_browse' => 'בחירת קובץ',
    'mod_Autoprovision_firmware_vendor' => 'יצרן',
    'mod_Autoprovision_firmware_model' => 'דגם',
    'mod_Autoprovision_firmware_version' => 'גרסה',
    'mod_Autoprovision_firmware_notes' => 'הערות',
    'mod_Autoprovision_firmware_filename' => 'קובץ',
    'mod_Autoprovision_firmware_size' => 'גודל',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'עריכת מטא-נתוני קושחה',
    'mod_Autoprovision_firmware_save' => 'שמור',
    'mod_Autoprovision_firmware_cancel' => 'ביטול',
];
