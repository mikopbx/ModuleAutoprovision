<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;

return [
    'mod_Autoprovision_additional_params' => 'დამატებითი პარამეტრები',
    'mod_Autoprovision_mac_white' => 'ტელეფონის MAC მისამართების თეთრი სია',
    'mod_Autoprovision_mac_black' => 'ტელეფონების MAC მისამართების შავი სია',
    'mod_Autoprovision_pbx_host' => 'სერვერის მისამართი ტელეფონის რეგისტრაციისთვის',
    'mod_Autoprovision_Extension' => 'გაფართოების ნომრის შაბლონი',
    'SubHeaderModuleAutoprovision' => 'დახმარება SIP ტელეფონების დაყენებაში',
    'BreadcrumbModuleAutoprovision' => 'ტელეფონის ავტომატური დაყენების მოდული',
    'mo_ModuleAutoprovision' => 'ტელეფონის ავტომატური დაყენების მოდული',
    'repModuleAutoprovision' => 'მოდული -% რეპესენტი%',
    'mod_Autoprovision_header' => 'თუ მოდული ჩართულია, SIP ანგარიში "<b>apv-miko-pbx</b>" ხელმისაწვდომი გახდება PBX-ზე.
<br>თქვენი ტელეფონის ავტომატურად კონფიგურაციისთვის, თქვენ უნდა დააბრუნოთ ის ქარხნულ პარამეტრებზე.
<br>თუ ტელეფონი პირველად დაუკავშირდება PBX-ს, ის დარეგისტრირდება "<b>apv-miko-pbx</b>" ანგარიშზე.
<br>ტელეფონის კონფიგურაციისთვის, თქვენ უნდა დარეკოთ მისგან "<b>%extension%</b>", სადაც XXX არის PBX-ის შიდა ნომერი.
<br><br>
ავტოკონფიგურაცია შესაძლებელია მხოლოდ საწარმოს ლოკალური ქსელისთვის, ტელეფონებისთვის <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_phone_settings_title' => 'ტელეფონის პარამეტრები',
    'mod_Autoprovision_phone_templates' => 'პარამეტრების შაბლონები',
    'mod_Autoprovision_general_settings' => 'URI პარამეტრები',
    'mod_Autoprovision_pnp' => 'PnP პარამეტრები',
    'mod_Autoprovision_addNew' => 'დამატება',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'ნიმუში',
    'mod_Autoprovision_templates_users_header' => 'MAC მისამართის აღწერისას ნებადართულია სიმბოლო <b>%</b> - რაც ნიშნავს "ნებისმიერი სიმბოლოების კომპლექტს" <br>
შაბლონი <b>805e0c67%</b> დაემთხვევა <b>805e0c670001</b> და <b>805e0c670002</b>',
    'mod_Autoprovision_phone_settings_user' => 'თანამშრომელი',
    'mod_Autoprovision_phone_settings_mac' => 'Mac მისამართი',
    'mod_Autoprovision_template_name' => 'სახელი',
    'mod_Autoprovision_search_tags' => 'ძიება...',
    'mod_Autoprovision_edit_template' => 'შაბლონის რედაქტირება',
    'mod_Autoprovision_end_edit_template' => 'დაასრულეთ რედაქტირება',
    'mod_Autoprovision_other_pbx' => 'Სატელეფონო წიგნი',
    'mod_Autoprovision_other_pbx_name' => 'სატელეფონო სადგურის დასახელება',
    'mod_Autoprovision_other_pbx_address' => 'PBX ქსელის მისამართი',
    'mod_Autoprovision_templates_header' => 'შაბლონის აღწერისას შეგიძლიათ გამოიყენოთ შემდეგი პარამეტრები: <b>{SIP_USER_NAME}</b> - თანამშრომლის სახელი <b>{SIP_NUM}</b> - შიდა ნომერი (შესვლა) <b>{SIP_PASS}</b> - პაროლი',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_other_pbx_header' => '<b>Warning!</b> The phone book must be accessible on every PBX at the URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>
List every address of the PBXes from which the phone book should be fetched.<br>',
    'mod_Autoprovision_templates_uri_header' => '<b>Warning!</b> All URIs are resolved relative to the base value <b>/pbxcore/api/autoprovision-http</b><br>
When describing a URI you may use the symbol <b>%</b> meaning "any set of characters". <br>
The URI <b>/%/%/test.cfg</b> will match <b>/1/2/test.cfg</b> and <b>/test/test3/test.cfg</b>',
];
