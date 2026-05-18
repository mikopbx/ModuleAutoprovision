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
    'repModuleAutoprovision' => 'Modul – %represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision – telefonkonfiguráció kiszolgálása HTTP-n',
    'fw_moduleautoprovisionDescriptionHint' => 'Dedikált TCP-port, amelyet a ModuleAutoprovision egyszerű HTTP-n szolgál ki (HTTPS-átirányítás nélkül).<br>Az IP-telefonok a kiosztás során ezen a porton keresztül töltik le a konfigurációs fájljaikat.<br>A hozzáférést csak arra a helyi hálózatra nyissa meg, amelyben a telefonok vannak.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP-szerver (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'Az UDP/69 portot a modulba épített, tiszta PHP-ben írt TFTP-szerver szolgálja ki.<br>Olyan telefonok és firmware-ek használják, amelyek a DHCP option 66 (TFTP) lehetőséget részesítik előnyben a multicast PnP-vel szemben — történelmileg Snom, néhány Fanvil firmware, valamint útválasztott hálózatok, ahol a multicast nem lépi át az L3 határt.<br><b>Sima szöveges protokoll:</b> csak abban a LAN-szegmensben nyissa meg a portot, ahol a telefonok találhatók.',
    'mo_ModuleAutoprovision' => 'Automatikus telefon konfigurációs modul',
    'BreadcrumbModuleAutoprovision' => 'Automatikus telefon konfigurációs modul',
    'SubHeaderModuleAutoprovision' => 'Segítség a SIP telefonok beállításában',
    'mod_Autoprovision_Extension' => 'Kiterjesztés száma sablon',
    'mod_Autoprovision_pbx_host' => 'Telefonos regisztrációhoz szükséges szerver címe',
    'mod_Autoprovision_http_port' => 'Autoprovision HTTP-port',
    'mod_Autoprovision_http_port_hint' => 'A modul külön TCP-portja a konfigurációk tiszta HTTP-n keresztüli kiszolgálására — nem vonatkozik rá a globális HTTPS-átirányítás. A telefonoknak ezen a porton kell elérniük a PBX-et; a tűzfalszabály automatikusan hozzáadódik.',
    'mod_Autoprovision_mac_black' => 'A telefon MAC-címeinek feketelistája',
    'mod_Autoprovision_mac_white' => 'A telefon MAC-címeinek fehér listája',
    'mod_Autoprovision_additional_params' => 'Extra lehetőségek',
    'mod_Autoprovision_tftp_enabled' => 'TFTP-alapú provisioning engedélyezése (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Elindít egy tiszta PHP-ben írt TFTP-szervert az UDP/69-en, amely ugyanazokat a MAC-szintű konfigurációkat szolgáltatja, mint a HTTP-csatorna, valamint a «Firmware» fülön található firmware-fájlokat is.<br>Hasznos, ha a multicast PnP blokkolva van (irodai WiFi és útválasztott hálózatoknál jellemző) vagy ha a telefon a DHCP option 66 lehetőséget részesíti előnyben (Snom, néhány Fanvil firmware).<br>Nincs szükség külső binárisokra — a modul workerén belül fut. <b>Sima szöveges protokoll</b>: csak megbízható LAN-on engedélyezze. Az UDP/69 tűzfalszabálya automatikusan megnyílik.',
    'mod_Autoprovision_phone_settings_title' => 'Telefon beállítások',
    'mod_Autoprovision_phone_templates' => 'Beállítások sablonok',
    'mod_Autoprovision_general_settings' => 'URI beállítások',
    'mod_Autoprovision_pnp' => 'PnP beállítások',
    'mod_Autoprovision_addNew' => 'Hozzáadás',
    'mod_Autoprovision_load_examples' => 'Példasablonok betöltése',
    'mod_Autoprovision_load_examples_hint' => 'Hozzáadja a beépített Yealink, Fanvil, Snom, Grandstream és Htek példasablonokat. A már létező nevű sablonokat kihagyja, ezért a gomb többször is megnyomható duplikációk kockázata nélkül.',
    'mod_Autoprovision_load_examples_installed' => 'Példasablonok telepítve',
    'mod_Autoprovision_load_examples_already_present' => 'Az összes példasablon már telepítve van.',
    'mod_Autoprovision_load_examples_failed' => 'A példasablonok telepítése sikertelen',
    'mod_Autoprovision_load_examples_partial' => 'Egyes példasablonok telepítése nem sikerült',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Az oldalon nem mentett módosítások vannak. A példák betöltése újratölti az oldalt és elveti azokat. Folytatja?',
    'mod_Autoprovision_load_examples_post_only' => 'POST kérés szükséges.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Minta',
    'mod_Autoprovision_phone_settings_user' => 'Munkavállaló',
    'mod_Autoprovision_phone_settings_mac' => 'Mac cím',
    'mod_Autoprovision_template_name' => 'Név',
    'mod_Autoprovision_search_tags' => 'Keresés...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Sablon szerkesztése',
    'mod_Autoprovision_end_edit_template' => 'Fejezze be a szerkesztést',
    'mod_Autoprovision_other_pbx' => 'Telefonkönyv',
    'mod_Autoprovision_other_pbx_name' => 'Alközpont neve',
    'mod_Autoprovision_other_pbx_address' => 'PBX hálózati cím',
    'mod_Autoprovision_templates_header' => 'A sablon leírásánál a következő paramétereket használhatja: <b>{SIP_USER_NAME}</b> - alkalmazott neve <b>{SIP_NUM}</b> - belső szám (bejelentkezés) <b>{SIP_PASS}</b> - jelszó',
    'mod_Autoprovision_other_pbx_header' => '<b>Figyelem!</b> A telefonkönyvnek minden PBX-en elérhetőnek kell lennie a <b>/pbxcore/api/autoprovision-http/phonebook</b> URI-n<br>Sorolja fel az összes PBX címét, amelyekről a telefonkönyvet le kell kérni.<br>',
    'mod_Autoprovision_templates_users_header' => 'A MAC-cím leírásakor megengedett a <b>%</b> szimbólum használata – ez „bármely karakterkészlet” <br>
