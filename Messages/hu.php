<?php
return [
    /*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */
    'mod_Autoprovision_Extension' => 'Kiterjesztés száma sablon',
    'mod_Autoprovision_other_pbx_name' => 'Alközpont neve',
    'mod_Autoprovision_other_pbx_address' => 'PBX hálózati cím',
    'repModuleAutoprovision' => 'Modul – %represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision – telefonkonfiguráció kiszolgálása HTTP-n',
    'fw_moduleautoprovisionDescriptionHint' => 'Dedikált TCP-port, amelyet a ModuleAutoprovision egyszerű HTTP-n szolgál ki (HTTPS-átirányítás nélkül).<br>Az IP-telefonok a kiosztás során ezen a porton keresztül töltik le a konfigurációs fájljaikat.<br>A hozzáférést csak arra a helyi hálózatra nyissa meg, amelyben a telefonok vannak.',
    'mo_ModuleAutoprovision' => 'Automatikus telefon konfigurációs modul',
    'BreadcrumbModuleAutoprovision' => 'Automatikus telefon konfigurációs modul',
    'SubHeaderModuleAutoprovision' => 'Segítség a SIP telefonok beállításában',
    'mod_Autoprovision_pbx_host' => 'Telefonos regisztrációhoz szükséges szerver címe',
    'mod_Autoprovision_mac_black' => 'A telefon MAC-címeinek feketelistája',
    'mod_Autoprovision_mac_white' => 'A telefon MAC-címeinek fehér listája',
    'mod_Autoprovision_additional_params' => 'Extra lehetőségek',
    'mod_Autoprovision_phone_settings_title' => 'Telefon beállítások',
    'mod_Autoprovision_phone_templates' => 'Beállítások sablonok',
    'mod_Autoprovision_general_settings' => 'URI beállítások',
    'mod_Autoprovision_pnp' => 'PnP beállítások',
    'mod_Autoprovision_addNew' => 'Hozzáadás',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Minta',
    'mod_Autoprovision_phone_settings_user' => 'Munkavállaló',
    'mod_Autoprovision_phone_settings_mac' => 'Mac cím',
    'mod_Autoprovision_template_name' => 'Név',
    'mod_Autoprovision_search_tags' => 'Keresés...',
    'mod_Autoprovision_edit_template' => 'Sablon szerkesztése',
    'mod_Autoprovision_end_edit_template' => 'Fejezze be a szerkesztést',
    'mod_Autoprovision_other_pbx' => 'Telefonkönyv',
    'mod_Autoprovision_templates_header' => 'A sablon leírásánál a következő paramétereket használhatja: <b>{SIP_USER_NAME}</b> - alkalmazott neve <b>{SIP_NUM}</b> - belső szám (bejelentkezés) <b>{SIP_PASS}</b> - jelszó',
    'mod_Autoprovision_templates_users_header' => 'A MAC-cím leírásakor megengedett a <b>%</b> szimbólum használata – ez „bármely karakterkészlet” <br>
A <b>805e0c67%</b> sablon egyezik a következővel: <b>805e0c670001</b> és <b>805e0c670002</b>',
    'mod_Autoprovision_header' => 'Ha a modul engedélyezve van, az "<b>apv-miko-pbx</b>" SIP-fiók elérhetővé válik az alközponton
<br>A telefon automatikus konfigurálásához vissza kell állítania a gyári beállításokat.
<br>Ha a telefon először csatlakozik az alközponthoz, akkor az „<b>apv-miko-pbx</b>” fiókban lesz regisztrálva.
<br>A telefon konfigurálásához fel kell hívnia a „<b>%extension%</b>” számot, ahol az XXX az alközpont belső száma.
<br><br>
Az automatikus konfigurálás csak a vállalati helyi hálózaton, a <b>Yealink, Snom, Fanvil</b> telefonokon lehetséges.',
    'mod_Autoprovision_filter_posts' => 'Select…',
];
