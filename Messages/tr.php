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
    'repModuleAutoprovision' => 'Modül -% tekrarlama%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - HTTP üzerinden telefon yapılandırması teslimi',
    'fw_moduleautoprovisionDescriptionHint' => 'ModuleAutoprovision tarafından düz HTTP üzerinden sunulan özel TCP portu (HTTPS yönlendirmesi yok).<br>IP telefonlar otomatik yapılandırma sırasında konfigürasyon dosyalarını bu porttan alır.<br>Erişimi yalnızca telefonların bulunduğu yerel ağa açın.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP sunucusu (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 modüle gömülü, saf PHP ile yazılmış TFTP sunucusu tarafından sunulur.<br>Multicast PnP yerine DHCP option 66 (TFTP) tercih eden telefonlar ve firmware\'ler tarafından kullanılır — geçmişte Snom, bazı Fanvil firmware\'leri ve multicast\'in L3 sınırını geçmediği yönlendirilmiş ağlar.<br><b>Düz metin protokol:</b> portu yalnızca telefonların bulunduğu LAN segmentinde açın.',
    'mo_ModuleAutoprovision' => 'Otomatik telefon kurulum modülü',
    'BreadcrumbModuleAutoprovision' => 'Otomatik telefon kurulum modülü',
    'SubHeaderModuleAutoprovision' => 'SIP telefonlarının kurulumunda yardım',
    'mod_Autoprovision_Extension' => 'Dahili numara şablonu',
    'mod_Autoprovision_pbx_host' => 'Telefon kaydı için sunucu adresi',
    'mod_Autoprovision_http_port' => 'Otomatik provizyon HTTP portu',
    'mod_Autoprovision_http_port_hint' => 'Modülün yapılandırmaları saf HTTP üzerinden sunduğu özel TCP portu — global HTTPS yönlendirmesine tabi değildir. Telefonların PBX\'e bu port üzerinden erişmesi gerekir; güvenlik duvarı kuralı otomatik eklenir.',
    'mod_Autoprovision_mac_black' => 'Telefonların MAC adreslerinin kara listesi',
    'mod_Autoprovision_mac_white' => 'Telefon MAC adresi beyaz listesi',
    'mod_Autoprovision_additional_params' => 'Ek seçenekler',
    'mod_Autoprovision_tftp_enabled' => 'TFTP provizyonunu etkinleştir (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'UDP/69\'da saf PHP\'de yazılmış bir TFTP sunucusu başlatır; HTTP kanalı ile aynı MAC bazlı yapılandırmaları ve «Firmware» sekmesindeki firmware dosyalarını sunar.<br>Multicast PnP\'nin engellendiği durumlarda (ofis WiFi\'lerinde ve yönlendirilmiş ağlarda tipiktir) veya telefon DHCP option 66\'yı tercih ediyorsa (Snom, bazı Fanvil firmware\'leri) yararlıdır.<br>Harici binary yok — modül worker\'ının içinde çalışır. <b>Düz metin protokol</b>: yalnızca güvenilir LAN\'da etkinleştirin. UDP/69 için güvenlik duvarı kuralı otomatik olarak açılır.',
    'mod_Autoprovision_phone_settings_title' => 'Telefon ayarları',
    'mod_Autoprovision_phone_templates' => 'Ayarlar şablonları',
    'mod_Autoprovision_general_settings' => 'URI Ayarları',
    'mod_Autoprovision_pnp' => 'PnP Ayarları',
    'mod_Autoprovision_addNew' => 'Eklemek',
    'mod_Autoprovision_load_examples' => 'Örnek şablonları yükle',
    'mod_Autoprovision_load_examples_hint' => 'Yealink, Fanvil, Snom, Grandstream ve Htek için gömülü örnek şablonları ekler. Aynı isimde mevcut şablonlar atlanır, bu nedenle düğmeye art arda basmak çift kayıt riski olmadan güvenlidir.',
    'mod_Autoprovision_load_examples_installed' => 'Örnek şablonlar yüklendi',
    'mod_Autoprovision_load_examples_already_present' => 'Tüm örnek şablonlar zaten yüklü.',
    'mod_Autoprovision_load_examples_failed' => 'Örnek şablonlar yüklenemedi',
    'mod_Autoprovision_load_examples_partial' => 'Örnek şablonların bir kısmı yüklenemedi',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Sayfada kaydedilmemiş değişiklikler var. Örnekleri yüklemek sayfayı yeniden yükleyecek ve değişiklikleri iptal edecektir. Devam edilsin mi?',
    'mod_Autoprovision_load_examples_post_only' => 'POST isteği gerekli.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Örnek',
    'mod_Autoprovision_phone_settings_user' => 'Çalışan',
    'mod_Autoprovision_phone_settings_mac' => 'Mac Adresi',
    'mod_Autoprovision_template_name' => 'İsim',
    'mod_Autoprovision_search_tags' => 'Aramak...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Şablonu düzenleme',
    'mod_Autoprovision_end_edit_template' => 'Düzenlemeyi bitir',
    'mod_Autoprovision_other_pbx' => 'Telefon rehberi',
    'mod_Autoprovision_other_pbx_name' => 'Telefon santralinin adı',
    'mod_Autoprovision_other_pbx_address' => 'PBX ağ adresi',
    'mod_Autoprovision_templates_header' => 'Bir şablonu tanımlarken aşağıdaki parametreleri kullanabilirsiniz: <b>{SIP_USER_NAME}</b> - çalışan adı <b>{SIP_NUM}</b> - dahili numara (giriş) <b>{SIP_PASS}</b> - şifre',
    'mod_Autoprovision_other_pbx_header' => '<b>Dikkat!</b> Telefon rehberi her PBX\'te <b>/pbxcore/api/autoprovision-http/phonebook</b> URI\'sinde erişilebilir olmalıdır<br>Telefon rehberi alınacak tüm PBX adreslerini listeleyin.<br>',
    'mod_Autoprovision_templates_users_header' => 'Bir MAC adresini tanımlarken, "herhangi bir karakter kümesi" anlamına gelen <b>%</b> sembolünün kullanılmasına izin verilir <br>
