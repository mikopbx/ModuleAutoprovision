<?php
return [
    'mod_Autoprovision_additional_params' => 'Yderligere muligheder',
    'mod_Autoprovision_mac_white' => 'Telefon MAC-adresse hvidliste',
    'mod_Autoprovision_mac_black' => 'Sortliste over MAC-adresser på telefoner',
    'mod_Autoprovision_Extension' => 'Skabelon til lokalnummer',
    'BreadcrumbModuleAutoprovision' => 'Automatisk telefonopsætningsmodul',
    'mo_ModuleAutoprovision' => 'Automatisk telefonopsætningsmodul',
    /**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
    'repModuleAutoprovision' => 'Modul - % repræsenterer %',
    'mod_Autoprovision_pbx_host' => 'Serveradresse til telefonregistrering',
    'SubHeaderModuleAutoprovision' => 'Hjælp til opsætning af SIP-telefoner',
    'mod_Autoprovision_header' => 'Hvis modulet er aktiveret, bliver SIP-kontoen "<b>apv-miko-pbx</b>" tilgængelig på PBX\'en.
<br>For automatisk at konfigurere din telefon skal du nulstille den til fabriksindstillingerne.
<br>Hvis telefonen opretter forbindelse til PBX\'en for første gang, vil den blive registreret på "<b>apv-miko-pbx</b>"-kontoen.
<br>For at konfigurere telefonen skal du ringe til "<b>%extension%</b>" fra den, hvor XXX er det interne nummer på PBX\'en.
<br><br>
Autokonfiguration er kun mulig for virksomhedens lokale netværk, for telefoner <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_pnp' => 'PnP-indstillinger',
    'mod_Autoprovision_addNew' => 'Tilføje',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Prøve',
    'mod_Autoprovision_phone_settings_user' => 'Medarbejder',
    'mod_Autoprovision_phone_settings_mac' => 'Mac-adresse',
    'mod_Autoprovision_template_name' => 'Navn',
    'mod_Autoprovision_phone_settings_title' => 'Telefonindstillinger',
    'mod_Autoprovision_phone_templates' => 'Indstillingsskabeloner',
    'mod_Autoprovision_general_settings' => 'URI-indstillinger',
    'mod_Autoprovision_search_tags' => 'Søg...',
    'mod_Autoprovision_edit_template' => 'Redigering af en skabelon',
    'mod_Autoprovision_end_edit_template' => 'Afslut redigeringen',
    'mod_Autoprovision_other_pbx' => 'Telefonbog',
    'mod_Autoprovision_other_pbx_name' => 'Navn på telefoncentralen',
    'mod_Autoprovision_other_pbx_address' => 'PBX netværksadresse',
    'mod_Autoprovision_templates_header' => 'Når du beskriver en skabelon, kan du bruge følgende parametre: <b>{SIP_USER_NAME}</b> - medarbejdernavn <b>{SIP_NUM}</b> - internt nummer (login) <b>{SIP_PASS}</b> - adgangskode',
    'mod_Autoprovision_templates_users_header' => 'Når du beskriver en MAC-adresse, er det tilladt at bruge symbolet <b>%</b> - hvilket betyder "ethvert sæt af tegn" <br>
Skabelonen <b>805e0c67%</b> vil matche <b>805e0c670001</b> og <b>805e0c670002</b>',
];
