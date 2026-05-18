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
    'repModuleAutoprovision' => 'მოდული -% რეპესენტი%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision — ტელეფონების კონფიგურაციის მიწოდება HTTP-ით',
    'fw_moduleautoprovisionDescriptionHint' => 'ModuleAutoprovision-ის მიერ ჩვეულებრივი HTTP-ით (HTTPS-ზე გადამისამართების გარეშე) მომსახურე გამოყოფილი TCP პორტი.<br>IP ტელეფონები ავტოკონფიგურაციის დროს კონფიგურაციის ფაილებს ამ პორტიდან ჩამოტვირთავენ.<br>წვდომა გახსენით მხოლოდ იმ ლოკალური ქსელისთვის, რომელშიც ტელეფონები იმყოფებიან.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP სერვერი (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69-ს ემსახურება მოდულში ჩაშენებული, სუფთა PHP-ში დაწერილი TFTP სერვერი.<br>გამოიყენებენ ტელეფონები და firmware-ები, რომლებიც DHCP option 66 (TFTP) უპირატესობას ანიჭებენ multicast PnP-ის ნაცვლად — ისტორიულად Snom, ზოგიერთი Fanvil firmware და მარშრუტიზებული ქსელები, სადაც multicast არ კვეთს L3 საზღვარს.<br><b>ღია ტექსტური პროტოკოლი:</b> გახსენით პორტი მხოლოდ იმ LAN სეგმენტში, სადაც ტელეფონები მდებარეობს.',
    'mo_ModuleAutoprovision' => 'ტელეფონის ავტომატური დაყენების მოდული',
    'BreadcrumbModuleAutoprovision' => 'ტელეფონის ავტომატური დაყენების მოდული',
    'SubHeaderModuleAutoprovision' => 'დახმარება SIP ტელეფონების დაყენებაში',
    'mod_Autoprovision_Extension' => 'გაფართოების ნომრის შაბლონი',
    'mod_Autoprovision_pbx_host' => 'სერვერის მისამართი ტელეფონის რეგისტრაციისთვის',
    'mod_Autoprovision_http_port' => 'ავტოპროვიზიონის HTTP პორტი',
    'mod_Autoprovision_http_port_hint' => 'მოდულის ცალკე TCP პორტი კონფიგურაციების სუფთა HTTP-ით მიწოდებისთვის — ექვემდებარება არ ერთიან HTTPS გადამისამართებას. ტელეფონებმა უნდა მიმართონ PBX-ს ამ პორტზე; firewall-ის წესი ემატება ავტომატურად.',
    'mod_Autoprovision_mac_black' => 'ტელეფონების MAC მისამართების შავი სია',
    'mod_Autoprovision_mac_white' => 'ტელეფონის MAC მისამართების თეთრი სია',
    'mod_Autoprovision_additional_params' => 'დამატებითი პარამეტრები',
    'mod_Autoprovision_tftp_enabled' => 'ჩართეთ TFTP პროვიზიონი (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'ააქტიურებს სუფთა PHP TFTP სერვერს UDP/69-ზე, რომელიც აწვდის იგივე MAC-ის მიხედვით კონფიგურაციებს, რასაც HTTP არხი, ასევე firmware ფაილებს «Firmware» ჩანართიდან.<br>გამოსადეგია, როცა multicast PnP დაბლოკილია (ტიპურია საოფისე WiFi-სა და მარშრუტიზებულ ქსელებში) ან როცა ტელეფონი ანიჭებს უპირატესობას DHCP option 66-ს (Snom, ზოგიერთი Fanvil firmware).<br>გარე ბინარული ფაილები არ არის საჭირო — მუშაობს მოდულის worker-ში. <b>ღია ტექსტური პროტოკოლი</b>: ჩართეთ მხოლოდ სანდო LAN-ში. UDP/69-ის firewall წესი იხსნება ავტომატურად.',
    'mod_Autoprovision_phone_settings_title' => 'ტელეფონის პარამეტრები',
    'mod_Autoprovision_phone_templates' => 'პარამეტრების შაბლონები',
    'mod_Autoprovision_general_settings' => 'URI პარამეტრები',
    'mod_Autoprovision_pnp' => 'PnP პარამეტრები',
    'mod_Autoprovision_addNew' => 'დამატება',
    'mod_Autoprovision_load_examples' => 'ნიმუშის შაბლონების ჩატვირთვა',
    'mod_Autoprovision_load_examples_hint' => 'ამატებს ჩაშენებულ ნიმუშის შაბლონებს Yealink-ისთვის, Fanvil-ისთვის, Snom-ისთვის, Grandstream-ისთვის და Htek-ისთვის. შაბლონები უკვე არსებული სახელებით გამოტოვებულია, ამიტომ ღილაკზე შესაძლებელია არაერთხელ დაჭერა დუბლიკატების შექმნის რისკის გარეშე.',
    'mod_Autoprovision_load_examples_installed' => 'ნიმუშის შაბლონები დაინსტალირდა',
    'mod_Autoprovision_load_examples_already_present' => 'ყველა ნიმუშის შაბლონი უკვე დაინსტალირებულია.',
    'mod_Autoprovision_load_examples_failed' => 'ნიმუშის შაბლონების დაყენება ვერ მოხერხდა',
    'mod_Autoprovision_load_examples_partial' => 'ნიმუშის შაბლონების ნაწილი არ დაინსტალირდა',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'გვერდზე არის შენახული ცვლილებები. ნიმუშების ჩატვირთვა გვერდს თავიდან ჩატვირთავს და ცვლილებებს გააუქმებს. გავაგრძელო?',
    'mod_Autoprovision_load_examples_post_only' => 'საჭიროა POST მოთხოვნა.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'ნიმუში',
    'mod_Autoprovision_phone_settings_user' => 'თანამშრომელი',
    'mod_Autoprovision_phone_settings_mac' => 'Mac მისამართი',
    'mod_Autoprovision_template_name' => 'სახელი',
    'mod_Autoprovision_search_tags' => 'ძიება...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'შაბლონის რედაქტირება',
    'mod_Autoprovision_end_edit_template' => 'დაასრულეთ რედაქტირება',
    'mod_Autoprovision_other_pbx' => 'Სატელეფონო წიგნი',
    'mod_Autoprovision_other_pbx_name' => 'სატელეფონო სადგურის დასახელება',
    'mod_Autoprovision_other_pbx_address' => 'PBX ქსელის მისამართი',
    'mod_Autoprovision_templates_header' => 'შაბლონის აღწერისას შეგიძლიათ გამოიყენოთ შემდეგი პარამეტრები: <b>{SIP_USER_NAME}</b> - თანამშრომლის სახელი <b>{SIP_NUM}</b> - შიდა ნომერი (შესვლა) <b>{SIP_PASS}</b> - პაროლი',
    'mod_Autoprovision_other_pbx_header' => '<b>ყურადღება!</b> სატელეფონო წიგნი ხელმისაწვდომი უნდა იყოს ყველა PBX-ზე URI-ით <b>/pbxcore/api/autoprovision-http/phonebook</b><br>ჩამოთვალეთ ყველა PBX-ის მისამართი, საიდანაც უნდა მოიძიო სატელეფონო წიგნი.<br>',
    'mod_Autoprovision_templates_users_header' => 'MAC მისამართის აღწერისას ნებადართულია სიმბოლო <b>%</b> - რაც ნიშნავს "ნებისმიერი სიმბოლოების კომპლექტს" <br>
