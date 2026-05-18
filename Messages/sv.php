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
    'repModuleAutoprovision' => 'Modul -% representerar%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - leverans av telefonkonfiguration via HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Dedikerad TCP-port som ModuleAutoprovision tillhandahåller över vanlig HTTP (ingen HTTPS-omdirigering).<br>IP-telefoner hämtar sina konfigurationsfiler från denna port under provisioneringen.<br>Öppna åtkomsten endast för det lokala nätverk där telefonerna finns.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP-server (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 betjänas av den inbyggda TFTP-servern skriven i ren PHP.<br>Används av telefoner och firmware som föredrar DHCP option 66 (TFTP) framför multicast PnP — historiskt Snom, vissa Fanvil-firmware och routade nätverk där multicast inte korsar L3-gränsen.<br><b>Klartextprotokoll:</b> öppna porten endast i det LAN-segment där telefonerna finns.',
    'mo_ModuleAutoprovision' => 'Automatisk telefoninställningsmodul',
    'BreadcrumbModuleAutoprovision' => 'Automatisk telefoninställningsmodul',
    'SubHeaderModuleAutoprovision' => 'Hjälp med att ställa in SIP-telefoner',
    'mod_Autoprovision_Extension' => 'Mall för anknytningsnummer',
    'mod_Autoprovision_pbx_host' => 'Serveradress för telefonregistrering',
    'mod_Autoprovision_http_port' => 'HTTP-port för autoprovisionering',
    'mod_Autoprovision_http_port_hint' => 'Modulens dedikerade TCP-port för leverans av konfigurationer över ren HTTP — omfattas inte av den globala HTTPS-omdirigeringen. Telefonerna måste nå PBX:en på denna port; brandväggsregel läggs till automatiskt.',
    'mod_Autoprovision_mac_black' => 'Svartlista över MAC-adresser för telefoner',
    'mod_Autoprovision_mac_white' => 'Vitlista för telefon MAC-adresser',
    'mod_Autoprovision_additional_params' => 'Ytterligare alternativ',
    'mod_Autoprovision_tftp_enabled' => 'Aktivera TFTP-provisionering (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Startar en TFTP-server i ren PHP på UDP/69 som levererar samma per-MAC-konfigurationer som HTTP-kanalen, plus firmware från fliken «Firmware».<br>Användbart när multicast PnP är blockerat (typiskt i kontors-WiFi och routade nätverk) eller när telefonen föredrar DHCP option 66 (Snom, vissa Fanvil-firmware).<br>Inga externa binärer — körs inuti modulens worker. <b>Klartextprotokoll</b>: aktivera endast i ett betrott LAN. Brandväggsregel för UDP/69 öppnas automatiskt.',
    'mod_Autoprovision_phone_settings_title' => 'Telefon inställningar',
    'mod_Autoprovision_phone_templates' => 'Inställningar mallar',
    'mod_Autoprovision_general_settings' => 'URI-inställningar',
    'mod_Autoprovision_pnp' => 'PnP-inställningar',
    'mod_Autoprovision_addNew' => 'Lägg till',
    'mod_Autoprovision_load_examples' => 'Ladda exempelmallar',
    'mod_Autoprovision_load_examples_hint' => 'Lägger till de medföljande exempelmallarna för Yealink, Fanvil, Snom, Grandstream och Htek. Mallar med redan befintliga namn hoppas över, så knappen kan tryckas flera gånger utan risk för dubbletter.',
    'mod_Autoprovision_load_examples_installed' => 'Exempelmallar installerade',
    'mod_Autoprovision_load_examples_already_present' => 'Alla exempelmallar är redan installerade.',
    'mod_Autoprovision_load_examples_failed' => 'Det gick inte att installera exempelmallarna',
    'mod_Autoprovision_load_examples_partial' => 'Vissa exempelmallar installerades inte',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Det finns osparade ändringar på sidan. Att ladda exemplen kommer att läsa in sidan på nytt och kasta dem. Fortsätta?',
    'mod_Autoprovision_load_examples_post_only' => 'POST-begäran krävs.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Prov',
    'mod_Autoprovision_phone_settings_user' => 'Anställd',
    'mod_Autoprovision_phone_settings_mac' => 'MAC-adress',
    'mod_Autoprovision_template_name' => 'namn',
    'mod_Autoprovision_search_tags' => 'Sök...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Redigera en mall',
    'mod_Autoprovision_end_edit_template' => 'Slutför redigeringen',
    'mod_Autoprovision_other_pbx' => 'Telefonbok',
    'mod_Autoprovision_other_pbx_name' => 'Telefonväxelns namn',
    'mod_Autoprovision_other_pbx_address' => 'PBX nätverksadress',
    'mod_Autoprovision_templates_header' => 'När du beskriver en mall kan du använda följande parametrar: <b>{SIP_USER_NAME}</b> - anställds namn <b>{SIP_NUM}</b> - internt nummer (inloggning) <b>{SIP_PASS}</b> - lösenord',
    'mod_Autoprovision_other_pbx_header' => '<b>Obs!</b> Telefonboken måste vara tillgänglig på varje PBX på URI:n <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Lista alla adresser till de PBX:er från vilka telefonboken ska hämtas.<br>',
    'mod_Autoprovision_templates_users_header' => 'När du beskriver en MAC-adress är det tillåtet att använda symbolen <b>%</b> - vilket betyder "alla teckenuppsättningar" <br>
Mallen <b>805e0c67%</b> matchar <b>805e0c670001</b> och <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Obs!</b> Alla URI:er byggs i förhållande till basvärdet <b>/pbxcore/api/autoprovision-http</b><br>Vid beskrivning av en URI får symbolen <b>%</b> användas — vilket betyder „valfri teckenuppsättning“.<br>URI:n <b>/%/%/test.cfg</b> matchar <b>/1/2/test.cfg</b> och <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Om modulen är aktiverad blir SIP-kontot "<b>apv-miko-pbx</b>" tillgängligt på telefonväxeln.
<br>För att konfigurera din telefon automatiskt måste du återställa den till fabriksinställningarna.
<br>Om telefonen ansluter till telefonväxeln för första gången kommer den att registreras på "<b>apv-miko-pbx</b>"-kontot.
<br>För att konfigurera telefonen måste du ringa "<b>%extension%</b>" från den, där XXX är det interna numret på telefonväxeln.
<br><br>
Autokonfiguration är endast möjlig för företagets lokala nätverk, för telefoner <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Ladda upp firmware-filer som PBX:en levererar till telefonerna via HTTP från provisioneringsporten. Använd platshållaren <b>{FIRMWARE_URL}</b> i dina mallar för att infoga nedladdningens URL.',
    'mod_Autoprovision_firmware_drop_hint' => 'Dra en firmware-fil hit eller klicka för att välja',
    'mod_Autoprovision_firmware_browse' => 'Välj fil',
    'mod_Autoprovision_firmware_vendor' => 'Tillverkare',
    'mod_Autoprovision_firmware_model' => 'Modell',
    'mod_Autoprovision_firmware_version' => 'Version',
    'mod_Autoprovision_firmware_notes' => 'Anteckningar',
    'mod_Autoprovision_firmware_filename' => 'Fil',
    'mod_Autoprovision_firmware_size' => 'Storlek',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Redigera firmware-metadata',
    'mod_Autoprovision_firmware_save' => 'Spara',
    'mod_Autoprovision_firmware_cancel' => 'Avbryt',
];
