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
    'repModuleAutoprovision' => 'Modul - %represent%',
    'fw_moduleautoprovisionDescription' => 'Avtoprovayder — IP telefonların konfiqurasiyasının HTTP üzərindən çatdırılması',
    'fw_moduleautoprovisionDescriptionHint' => 'ModuleAutoprovision tərəfindən saf HTTP üzərindən təqdim edilən xüsusi TCP portu (HTTPS yönləndirməsi yoxdur).<br>IP telefonlar avtokonfiqurasiya zamanı öz konfiqurasiya fayllarını bu portdan götürür.<br>Girişi yalnız telefonların yerləşdiyi lokal şəbəkə üçün açın.',
    'fw_AutoprovisionTftpPortDescription' => 'Avtoprovision — TFTP server (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 modul daxilindəki saf PHP TFTP serveri tərəfindən xidmət olunur.<br>Multicast PnP əvəzinə DHCP option 66 (TFTP) üstün tutan telefonlar və firmware-lər tərəfindən istifadə olunur — tarixən Snom, bəzi Fanvil firmware-ləri, həmçinin multicast L3 sərhədini keçmədiyi marşrutlanan şəbəkələr.<br><b>Açıq mətn protokoldur:</b> portu yalnız telefonların yerləşdiyi LAN seqmentində açın.',
    'mo_ModuleAutoprovision' => 'Telefonun avtomatik konfiqurasiya modulu',
    'BreadcrumbModuleAutoprovision' => 'Telefonun avtomatik konfiqurasiya modulu',
    'SubHeaderModuleAutoprovision' => 'SIP telefonlarının qurulmasında kömək edin',
    'mod_Autoprovision_Extension' => 'Genişləndirici şablon',
    'mod_Autoprovision_pbx_host' => 'Telefon qeydiyyatı üçün server ünvanı',
    'mod_Autoprovision_http_port' => 'Avtoprovision HTTP portu',
    'mod_Autoprovision_http_port_hint' => 'Modulun konfiqurasiyaları saf HTTP ilə paylaşdığı ayrıca TCP port — qlobal HTTPS yönləndirməsindən kənarda qalır. Telefonlar PBX-ə bu port vasitəsilə müraciət etməlidir; firewall qaydası avtomatik əlavə olunur.',
    'mod_Autoprovision_mac_black' => 'Telefon MAC Qara Siyahı',
    'mod_Autoprovision_mac_white' => 'Telefonun MAC ünvanının ağ siyahısı',
    'mod_Autoprovision_additional_params' => 'Əlavə seçimlər',
    'mod_Autoprovision_tftp_enabled' => 'TFTP provizioninqi aktiv et (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'UDP/69-da saf PHP TFTP serverini işə salır, eyni MAC üzrə konfiqurasiyaları HTTP kanalı kimi paylaşır, həmçinin «Firmware» bölməsindəki firmware fayllarını verir.<br>Multicast PnP bloklanıbsa (ofis WiFi və marşrutlanan şəbəkələrdə tipikdir) və ya telefon DHCP option 66-ya üstünlük verirsə (Snom, bəzi Fanvil firmware-ləri) faydalıdır.<br>Heç bir xarici binary tələb olunmur — modul worker-i daxilində işləyir. <b>Açıq mətn protokoldur</b>: yalnız etibarlı LAN-da aktiv edin. UDP/69 üçün firewall qaydası avtomatik açılır.',
    'mod_Autoprovision_phone_settings_title' => 'Telefon parametrləri',
    'mod_Autoprovision_phone_templates' => 'Parametrlər şablonları',
    'mod_Autoprovision_general_settings' => 'URI Parametrləri',
    'mod_Autoprovision_pnp' => 'PnP Parametrləri',
    'mod_Autoprovision_addNew' => 'əlavə et',
    'mod_Autoprovision_load_examples' => 'Şablon nümunələrini yüklə',
    'mod_Autoprovision_load_examples_hint' => 'Yealink, Fanvil, Snom, Grandstream və Htek üçün yerləşdirilmiş şablon nümunələrini əlavə edir. Adları artıq mövcud olan şablonlar atlanır, ona görə dublikat yaratmadan düyməni təkrar basmaq olar.',
    'mod_Autoprovision_load_examples_installed' => 'Şablon nümunələri quraşdırıldı',
    'mod_Autoprovision_load_examples_already_present' => 'Bütün şablon nümunələri artıq quraşdırılıb.',
    'mod_Autoprovision_load_examples_failed' => 'Şablon nümunələrini quraşdırmaq mümkün olmadı',
    'mod_Autoprovision_load_examples_partial' => 'Şablon nümunələrinin bir hissəsi quraşdırılmadı',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Bu səhifədə yadda saxlanılmamış dəyişikliklər var. Nümunələri yükləmək səhifəni yenidən yükləyəcək və dəyişiklikləri sıfırlayacaq. Davam edilsin?',
    'mod_Autoprovision_load_examples_post_only' => 'POST sorğusu tələb olunur.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Nümunə',
    'mod_Autoprovision_phone_settings_user' => 'işçi',
    'mod_Autoprovision_phone_settings_mac' => 'MAC Ünvanı',
    'mod_Autoprovision_template_name' => 'ad',
    'mod_Autoprovision_search_tags' => 'Axtar...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Şablonu redaktə etmək',
    'mod_Autoprovision_end_edit_template' => 'Redaktəni tamamlayın',
    'mod_Autoprovision_other_pbx' => 'Telefon kitabçası',
    'mod_Autoprovision_other_pbx_name' => 'Telefon stansiyasının adı',
    'mod_Autoprovision_other_pbx_address' => 'PBX şəbəkə ünvanı',
    'mod_Autoprovision_templates_header' => 'Şablonu təsvir edərkən aşağıdakı parametrlərdən istifadə edə bilərsiniz: <b>{SIP_USER_NAME}</b> - işçi adı <b>{SIP_NUM}</b> - daxili nömrə (giriş) <b>{SIP_PASS}</b> - parol',
    'mod_Autoprovision_other_pbx_header' => '<b>Diqqət!</b> Telefon kitabçası hər PBX üçün <b>/pbxcore/api/autoprovision-http/phonebook</b> URI-də əlçatan olmalıdır<br>Telefon kitabçasını almaq lazım olan PBX-lərin bütün ünvanlarını sadalayın.<br>',
    'mod_Autoprovision_templates_users_header' => 'MAC ünvanını təsvir edərkən <b>%</b> simvolundan istifadə etməyə icazə verilir - "hər hansı bir simvol dəsti" mənasını verir <br>
