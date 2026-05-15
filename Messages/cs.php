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
    'repModuleAutoprovision' => '%represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision – doručování konfigurace telefonů přes HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Vyhrazený TCP port obsluhovaný modulem ModuleAutoprovision přes čisté HTTP (bez přesměrování na HTTPS).<br>IP telefony si z tohoto portu během automatické konfigurace stahují své konfigurační soubory.<br>Přístup otevřete pouze pro lokální síť, ve které telefony jsou.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP server (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 obsluhované vestavěným TFTP serverem v čistém PHP.<br>Používá se telefony a firmwary, které dávají přednost DHCP option 66 (TFTP) před multicast PnP — historicky Snom, některé firmwary Fanvil a směrované sítě, kde multicast nepřechází přes L3 hranici.<br><b>Otevřený textový protokol:</b> port otevírejte pouze v segmentu LAN, kde jsou telefony.',
    'mo_ModuleAutoprovision' => 'Modul automatického nastavení telefonu',
    'BreadcrumbModuleAutoprovision' => 'Modul automatického nastavení telefonu',
    'SubHeaderModuleAutoprovision' => 'Pomoc s nastavením SIP telefonů',
    'mod_Autoprovision_Extension' => 'Šablona čísla přípony',
    'mod_Autoprovision_pbx_host' => 'Adresa serveru pro telefonickou registraci',
    'mod_Autoprovision_http_port' => 'Port HTTP pro autoprovisioning',
    'mod_Autoprovision_http_port_hint' => 'Vyhrazený TCP port modulu pro odesílání konfigurací po čistém HTTP — nepodléhá globálnímu přesměrování na HTTPS. Telefony musí PBX kontaktovat na tomto portu; pravidlo firewallu se přidává automaticky.',
    'mod_Autoprovision_mac_black' => 'Černá listina MAC adres telefonů',
    'mod_Autoprovision_mac_white' => 'Seznam povolených MAC adres telefonu',
    'mod_Autoprovision_additional_params' => 'Další možnosti',
    'mod_Autoprovision_tftp_enabled' => 'Povolit provisioning přes TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Spustí vestavěný TFTP server v čistém PHP (UDP/69), který poskytuje stejné konfigurace dle MAC jako kanál HTTP a také firmwary ze záložky «Firmware».<br>Užitečné, pokud je multicast PnP blokován (typické v kancelářských WiFi a směrovaných sítích) nebo telefon preferuje DHCP option 66 (Snom, některé firmwary Fanvil).<br>Žádné externí binární soubory — běží uvnitř workeru modulu. <b>Otevřený textový protokol</b>: povolujte pouze v důvěryhodné LAN. Pravidlo firewallu pro UDP/69 se otevírá automaticky.',
    'mod_Autoprovision_phone_settings_title' => 'Nastavení telefonu',
    'mod_Autoprovision_phone_templates' => 'Šablony nastavení',
    'mod_Autoprovision_general_settings' => 'Nastavení URI',
    'mod_Autoprovision_pnp' => 'Nastavení PnP',
    'mod_Autoprovision_addNew' => 'Přidat',
    'mod_Autoprovision_load_examples' => 'Načíst příklady šablon',
    'mod_Autoprovision_load_examples_hint' => 'Přidá zabudované příklady šablon pro Yealink, Fanvil, Snom, Grandstream a Htek. Šablony s již existujícími názvy budou přeskočeny, takže tlačítko lze stisknout opakovaně bez rizika duplikátů.',
    'mod_Autoprovision_load_examples_installed' => 'Příklady šablon byly nainstalovány',
    'mod_Autoprovision_load_examples_already_present' => 'Všechny příklady šablon jsou již nainstalovány.',
    'mod_Autoprovision_load_examples_failed' => 'Příklady šablon se nepodařilo nainstalovat',
    'mod_Autoprovision_load_examples_partial' => 'Část příkladů šablon se nenainstalovala',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Na stránce jsou neuložené změny. Načtení příkladů znovu načte stránku a tyto změny zruší. Pokračovat?',
    'mod_Autoprovision_load_examples_post_only' => 'Vyžaduje se POST požadavek.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Vzorek',
    'mod_Autoprovision_phone_settings_user' => 'Zaměstnanec',
    'mod_Autoprovision_phone_settings_mac' => 'MAC adresa',
    'mod_Autoprovision_template_name' => 'název',
    'mod_Autoprovision_search_tags' => 'Vyhledávání...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Úprava šablony',
    'mod_Autoprovision_end_edit_template' => 'Dokončete úpravy',
    'mod_Autoprovision_other_pbx' => 'Telefonní seznam',
    'mod_Autoprovision_other_pbx_name' => 'Název telefonní ústředny',
    'mod_Autoprovision_other_pbx_address' => 'Síťová adresa PBX',
    'mod_Autoprovision_templates_header' => 'Při popisu šablony můžete použít následující parametry: <b>{SIP_USER_NAME}</b> - jméno zaměstnance <b>{SIP_NUM}</b> - interní číslo (login) <b>{SIP_PASS}</b> - heslo',
    'mod_Autoprovision_other_pbx_header' => '<b>Pozor!</b> Telefonní seznam musí být dostupný na každé PBX na URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Uveďte všechny adresy PBX, ze kterých chcete získat telefonní seznam.<br>',
    'mod_Autoprovision_templates_users_header' => 'Při popisu MAC adresy je povoleno používat symbol <b>%</b> – což znamená „jakákoli sada znaků“ <br>
Šablona <b>805e0c67 %</b> bude odpovídat <b>805e0c670001</b> a <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Pozor!</b> Všechny URI se vytvářejí relativně k základní hodnotě <b>/pbxcore/api/autoprovision-http</b><br>Při popisu URI lze použít symbol <b>%</b> — znamená „libovolnou sadu znaků“.<br>URI <b>/%/%/test.cfg</b> bude odpovídat <b>/1/2/test.cfg</b> i <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Pokud je modul povolen, zpřístupní se na ústředně SIP účet "<b>apv-miko-pbx</b>".
<br>Chcete-li telefon automaticky nakonfigurovat, musíte jej resetovat do továrního nastavení.
<br>Pokud se telefon připojí k ústředně poprvé, bude zaregistrován k účtu "<b>apv-miko-pbx</b>".
<br>Pro konfiguraci telefonu z něj musíte zavolat na "<b>%extension%</b>", kde XXX je interní číslo ústředny.
<br><br>
Automatická konfigurace je možná pouze pro lokální síť podniku, pro telefony <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmwary',
    'mod_Autoprovision_firmware_header' => 'Nahrajte soubory firmwarů, které PBX bude poskytovat telefonům přes HTTP na provisioning portu. V šablonách použijte placeholder <b>{FIRMWARE_URL}</b> pro vložení URL stahování.',
    'mod_Autoprovision_firmware_drop_hint' => 'Přetáhněte sem soubor firmwaru nebo klikněte pro výběr',
    'mod_Autoprovision_firmware_browse' => 'Vybrat soubor',
    'mod_Autoprovision_firmware_vendor' => 'Výrobce',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Verze',
    'mod_Autoprovision_firmware_notes' => 'Poznámky',
    'mod_Autoprovision_firmware_filename' => 'Soubor',
    'mod_Autoprovision_firmware_size' => 'Velikost',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Úprava metadat firmwaru',
    'mod_Autoprovision_firmware_save' => 'Uložit',
    'mod_Autoprovision_firmware_cancel' => 'Zrušit',
];
