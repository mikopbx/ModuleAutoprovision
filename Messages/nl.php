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
    'repModuleAutoprovision' => 'Module - %represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovisioning - telefoonconfiguratie leveren via HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Toegewijde TCP-poort die ModuleAutoprovision over gewone HTTP bedient (geen HTTPS-redirect).<br>IP-telefoons halen hun configuratiebestanden tijdens provisioning op via deze poort.<br>Open de toegang alleen voor het lokale netwerk waarin de telefoons staan.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP-server (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 wordt bediend door de in de module geïntegreerde TFTP-server in pure PHP.<br>Wordt gebruikt door telefoons en firmware die DHCP option 66 (TFTP) verkiezen boven multicast PnP — historisch Snom, sommige Fanvil-firmwares en gerouteerde netwerken waarin multicast de L3-grens niet overschrijdt.<br><b>Onversleuteld protocol:</b> open de poort alleen in het LAN-segment waar de telefoons staan.',
    'mo_ModuleAutoprovision' => 'Autoprovision module',
    'BreadcrumbModuleAutoprovision' => 'Autoprovision module',
    'SubHeaderModuleAutoprovision' => 'Bulk IP-telefoon installatie',
    'mod_Autoprovision_Extension' => 'Instelpatroon',
    'mod_Autoprovision_pbx_host' => 'PBX DNS naam',
    'mod_Autoprovision_http_port' => 'Autoprovision HTTP-poort',
    'mod_Autoprovision_http_port_hint' => 'Toegewijde TCP-poort van de module voor het aanleveren van configuraties via pure HTTP — valt niet onder de globale HTTPS-redirect. Telefoons moeten de PBX via deze poort benaderen; firewall-regel wordt automatisch toegevoegd.',
    'mod_Autoprovision_mac_black' => 'MAC-blacklist',
    'mod_Autoprovision_mac_white' => 'MAC-whitelist',
    'mod_Autoprovision_additional_params' => 'Overige instellingen',
    'mod_Autoprovision_tftp_enabled' => 'TFTP-provisioning inschakelen (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Start een TFTP-server in pure PHP op UDP/69 die dezelfde per-MAC configuraties levert als het HTTP-kanaal, plus firmware uit het tabblad «Firmware».<br>Handig als multicast PnP geblokkeerd is (typisch in kantoor-WiFi en gerouteerde netwerken) of als de telefoon DHCP option 66 verkiest (Snom, sommige Fanvil-firmwares).<br>Geen externe binaries — draait binnen de worker van de module. <b>Onversleuteld protocol</b>: alleen inschakelen in een vertrouwd LAN. De firewall-regel voor UDP/69 wordt automatisch geopend.',
    'mod_Autoprovision_phone_settings_title' => 'Telefoon instellingen',
    'mod_Autoprovision_phone_templates' => 'Sjablonen voor instellingen',
    'mod_Autoprovision_general_settings' => 'URI-instellingen',
    'mod_Autoprovision_pnp' => 'PnP-instellingen',
    'mod_Autoprovision_addNew' => 'Toevoegen',
    'mod_Autoprovision_load_examples' => 'Voorbeeldsjablonen laden',
    'mod_Autoprovision_load_examples_hint' => 'Voegt de meegeleverde voorbeeldsjablonen voor Yealink, Fanvil, Snom, Grandstream en Htek toe. Sjablonen met reeds bestaande namen worden overgeslagen, dus de knop kan meerdere keren worden ingedrukt zonder risico op duplicaten.',
    'mod_Autoprovision_load_examples_installed' => 'Voorbeeldsjablonen geïnstalleerd',
    'mod_Autoprovision_load_examples_already_present' => 'Alle voorbeeldsjablonen zijn al geïnstalleerd.',
    'mod_Autoprovision_load_examples_failed' => 'Installatie van voorbeeldsjablonen is mislukt',
    'mod_Autoprovision_load_examples_partial' => 'Sommige voorbeeldsjablonen konden niet worden geïnstalleerd',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Er zijn niet-opgeslagen wijzigingen op de pagina. Door de voorbeelden te laden wordt de pagina opnieuw geladen en gaan deze verloren. Doorgaan?',
    'mod_Autoprovision_load_examples_post_only' => 'POST-verzoek vereist.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Steekproef',
    'mod_Autoprovision_phone_settings_user' => 'Medewerker',
    'mod_Autoprovision_phone_settings_mac' => 'Mac adres',
    'mod_Autoprovision_template_name' => 'Naam',
    'mod_Autoprovision_search_tags' => 'Zoekopdracht...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Een sjabloon bewerken',
    'mod_Autoprovision_end_edit_template' => 'Voltooi het bewerken',
    'mod_Autoprovision_other_pbx' => 'Telefoonboek',
    'mod_Autoprovision_other_pbx_name' => 'Naam van de telefooncentrale',
    'mod_Autoprovision_other_pbx_address' => 'PBX-netwerkadres',
    'mod_Autoprovision_templates_header' => 'Bij het beschrijven van een sjabloon kunt u de volgende parameters gebruiken: <b>{SIP_USER_NAME}</b> - naam van de werknemer <b>{SIP_NUM}</b> - intern nummer (login) <b>{SIP_PASS}</b> - wachtwoord',
    'mod_Autoprovision_other_pbx_header' => '<b>Let op!</b> Het telefoonboek moet op elke PBX toegankelijk zijn op de URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Voer alle adressen op van de PBX\'en waarvan het telefoonboek moet worden opgehaald.<br>',
    'mod_Autoprovision_templates_users_header' => 'Bij het beschrijven van een MAC-adres is het toegestaan om het symbool <b>%</b> te gebruiken, wat \'elke reeks tekens\' betekent <br>
