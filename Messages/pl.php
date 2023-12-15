<?php
return [
    'mod_Autoprovision_additional_params' => 'Dodatkowe opcje',
    'mod_Autoprovision_mac_white' => 'Biała lista adresów MAC telefonu',
    'mod_Autoprovision_mac_black' => 'Czarna lista adresów MAC telefonów',
    'mod_Autoprovision_pbx_host' => 'Adres serwera do rejestracji telefonu',
    'mod_Autoprovision_Extension' => 'Szablon numeru wewnętrznego',
    'SubHeaderModuleAutoprovision' => 'Pomoc w konfiguracji telefonów SIP',
    'BreadcrumbModuleAutoprovision' => 'Moduł automatycznej konfiguracji telefonu',
    'mo_ModuleAutoprovision' => 'Moduł automatycznej konfiguracji telefonu',
    /**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
    'repModuleAutoprovision' => 'Moduł -% reprezentatywnych%',
    'mod_Autoprovision_header' => 'Jeżeli moduł jest włączony, w centrali staje się dostępne konto SIP „<b>apv-miko-pbx</b>”.
<br>Aby automatycznie skonfigurować telefon, musisz zresetować go do ustawień fabrycznych.
<br>Jeśli telefon połączy się z centralą po raz pierwszy, zostanie zarejestrowany na koncie „<b>apv-miko-pbx</b>”.
<br>Aby skonfigurować telefon należy wywołać z niego "<b>%extension%</b>", gdzie XXX to numer wewnętrzny centrali.
<br><br>
Autokonfiguracja możliwa jest tylko dla sieci lokalnej przedsiębiorstwa, dla telefonów <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_phone_settings_title' => 'Ustawienia telefonu',
    'mod_Autoprovision_phone_templates' => 'Szablony ustawień',
    'mod_Autoprovision_general_settings' => 'Ustawienia URI',
    'mod_Autoprovision_pnp' => 'Ustawienia PnP',
    'mod_Autoprovision_addNew' => 'Dodać',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Próbka',
    'mod_Autoprovision_phone_settings_user' => 'Pracownik',
    'mod_Autoprovision_phone_settings_mac' => 'Adres MAC',
    'mod_Autoprovision_template_name' => 'Nazwa',
    'mod_Autoprovision_search_tags' => 'Szukaj...',
    'mod_Autoprovision_edit_template' => 'Edycja szablonu',
    'mod_Autoprovision_end_edit_template' => 'Zakończ edycję',
    'mod_Autoprovision_other_pbx' => 'Książka telefoniczna',
    'mod_Autoprovision_other_pbx_name' => 'Nazwa centrali telefonicznej',
    'mod_Autoprovision_other_pbx_address' => 'Adres sieci centrali PBX',
    'mod_Autoprovision_templates_header' => 'Opisując szablon możesz wykorzystać następujące parametry: <b>{SIP_USER_NAME}</b> - imię i nazwisko pracownika <b>{SIP_NUM}</b> - numer wewnętrzny (login) <b>{SIP_PASS}</b> - hasło',
    'mod_Autoprovision_templates_users_header' => 'Przy opisie adresu MAC dopuszczalne jest użycie symbolu <b>%</b> - oznaczającego „dowolny zestaw znaków” <br>
Szablon <b>805e0c67%</b> będzie pasował do szablonów <b>805e0c670001</b> i <b>805e0c670002</b>',
];
