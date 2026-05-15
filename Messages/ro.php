<?php
return [
    /*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */
    'mo_ModuleAutoprovision' => 'Modul de configurare automată a telefonului',
    'BreadcrumbModuleAutoprovision' => 'Modul de configurare automată a telefonului',
    'SubHeaderModuleAutoprovision' => 'Ajutor la configurarea telefoanelor SIP',
    'repModuleAutoprovision' => 'Modul - %represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - livrarea configurației telefoanelor prin HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Port TCP dedicat servit de ModuleAutoprovision prin HTTP simplu (fără redirecționare HTTPS).<br>Telefoanele IP descarcă fișierele lor de configurare de pe acest port în timpul provisionării.<br>Deschideți accesul doar pentru rețeaua locală în care se află telefoanele.',
    'mod_Autoprovision_Extension' => 'Șablon de extensie',
    'mod_Autoprovision_pbx_host' => 'Adresa serverului pentru înregistrarea telefonului',
    'mod_Autoprovision_mac_black' => 'Lista neagră MAC a telefonului',
    'mod_Autoprovision_mac_white' => 'Lista albă cu adrese MAC ale telefonului',
    'mod_Autoprovision_additional_params' => 'Opțiuni suplimentare',
    'mod_Autoprovision_header' => 'Dacă modulul este activat, contul SIP „<b>apv-miko-pbx</b>” devine disponibil pe PBX.
<br>Pentru a configura automat telefonul, trebuie să-l resetați la setările din fabrică.
<br>Dacă telefonul se conectează la PBX pentru prima dată, acesta va fi înregistrat în contul „<b>apv-miko-pbx</b>”.
<br>Pentru a configura telefonul, trebuie să apelați „<b>%extension%</b>” de la acesta, unde XXX este numărul intern al PBX-ului.
<br><br>
Autoconfigurarea este posibilă numai pentru rețeaua locală a întreprinderii, pentru telefoanele <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_phone_settings_title' => 'Setările telefonului',
    'mod_Autoprovision_phone_templates' => 'Șabloane de setări',
    'mod_Autoprovision_general_settings' => 'Setări URI',
    'mod_Autoprovision_pnp' => 'Setări PnP',
    'mod_Autoprovision_addNew' => 'Adăuga',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Probă',
    'mod_Autoprovision_phone_settings_user' => 'Angajat',
    'mod_Autoprovision_phone_settings_mac' => 'Adresa mac',
    'mod_Autoprovision_template_name' => 'Nume',
    'mod_Autoprovision_search_tags' => 'Căutare...',
    'mod_Autoprovision_edit_template' => 'Editarea unui șablon',
    'mod_Autoprovision_end_edit_template' => 'Terminați editarea',
    'mod_Autoprovision_other_pbx' => 'Carte de telefoane',
    'mod_Autoprovision_other_pbx_name' => 'Numele centrală telefonică',
    'mod_Autoprovision_other_pbx_address' => 'adresa rețelei PBX',
    'mod_Autoprovision_templates_header' => 'Când descrieți un șablon, puteți utiliza următorii parametri: <b>{SIP_USER_NAME}</b> - numele angajatului <b>{SIP_NUM}</b> - număr intern (autentificare) <b>{SIP_PASS}</b> - parolă',
    'mod_Autoprovision_templates_users_header' => 'Când descrieți o adresă MAC, este permisă utilizarea simbolului <b>%</b> - adică „orice set de caractere” <br>
Șablonul <b>805e0c67%</b> se va potrivi cu <b>805e0c670001</b> și <b>805e0c670002</b>',
    'mod_Autoprovision_filter_posts' => 'Select…',
];