შაბლონი <b>805e0c67%</b> დაემთხვევა <b>805e0c670001</b> და <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>ყურადღება!</b> ყველა URI იქმნება საბაზო მნიშვნელობასთან <b>/pbxcore/api/autoprovision-http</b> შედარებით<br>URI-ის აღწერისას შესაძლებელია სიმბოლოს <b>%</b> გამოყენება — ნიშნავს „ნებისმიერ სიმბოლოთა ნაკრებს“.<br>URI <b>/%/%/test.cfg</b> შეესაბამება <b>/1/2/test.cfg</b>-სა და <b>/test/test3/test.cfg</b>-ს',
    'mod_Autoprovision_header' => 'თუ მოდული ჩართულია, SIP ანგარიში "<b>apv-miko-pbx</b>" ხელმისაწვდომი გახდება PBX-ზე.
<br>თქვენი ტელეფონის ავტომატურად კონფიგურაციისთვის, თქვენ უნდა დააბრუნოთ ის ქარხნულ პარამეტრებზე.
<br>თუ ტელეფონი პირველად დაუკავშირდება PBX-ს, ის დარეგისტრირდება "<b>apv-miko-pbx</b>" ანგარიშზე.
<br>ტელეფონის კონფიგურაციისთვის, თქვენ უნდა დარეკოთ მისგან "<b>%extension%</b>", სადაც XXX არის PBX-ის შიდა ნომერი.
<br><br>
ავტოკონფიგურაცია შესაძლებელია მხოლოდ საწარმოს ლოკალური ქსელისთვის, ტელეფონებისთვის <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware-ები',
    'mod_Autoprovision_firmware_header' => 'ატვირთეთ firmware ფაილები, რომლებსაც PBX პროვიზიონის პორტიდან HTTP-ით მიაწვდის ტელეფონებს. შაბლონებში გამოიყენეთ placeholder <b>{FIRMWARE_URL}</b> ჩამოტვირთვის URL-ის ჩასმისთვის.',
    'mod_Autoprovision_firmware_drop_hint' => 'გადმოათრიეთ firmware ფაილი აქ ან დააწკაპუნეთ ასარჩევად',
    'mod_Autoprovision_firmware_browse' => 'ფაილის არჩევა',
    'mod_Autoprovision_firmware_vendor' => 'მწარმოებელი',
    'mod_Autoprovision_firmware_model' => 'მოდელი',
    'mod_Autoprovision_firmware_version' => 'ვერსია',
    'mod_Autoprovision_firmware_notes' => 'შენიშვნები',
    'mod_Autoprovision_firmware_filename' => 'ფაილი',
    'mod_Autoprovision_firmware_size' => 'ზომა',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Firmware-ის მეტამონაცემების რედაქტირება',
    'mod_Autoprovision_firmware_save' => 'შენახვა',
    'mod_Autoprovision_firmware_cancel' => 'გაუქმება',
];
