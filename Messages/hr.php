<?php
return [
    'mod_Autoprovision_other_pbx_address' => 'PBX mrežna adresa',
    /*
 * MikoPBX - free phone system for small business
 * Copyright © 2017-2023 Alexey Portnov and Nikolay Beketov
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
    'repModuleAutoprovision' => 'Modul - %repesent%',
    'mo_ModuleAutoprovision' => 'Modul za automatsku konfiguraciju telefona',
    'BreadcrumbModuleAutoprovision' => 'Modul za automatsku konfiguraciju telefona',
    'SubHeaderModuleAutoprovision' => 'Pomoć pri postavljanju SIP telefona',
    'mod_Autoprovision_Extension' => 'Predložak kućnog broja',
    'mod_Autoprovision_pbx_host' => 'Adresa poslužitelja za registraciju telefona',
    'mod_Autoprovision_mac_black' => 'Crna lista telefonskih MAC adresa',
    'mod_Autoprovision_mac_white' => 'Bijela lista telefonskih MAC adresa',
    'mod_Autoprovision_additional_params' => 'Dodatne mogućnosti',
    'mod_Autoprovision_phone_settings_title' => 'Postavke telefona',
    'mod_Autoprovision_phone_templates' => 'Predlošci postavki',
    'mod_Autoprovision_general_settings' => 'URI postavke',
    'mod_Autoprovision_pnp' => 'PnP postavke',
    'mod_Autoprovision_addNew' => 'Dodati',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Uzorak',
    'mod_Autoprovision_phone_settings_user' => 'Zaposlenik',
    'mod_Autoprovision_phone_settings_mac' => 'MAC adresa',
    'mod_Autoprovision_template_name' => 'Ime',
    'mod_Autoprovision_search_tags' => 'Pretraživanje...',
    'mod_Autoprovision_edit_template' => 'Uređivanje predloška',
    'mod_Autoprovision_end_edit_template' => 'Završite uređivanje',
    'mod_Autoprovision_other_pbx' => 'telefonski imenik',
    'mod_Autoprovision_other_pbx_name' => 'Naziv telefonske centrale',
    'mod_Autoprovision_templates_header' => 'Kada opisujete predložak, možete koristiti sljedeće parametre: <b>{SIP_USER_NAME}</b> - ime zaposlenika <b>{SIP_NUM}</b> - dodatni broj (prijava) <b>{SIP_PASS}</b> - lozinka',
    'mod_Autoprovision_templates_users_header' => 'Kada se opisuje MAC adresa, dopušteno je koristiti simbol <b>%</b> - što znači "bilo koji skup znakova" <br>
Uzorak <b>805e0c67%</b> odgovarat će <b>805e0c670001</b> i <b>805e0c670002</b>',
    'mod_Autoprovision_header' => 'Ako je modul omogućen, SIP račun "<b>apv-miko-pbx</b>" postaje dostupan na PBX-u
<br>Da biste automatski konfigurirali svoj telefon, morate ga vratiti na tvorničke postavke.
<br>Ako se telefon prvi put spaja na PBX, bit će registriran na računu "<b>apv-miko-pbx</b>".
<br>Da biste postavili svoj telefon, morate s njega nazvati broj “<b>%extension%</b>”, gdje je XXX interni broj na PBX-u.
<br><br>
Automatska konfiguracija moguća je samo za lokalnu mrežu poduzeća, za telefone <b>Yealink, Snom, Fanvil</b>.',
];
