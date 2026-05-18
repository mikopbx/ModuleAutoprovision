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
    'fw_moduleautoprovisionDescription' => 'Autoprovision - livrarea configurației telefoanelor prin HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Port TCP dedicat servit de ModuleAutoprovision prin HTTP simplu (fără redirecționare HTTPS).<br>Telefoanele IP descarcă fișierele lor de configurare de pe acest port în timpul provisionării.<br>Deschideți accesul doar pentru rețeaua locală în care se află telefoanele.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — Server TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 servit de serverul TFTP integrat în modul, scris în PHP pur.<br>Folosit de telefoane și firmware-uri care preferă DHCP option 66 (TFTP) în detrimentul PnP multicast — istoric Snom, unele firmware-uri Fanvil, precum și rețele rutate în care multicast-ul nu trece granița L3.<br><b>Protocol în text clar:</b> deschideți portul doar în segmentul LAN unde se află telefoanele.',
    'mo_ModuleAutoprovision' => 'Modul de configurare automată a telefonului',
    'BreadcrumbModuleAutoprovision' => 'Modul de configurare automată a telefonului',
    'SubHeaderModuleAutoprovision' => 'Ajutor la configurarea telefoanelor SIP',
    'mod_Autoprovision_Extension' => 'Șablon de extensie',
    'mod_Autoprovision_pbx_host' => 'Adresa serverului pentru înregistrarea telefonului',
    'mod_Autoprovision_http_port' => 'Port HTTP pentru autoprovisioning',
    'mod_Autoprovision_http_port_hint' => 'Port TCP dedicat al modulului pentru livrarea configurațiilor prin HTTP pur — nu este afectat de redirecționarea globală către HTTPS. Telefoanele trebuie să contacteze PBX-ul pe acest port; regula de firewall este adăugată automat.',
    'mod_Autoprovision_mac_black' => 'Lista neagră MAC a telefonului',
    'mod_Autoprovision_mac_white' => 'Lista albă cu adrese MAC ale telefonului',
    'mod_Autoprovision_additional_params' => 'Opțiuni suplimentare',
    'mod_Autoprovision_tftp_enabled' => 'Activează provisioning prin TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Pornește un server TFTP în PHP pur pe UDP/69 care livrează aceleași configurații per-MAC ca și canalul HTTP, precum și firmware-uri din fila «Firmware».<br>Util atunci când PnP multicast este blocat (tipic în WiFi de birou și rețele rutate) sau când telefonul preferă DHCP option 66 (Snom, unele firmware-uri Fanvil).<br>Fără binare externe — rulează în interiorul workerului modulului. <b>Protocol în text clar</b>: activați doar într-un LAN de încredere. Regula de firewall pentru UDP/69 se deschide automat.',
    'mod_Autoprovision_phone_settings_title' => 'Setările telefonului',
    'mod_Autoprovision_phone_templates' => 'Șabloane de setări',
    'mod_Autoprovision_general_settings' => 'Setări URI',
    'mod_Autoprovision_pnp' => 'Setări PnP',
    'mod_Autoprovision_addNew' => 'Adăuga',
    'mod_Autoprovision_load_examples' => 'Încarcă șabloane exemplu',
    'mod_Autoprovision_load_examples_hint' => 'Adaugă șabloanele exemplu incluse pentru Yealink, Fanvil, Snom, Grandstream și Htek. Șabloanele cu nume deja existente sunt sărite, deci butonul poate fi apăsat de mai multe ori fără riscul de duplicate.',
    'mod_Autoprovision_load_examples_installed' => 'Șabloane exemplu instalate',
    'mod_Autoprovision_load_examples_already_present' => 'Toate șabloanele exemplu sunt deja instalate.',
    'mod_Autoprovision_load_examples_failed' => 'Nu s-au putut instala șabloanele exemplu',
    'mod_Autoprovision_load_examples_partial' => 'O parte din șabloanele exemplu nu au fost instalate',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'În pagină există modificări nesalvate. Încărcarea exemplelor va reîncărca pagina și le va anula. Continuați?',
    'mod_Autoprovision_load_examples_post_only' => 'Este necesară o cerere POST.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Probă',
    'mod_Autoprovision_phone_settings_user' => 'Angajat',
    'mod_Autoprovision_phone_settings_mac' => 'Adresa mac',
    'mod_Autoprovision_template_name' => 'Nume',
    'mod_Autoprovision_search_tags' => 'Căutare...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Editarea unui șablon',
    'mod_Autoprovision_end_edit_template' => 'Terminați editarea',
    'mod_Autoprovision_other_pbx' => 'Carte de telefoane',
    'mod_Autoprovision_other_pbx_name' => 'Numele centrală telefonică',
    'mod_Autoprovision_other_pbx_address' => 'adresa rețelei PBX',
    'mod_Autoprovision_templates_header' => 'Când descrieți un șablon, puteți utiliza următorii parametri: <b>{SIP_USER_NAME}</b> - numele angajatului <b>{SIP_NUM}</b> - număr intern (autentificare) <b>{SIP_PASS}</b> - parolă',
    'mod_Autoprovision_other_pbx_header' => '<b>Atenție!</b> Agenda telefonică trebuie să fie accesibilă pe fiecare PBX la URI-ul <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Listați toate adresele PBX-urilor de la care trebuie preluată agenda telefonică.<br>',
    'mod_Autoprovision_templates_users_header' => 'Când descrieți o adresă MAC, este permisă utilizarea simbolului <b>%</b> - adică „orice set de caractere” <br>
Șablonul <b>805e0c67%</b> se va potrivi cu <b>805e0c670001</b> și <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Atenție!</b> Toate URI-urile sunt construite relativ la valoarea de bază <b>/pbxcore/api/autoprovision-http</b><br>La descrierea unui URI se poate folosi simbolul <b>%</b> — însemnând „orice set de caractere“.<br>URI-ul <b>/%/%/test.cfg</b> va corespunde cu <b>/1/2/test.cfg</b> și cu <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Dacă modulul este activat, contul SIP „<b>apv-miko-pbx</b>” devine disponibil pe PBX.
<br>Pentru a configura automat telefonul, trebuie să-l resetați la setările din fabrică.
<br>Dacă telefonul se conectează la PBX pentru prima dată, acesta va fi înregistrat în contul „<b>apv-miko-pbx</b>”.
<br>Pentru a configura telefonul, trebuie să apelați „<b>%extension%</b>” de la acesta, unde XXX este numărul intern al PBX-ului.
<br><br>
Autoconfigurarea este posibilă numai pentru rețeaua locală a întreprinderii, pentru telefoanele <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Încărcați fișiere de firmware pe care PBX-ul le va livra telefoanelor prin HTTP de pe portul de provisioning. Folosiți în șabloane substituentul <b>{FIRMWARE_URL}</b> pentru a insera URL-ul de descărcare.',
    'mod_Autoprovision_firmware_drop_hint' => 'Trageți aici un fișier de firmware sau dați clic pentru a alege',
    'mod_Autoprovision_firmware_browse' => 'Alege fișier',
    'mod_Autoprovision_firmware_vendor' => 'Producător',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Versiune',
    'mod_Autoprovision_firmware_notes' => 'Note',
    'mod_Autoprovision_firmware_filename' => 'Fișier',
    'mod_Autoprovision_firmware_size' => 'Dimensiune',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Editare metadate firmware',
    'mod_Autoprovision_firmware_save' => 'Salvează',
    'mod_Autoprovision_firmware_cancel' => 'Anulează',
];