De sjabloon <b>805e0c67%</b> komt overeen met <b>805e0c670001</b> en <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Let op!</b> Alle URI\'s worden opgebouwd ten opzichte van de basiswaarde <b>/pbxcore/api/autoprovision-http</b><br>Bij het beschrijven van een URI mag het symbool <b>%</b> worden gebruikt — dat betekent „elke reeks tekens“.<br>De URI <b>/%/%/test.cfg</b> komt overeen met <b>/1/2/test.cfg</b> en met <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Als de module is ingeschakeld, wordt het SIP-account "<b>apv-miko-pbx</b>" beschikbaar op de PBX.
<br>Als u uw telefoon automatisch wilt configureren, moet u deze terugzetten naar de fabrieksinstellingen.
<br>Als de telefoon voor de eerste keer verbinding maakt met de PBX, wordt deze geregistreerd bij de "<b>apv-miko-pbx</b>"-account.
<br>Om de telefoon te configureren, moet u vanaf de telefoon "<b>%extension%</b>" bellen, waarbij XXX het interne nummer op de PBX is.
<br><br>
Automatische configuratie is alleen mogelijk voor toestellen in hetzelfde lokale netwerk, voor telefoons van <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Upload firmware-bestanden die de PBX via HTTP vanaf de provisioning-poort aan telefoons levert. Gebruik in uw sjablonen de placeholder <b>{FIRMWARE_URL}</b> om de download-URL in te voegen.',
    'mod_Autoprovision_firmware_drop_hint' => 'Sleep hier een firmware-bestand of klik om te kiezen',
    'mod_Autoprovision_firmware_browse' => 'Kies bestand',
    'mod_Autoprovision_firmware_vendor' => 'Fabrikant',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Versie',
    'mod_Autoprovision_firmware_notes' => 'Notities',
    'mod_Autoprovision_firmware_filename' => 'Bestand',
    'mod_Autoprovision_firmware_size' => 'Grootte',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Firmware-metadata bewerken',
    'mod_Autoprovision_firmware_save' => 'Opslaan',
    'mod_Autoprovision_firmware_cancel' => 'Annuleren',
];
