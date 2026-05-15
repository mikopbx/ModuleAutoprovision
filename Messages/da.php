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
    'repModuleAutoprovision' => 'Modul - % repræsenterer %',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - levering af telefonkonfiguration over HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Dedikeret TCP-port, som ModuleAutoprovision serverer over almindelig HTTP (ingen HTTPS-omdirigering).<br>IP-telefoner henter deres konfigurationsfiler fra denne port under provisionering.<br>Åbn kun adgang for det lokale netværk, hvor telefonerne befinder sig.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP-server (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 betjenes af den indbyggede TFTP-server skrevet i ren PHP.<br>Bruges af telefoner og firmware, der foretrækker DHCP option 66 (TFTP) frem for multicast PnP — historisk Snom, nogle Fanvil-firmwares samt routede netværk, hvor multicast ikke krydser L3-grænsen.<br><b>Klartekstprotokol:</b> åbn kun porten i det LAN-segment, hvor telefonerne sidder.',
    'mo_ModuleAutoprovision' => 'Automatisk telefonopsætningsmodul',
    'BreadcrumbModuleAutoprovision' => 'Automatisk telefonopsætningsmodul',
    'SubHeaderModuleAutoprovision' => 'Hjælp til opsætning af SIP-telefoner',
    'mod_Autoprovision_Extension' => 'Skabelon til lokalnummer',
    'mod_Autoprovision_pbx_host' => 'Serveradresse til telefonregistrering',
    'mod_Autoprovision_http_port' => 'HTTP-port til autoprovisionering',
    'mod_Autoprovision_http_port_hint' => 'Dedikeret TCP-port til modulet, der leverer konfigurationer over ren HTTP — er ikke underlagt den globale HTTPS-redirect. Telefoner skal kontakte PBX\'en på denne port; firewall-regel tilføjes automatisk.',
    'mod_Autoprovision_mac_black' => 'Sortliste over MAC-adresser på telefoner',
    'mod_Autoprovision_mac_white' => 'Telefon MAC-adresse hvidliste',
    'mod_Autoprovision_additional_params' => 'Yderligere muligheder',
    'mod_Autoprovision_tftp_enabled' => 'Aktivér TFTP-provisionering (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Starter den indbyggede TFTP-server i ren PHP (UDP/69), som leverer de samme per-MAC-konfigurationer som HTTP-kanalen samt firmwarefiler fra «Firmware»-fanen.<br>Praktisk når multicast PnP er blokeret (typisk i kontor-WiFi og routede netværk) eller telefonen foretrækker DHCP option 66 (Snom, nogle Fanvil-firmwares).<br>Ingen eksterne binaries — kører inde i modulets worker. <b>Klartekstprotokol</b>: aktivér kun i et betroet LAN. Firewall-regel for UDP/69 åbnes automatisk.',
    'mod_Autoprovision_phone_settings_title' => 'Telefonindstillinger',
    'mod_Autoprovision_phone_templates' => 'Indstillingsskabeloner',
    'mod_Autoprovision_general_settings' => 'URI-indstillinger',
    'mod_Autoprovision_pnp' => 'PnP-indstillinger',
    'mod_Autoprovision_addNew' => 'Tilføje',
    'mod_Autoprovision_load_examples' => 'Indlæs eksempelskabeloner',
    'mod_Autoprovision_load_examples_hint' => 'Tilføjer de medfølgende eksempelskabeloner til Yealink, Fanvil, Snom, Grandstream og Htek. Skabeloner med navne, der allerede findes, springes over, så knappen kan trykkes flere gange uden risiko for dubletter.',
    'mod_Autoprovision_load_examples_installed' => 'Eksempelskabeloner installeret',
    'mod_Autoprovision_load_examples_already_present' => 'Alle eksempelskabeloner er allerede installeret.',
    'mod_Autoprovision_load_examples_failed' => 'Kunne ikke installere eksempelskabeloner',
    'mod_Autoprovision_load_examples_partial' => 'Nogle eksempelskabeloner blev ikke installeret',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Der er ugemte ændringer på siden. Indlæsning af eksempler genindlæser siden og kasserer dem. Fortsæt?',
    'mod_Autoprovision_load_examples_post_only' => 'POST-anmodning kræves.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Prøve',
    'mod_Autoprovision_phone_settings_user' => 'Medarbejder',
    'mod_Autoprovision_phone_settings_mac' => 'Mac-adresse',
    'mod_Autoprovision_template_name' => 'Navn',
    'mod_Autoprovision_search_tags' => 'Søg...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Redigering af en skabelon',
    'mod_Autoprovision_end_edit_template' => 'Afslut redigeringen',
    'mod_Autoprovision_other_pbx' => 'Telefonbog',
    'mod_Autoprovision_other_pbx_name' => 'Navn på telefoncentralen',
    'mod_Autoprovision_other_pbx_address' => 'PBX netværksadresse',
    'mod_Autoprovision_templates_header' => 'Når du beskriver en skabelon, kan du bruge følgende parametre: <b>{SIP_USER_NAME}</b> - medarbejdernavn <b>{SIP_NUM}</b> - internt nummer (login) <b>{SIP_PASS}</b> - adgangskode',
    'mod_Autoprovision_other_pbx_header' => '<b>Bemærk!</b> Telefonbogen skal være tilgængelig på hver PBX via URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Angiv alle adresser på de PBX\'er, som telefonbogen skal hentes fra.<br>',
    'mod_Autoprovision_templates_users_header' => 'Når du beskriver en MAC-adresse, er det tilladt at bruge symbolet <b>%</b> - hvilket betyder "ethvert sæt af tegn" <br>
Skabelonen <b>805e0c67%</b> vil matche <b>805e0c670001</b> og <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Bemærk!</b> Alle URI\'er bygges relativt til basisværdien <b>/pbxcore/api/autoprovision-http</b><br>Når du beskriver en URI, kan symbolet <b>%</b> bruges — det betyder „enhver sekvens af tegn“.<br>URI\'en <b>/%/%/test.cfg</b> matcher <b>/1/2/test.cfg</b> og <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Hvis modulet er aktiveret, bliver SIP-kontoen "<b>apv-miko-pbx</b>" tilgængelig på PBX\'en.
<br>For automatisk at konfigurere din telefon skal du nulstille den til fabriksindstillingerne.
<br>Hvis telefonen opretter forbindelse til PBX\'en for første gang, vil den blive registreret på "<b>apv-miko-pbx</b>"-kontoen.
<br>For at konfigurere telefonen skal du ringe til "<b>%extension%</b>" fra den, hvor XXX er det interne nummer på PBX\'en.
<br><br>
Autokonfiguration er kun mulig for virksomhedens lokale netværk, for telefoner <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Upload firmwarefiler, som PBX\'en vil sende til telefoner over HTTP fra provisioneringsporten. Brug pladsholderen <b>{FIRMWARE_URL}</b> i dine skabeloner til at indsætte download-URL\'en.',
    'mod_Autoprovision_firmware_drop_hint' => 'Træk en firmwarefil hertil, eller klik for at vælge',
    'mod_Autoprovision_firmware_browse' => 'Vælg fil',
    'mod_Autoprovision_firmware_vendor' => 'Producent',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Version',
    'mod_Autoprovision_firmware_notes' => 'Noter',
    'mod_Autoprovision_firmware_filename' => 'Fil',
    'mod_Autoprovision_firmware_size' => 'Størrelse',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Redigér firmware-metadata',
    'mod_Autoprovision_firmware_save' => 'Gem',
    'mod_Autoprovision_firmware_cancel' => 'Annullér',
];
