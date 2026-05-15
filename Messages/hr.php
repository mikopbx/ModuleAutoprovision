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
    'fw_moduleautoprovisionDescription' => 'Autoprovision - isporuka konfiguracije telefona putem HTTP-a',
    'fw_moduleautoprovisionDescriptionHint' => 'Namjenski TCP port koji ModuleAutoprovision poslužuje preko čistog HTTP-a (bez preusmjeravanja na HTTPS).<br>IP telefoni preuzimaju svoje konfiguracijske datoteke s ovog porta tijekom automatske konfiguracije.<br>Pristup otvorite samo lokalnoj mreži u kojoj se nalaze telefoni.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP poslužitelj (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 poslužuje ugrađeni TFTP poslužitelj u čistom PHP-u.<br>Koriste ga telefoni i firmware koji preferiraju DHCP option 66 (TFTP) ispred multicast PnP-a — povijesno Snom, neki firmware Fanvil, kao i usmjeravane mreže gdje multicast ne prelazi L3 granicu.<br><b>Otvoreni tekstualni protokol:</b> otvorite port samo u segmentu LAN-a u kojem se nalaze telefoni.',
    'mo_ModuleAutoprovision' => 'Modul za automatsku konfiguraciju telefona',
    'BreadcrumbModuleAutoprovision' => 'Modul za automatsku konfiguraciju telefona',
    'SubHeaderModuleAutoprovision' => 'Pomoć pri postavljanju SIP telefona',
    'mod_Autoprovision_Extension' => 'Predložak kućnog broja',
    'mod_Autoprovision_pbx_host' => 'Adresa poslužitelja za registraciju telefona',
    'mod_Autoprovision_http_port' => 'HTTP port autoprovisioninga',
    'mod_Autoprovision_http_port_hint' => 'Namjenski TCP port modula za isporuku konfiguracija putem čistog HTTP-a — ne podliježe globalnoj HTTPS preusmjeravi. Telefoni moraju doseći PBX na ovom portu; pravilo vatrozida dodaje se automatski.',
    'mod_Autoprovision_mac_black' => 'Crna lista telefonskih MAC adresa',
    'mod_Autoprovision_mac_white' => 'Bijela lista telefonskih MAC adresa',
    'mod_Autoprovision_additional_params' => 'Dodatne mogućnosti',
    'mod_Autoprovision_tftp_enabled' => 'Omogući TFTP provisioning (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Pokreće TFTP poslužitelj u čistom PHP-u na UDP/69 koji isporučuje iste konfiguracije po MAC-u kao HTTP kanal te firmware datoteke s kartice «Firmware».<br>Korisno kada je multicast PnP blokiran (tipično u uredskim WiFi i usmjeravanim mrežama) ili kada telefon preferira DHCP option 66 (Snom, neki firmware Fanvil).<br>Bez vanjskih binarnih datoteka — radi unutar workera modula. <b>Otvoreni tekstualni protokol</b>: omogućite samo u pouzdanom LAN-u. Pravilo vatrozida za UDP/69 otvara se automatski.',
    'mod_Autoprovision_phone_settings_title' => 'Postavke telefona',
    'mod_Autoprovision_phone_templates' => 'Predlošci postavki',
    'mod_Autoprovision_general_settings' => 'URI postavke',
    'mod_Autoprovision_pnp' => 'PnP postavke',
    'mod_Autoprovision_addNew' => 'Dodati',
    'mod_Autoprovision_load_examples' => 'Učitaj primjere predložaka',
    'mod_Autoprovision_load_examples_hint' => 'Dodaje ugrađene primjere predložaka za Yealink, Fanvil, Snom, Grandstream i Htek. Predlošci s već postojećim nazivima preskaču se, pa se gumb može pritisnuti više puta bez rizika od duplikata.',
    'mod_Autoprovision_load_examples_installed' => 'Primjeri predložaka instalirani',
    'mod_Autoprovision_load_examples_already_present' => 'Svi primjeri predložaka već su instalirani.',
    'mod_Autoprovision_load_examples_failed' => 'Instalacija primjera predložaka nije uspjela',
    'mod_Autoprovision_load_examples_partial' => 'Dio primjera predložaka nije instaliran',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Na stranici postoje nespremljene promjene. Učitavanje primjera ponovno će učitati stranicu i odbaciti ih. Nastaviti?',
    'mod_Autoprovision_load_examples_post_only' => 'Potreban je POST zahtjev.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Uzorak',
    'mod_Autoprovision_phone_settings_user' => 'Zaposlenik',
    'mod_Autoprovision_phone_settings_mac' => 'MAC adresa',
    'mod_Autoprovision_template_name' => 'Ime',
    'mod_Autoprovision_search_tags' => 'Pretraživanje...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Uređivanje predloška',
    'mod_Autoprovision_end_edit_template' => 'Završite uređivanje',
    'mod_Autoprovision_other_pbx' => 'telefonski imenik',
    'mod_Autoprovision_other_pbx_name' => 'Naziv telefonske centrale',
    'mod_Autoprovision_other_pbx_address' => 'PBX mrežna adresa',
    'mod_Autoprovision_templates_header' => 'Kada opisujete predložak, možete koristiti sljedeće parametre: <b>{SIP_USER_NAME}</b> - ime zaposlenika <b>{SIP_NUM}</b> - dodatni broj (prijava) <b>{SIP_PASS}</b> - lozinka',
    'mod_Autoprovision_other_pbx_header' => '<b>Pažnja!</b> Telefonski imenik mora biti dostupan na svakom PBX-u na URI-ju <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Navedite sve adrese PBX-ova s kojih je potrebno dohvatiti telefonski imenik.<br>',
    'mod_Autoprovision_templates_users_header' => 'Kada se opisuje MAC adresa, dopušteno je koristiti simbol <b>%</b> - što znači "bilo koji skup znakova" <br>