<b>805e0c67%</b> şablonu <b>805e0c670001</b> və <b>805e0c670002</b> ilə uyğunlaşacaq',
    'mod_Autoprovision_templates_uri_header' => '<b>Diqqət!</b> Bütün URI-lər <b>/pbxcore/api/autoprovision-http</b> əsas dəyərinə görə qurulur<br>URI təsvirində <b>%</b> simvolu — «istənilən simvol dəsti» mənasını verir — istifadə oluna bilər.<br><b>/%/%/test.cfg</b> URI-si <b>/1/2/test.cfg</b> və <b>/test/test3/test.cfg</b> ilə uyğunlaşır',
    'mod_Autoprovision_header' => 'Modul işə salındıqda, "<b>apv-miko-pbx</b>" SIP hesabı ATS-də əlçatan olur.
<br>Telefonunuzu avtomatik konfiqurasiya etmək üçün onu zavod parametrlərinə sıfırlamalısınız.
<br>Əgər telefon ilk dəfə PBX-ə qoşularsa, o, "<b>apv-miko-pbx</b>" hesabına qeydiyyatdan keçəcək.
<br>Telefonu konfiqurasiya etmək üçün siz ondan "<b>%extension%</b>" zəng etməlisiniz, burada XXX PBX-də daxili nömrədir.
<br><br>
Avtokonfiqurasiya yalnız müəssisənin yerli şəbəkəsi, <b>Yealink, Snom, Fanvil</b> telefonları üçün mümkündür.',
    'mod_Autoprovision_firmware' => 'Firmware-lər',
    'mod_Autoprovision_firmware_header' => 'PBX-in provizioninq portu vasitəsilə telefonlara HTTP-də verəcəyi firmware fayllarını yükləyin. Yükləmə URL-ni daxil etmək üçün şablonlarda <b>{FIRMWARE_URL}</b> placeholder-indən istifadə edin.',
    'mod_Autoprovision_firmware_drop_hint' => 'Firmware faylını buraya sürükləyin və ya seçmək üçün klikləyin',
    'mod_Autoprovision_firmware_browse' => 'Fayl seçin',
    'mod_Autoprovision_firmware_vendor' => 'İstehsalçı',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Versiya',
    'mod_Autoprovision_firmware_notes' => 'Qeydlər',
    'mod_Autoprovision_firmware_filename' => 'Fayl',
    'mod_Autoprovision_firmware_size' => 'Ölçü',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Firmware metaməlumatlarını redaktə et',
    'mod_Autoprovision_firmware_save' => 'Saxla',
    'mod_Autoprovision_firmware_cancel' => 'Ləğv et',
];