<b>805e0c67%</b> şablonu <b>805e0c670001</b> ve <b>805e0c670002</b> ile eşleşecek',
    'mod_Autoprovision_templates_uri_header' => '<b>Dikkat!</b> Tüm URI\'ler <b>/pbxcore/api/autoprovision-http</b> temel değerine göre oluşturulur<br>URI tanımlanırken <b>%</b> sembolü kullanılabilir — „herhangi bir karakter dizisi“ anlamına gelir.<br><b>/%/%/test.cfg</b> URI\'si <b>/1/2/test.cfg</b> ve <b>/test/test3/test.cfg</b> ile eşleşir',
    'mod_Autoprovision_header' => 'Modül etkinleştirilirse "<b>apv-miko-pbx</b>" SIP hesabı PBX\'te kullanılabilir hale gelir.
<br>Telefonunuzu otomatik olarak yapılandırmak için fabrika ayarlarına sıfırlamanız gerekir.
<br>Telefon PBX\'e ilk kez bağlanıyorsa "<b>apv-miko-pbx</b>" hesabına kaydedilecektir.
<br>Telefonu yapılandırmak için "<b>%extension%</b>" telefonunu aramanız gerekir; burada XXX, PBX\'teki dahili numaradır.
<br><br>
Otomatik yapılandırma yalnızca işletmenin yerel ağı için, <b>Yealink, Snom, Fanvil</b> telefonları için mümkündür.',
    'mod_Autoprovision_firmware' => 'Firmware\'ler',
    'mod_Autoprovision_firmware_header' => 'PBX\'in provizyon portu üzerinden HTTP ile telefonlara sunacağı firmware dosyalarını yükleyin. İndirme URL\'ini eklemek için şablonlarınızda <b>{FIRMWARE_URL}</b> yer tutucusunu kullanın.',
    'mod_Autoprovision_firmware_drop_hint' => 'Firmware dosyasını buraya sürükleyin veya seçmek için tıklayın',
    'mod_Autoprovision_firmware_browse' => 'Dosya seç',
    'mod_Autoprovision_firmware_vendor' => 'Üretici',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Sürüm',
    'mod_Autoprovision_firmware_notes' => 'Notlar',
    'mod_Autoprovision_firmware_filename' => 'Dosya',
    'mod_Autoprovision_firmware_size' => 'Boyut',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Firmware meta verilerini düzenle',
    'mod_Autoprovision_firmware_save' => 'Kaydet',
    'mod_Autoprovision_firmware_cancel' => 'İptal',
];