Uzorak <b>805e0c67%</b> odgovarat će <b>805e0c670001</b> i <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Pažnja!</b> Svi URI-ji grade se relativno prema osnovnoj vrijednosti <b>/pbxcore/api/autoprovision-http</b><br>Pri opisu URI-ja dopušteno je koristiti simbol <b>%</b> — što znači „bilo koji skup znakova“.<br>URI <b>/%/%/test.cfg</b> odgovarat će <b>/1/2/test.cfg</b> i <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Ako je modul omogućen, SIP račun "<b>apv-miko-pbx</b>" postaje dostupan na PBX-u
<br>Da biste automatski konfigurirali svoj telefon, morate ga vratiti na tvorničke postavke.
<br>Ako se telefon prvi put spaja na PBX, bit će registriran na računu "<b>apv-miko-pbx</b>".
<br>Da biste postavili svoj telefon, morate s njega nazvati broj “<b>%extension%</b>”, gdje je XXX interni broj na PBX-u.
<br><br>
Automatska konfiguracija moguća je samo za lokalnu mrežu poduzeća, za telefone <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Prenesite datoteke firmwarea koje će PBX isporučivati telefonima putem HTTP-a s provisioning porta. U predlošcima koristite oznaku <b>{FIRMWARE_URL}</b> za umetanje URL-a za preuzimanje.',
    'mod_Autoprovision_firmware_drop_hint' => 'Povucite datoteku firmwarea ovamo ili kliknite za odabir',
    'mod_Autoprovision_firmware_browse' => 'Odaberi datoteku',
    'mod_Autoprovision_firmware_vendor' => 'Proizvođač',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Verzija',
    'mod_Autoprovision_firmware_notes' => 'Bilješke',
    'mod_Autoprovision_firmware_filename' => 'Datoteka',
    'mod_Autoprovision_firmware_size' => 'Veličina',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Uređivanje metapodataka firmwarea',
    'mod_Autoprovision_firmware_save' => 'Spremi',
    'mod_Autoprovision_firmware_cancel' => 'Odustani',
];