A <b>805e0c67%</b> sablon egyezik a következővel: <b>805e0c670001</b> és <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Figyelem!</b> Minden URI a <b>/pbxcore/api/autoprovision-http</b> alapérték relatívan épül fel<br>URI leírásakor használható a <b>%</b> szimbólum — „tetszőleges karaktersorozatot“ jelent.<br>A <b>/%/%/test.cfg</b> URI illeszkedik a <b>/1/2/test.cfg</b> és a <b>/test/test3/test.cfg</b> címekre',
    'mod_Autoprovision_header' => 'Ha a modul engedélyezve van, az "<b>apv-miko-pbx</b>" SIP-fiók elérhetővé válik az alközponton
<br>A telefon automatikus konfigurálásához vissza kell állítania a gyári beállításokat.
<br>Ha a telefon először csatlakozik az alközponthoz, akkor az „<b>apv-miko-pbx</b>” fiókban lesz regisztrálva.
<br>A telefon konfigurálásához fel kell hívnia a „<b>%extension%</b>” számot, ahol az XXX az alközpont belső száma.
<br><br>
Az automatikus konfigurálás csak a vállalati helyi hálózaton, a <b>Yealink, Snom, Fanvil</b> telefonokon lehetséges.',
    'mod_Autoprovision_firmware' => 'Firmware-ek',
    'mod_Autoprovision_firmware_header' => 'Töltsön fel firmware-fájlokat, amelyeket a PBX a provisioning porton keresztül HTTP-n szolgáltat a telefonoknak. Használja sablonjaiban a <b>{FIRMWARE_URL}</b> helyőrzőt a letöltési URL beillesztéséhez.',
    'mod_Autoprovision_firmware_drop_hint' => 'Húzzon ide egy firmware-fájlt, vagy kattintson a kiválasztáshoz',
    'mod_Autoprovision_firmware_browse' => 'Fájl választása',
    'mod_Autoprovision_firmware_vendor' => 'Gyártó',
    'mod_Autoprovision_firmware_model' => 'Modell',
    'mod_Autoprovision_firmware_version' => 'Verzió',
    'mod_Autoprovision_firmware_notes' => 'Megjegyzések',
    'mod_Autoprovision_firmware_filename' => 'Fájl',
    'mod_Autoprovision_firmware_size' => 'Méret',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Firmware-metaadatok szerkesztése',
    'mod_Autoprovision_firmware_save' => 'Mentés',
    'mod_Autoprovision_firmware_cancel' => 'Mégse',
];
