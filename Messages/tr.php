<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;

return [
    'mod_Autoprovision_additional_params' => 'Ek seçenekler',
    'mod_Autoprovision_mac_white' => 'Telefon MAC adresi beyaz listesi',
    'mod_Autoprovision_mac_black' => 'Telefonların MAC adreslerinin kara listesi',
    'mod_Autoprovision_pbx_host' => 'Telefon kaydı için sunucu adresi',
    'mod_Autoprovision_Extension' => 'Dahili numara şablonu',
    'SubHeaderModuleAutoprovision' => 'SIP telefonlarının kurulumunda yardım',
    'BreadcrumbModuleAutoprovision' => 'Otomatik telefon kurulum modülü',
    'repModuleAutoprovision' => 'Modül -% tekrarlama%',
    'mo_ModuleAutoprovision' => 'Otomatik telefon kurulum modülü',
    'mod_Autoprovision_header' => 'Modül etkinleştirilirse "<b>apv-miko-pbx</b>" SIP hesabı PBX\'te kullanılabilir hale gelir.
<br>Telefonunuzu otomatik olarak yapılandırmak için fabrika ayarlarına sıfırlamanız gerekir.
<br>Telefon PBX\'e ilk kez bağlanıyorsa "<b>apv-miko-pbx</b>" hesabına kaydedilecektir.
<br>Telefonu yapılandırmak için "<b>%extension%</b>" telefonunu aramanız gerekir; burada XXX, PBX\'teki dahili numaradır.
<br><br>
Otomatik yapılandırma yalnızca işletmenin yerel ağı için, <b>Yealink, Snom, Fanvil</b> telefonları için mümkündür.',
    'mod_Autoprovision_templates_uri_template' => 'Örnek',
    'mod_Autoprovision_phone_settings_user' => 'Çalışan',
    'mod_Autoprovision_phone_settings_mac' => 'Mac Adresi',
    'mod_Autoprovision_template_name' => 'İsim',
    'mod_Autoprovision_search_tags' => 'Aramak...',
    'mod_Autoprovision_edit_template' => 'Şablonu düzenleme',
    'mod_Autoprovision_phone_settings_title' => 'Telefon ayarları',
    'mod_Autoprovision_phone_templates' => 'Ayarlar şablonları',
    'mod_Autoprovision_general_settings' => 'URI Ayarları',
    'mod_Autoprovision_pnp' => 'PnP Ayarları',
    'mod_Autoprovision_addNew' => 'Eklemek',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_end_edit_template' => 'Düzenlemeyi bitir',
    'mod_Autoprovision_other_pbx' => 'Telefon rehberi',
    'mod_Autoprovision_other_pbx_name' => 'Telefon santralinin adı',
    'mod_Autoprovision_other_pbx_address' => 'PBX ağ adresi',
    'mod_Autoprovision_templates_header' => 'Bir şablonu tanımlarken aşağıdaki parametreleri kullanabilirsiniz: <b>{SIP_USER_NAME}</b> - çalışan adı <b>{SIP_NUM}</b> - dahili numara (giriş) <b>{SIP_PASS}</b> - şifre',
    'mod_Autoprovision_templates_users_header' => 'Bir MAC adresini tanımlarken, "herhangi bir karakter kümesi" anlamına gelen <b>%</b> sembolünün kullanılmasına izin verilir <br>
<b>805e0c67%</b> şablonu <b>805e0c670001</b> ve <b>805e0c670002</b> ile eşleşecek',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_other_pbx_header' => '<b>Warning!</b> The phone book must be accessible on every PBX at the URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>
List every address of the PBXes from which the phone book should be fetched.<br>',
    'mod_Autoprovision_templates_uri_header' => '<b>Warning!</b> All URIs are resolved relative to the base value <b>/pbxcore/api/autoprovision-http</b><br>
When describing a URI you may use the symbol <b>%</b> meaning "any set of characters". <br>
The URI <b>/%/%/test.cfg</b> will match <b>/1/2/test.cfg</b> and <b>/test/test3/test.cfg</b>',
];
