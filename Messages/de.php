<?php
return [
    'mod_Autoprovision_additional_params' => 'Erweiterte Einstellungen',
    'mod_Autoprovision_mac_white' => 'Whitelist der MAC-Adresse des Telefons',
    'mod_Autoprovision_mac_black' => 'Blacklist der MAC-Adressen von Telefonen',
    'mod_Autoprovision_pbx_host' => 'Serveradresse für die Telefonregistrierung',
    'mod_Autoprovision_Extension' => 'Nebenstellennummernvorlage',
    'SubHeaderModuleAutoprovision' => 'Hilfe beim Einrichten von SIP-Telefonen',
    'BreadcrumbModuleAutoprovision' => 'Automatisches Telefon-Setup-Modul',
    'mo_ModuleAutoprovision' => 'Automatisches Telefon-Setup-Modul',
    /**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
    'repModuleAutoprovision' => 'Modul -%repesent%',
    'mod_Autoprovision_header' => 'Wenn das Modul aktiviert ist, wird das SIP-Konto „<b>apv-miko-pbx</b>“ auf der TK-Anlage verfügbar.
<br>Um Ihr Telefon automatisch zu konfigurieren, müssen Sie es auf die Werkseinstellungen zurücksetzen.
<br>Wenn das Telefon zum ersten Mal eine Verbindung zur Telefonanlage herstellt, wird es im Konto „<b>apv-miko-pbx</b>“ registriert.
<br>Um das Telefon zu konfigurieren, müssen Sie von dort aus „<b>%extension%</b>“ anrufen, wobei XXX die interne Nummer der Telefonanlage ist.
<br><br>
Die automatische Konfiguration ist nur für das lokale Netzwerk des Unternehmens und für Telefone <b>Yealink, Snom, Fanvil</b> möglich.',
    'mod_Autoprovision_phone_settings_title' => 'Telefoneinstellungen',
    'mod_Autoprovision_phone_templates' => 'Einstellungsvorlagen',
    'mod_Autoprovision_general_settings' => 'URI-Einstellungen',
    'mod_Autoprovision_pnp' => 'PnP-Einstellungen',
    'mod_Autoprovision_addNew' => 'Hinzufügen',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Probe',
    'mod_Autoprovision_phone_settings_user' => 'Mitarbeiter',
    'mod_Autoprovision_phone_settings_mac' => 'MAC-Adresse',
    'mod_Autoprovision_template_name' => 'Name',
    'mod_Autoprovision_search_tags' => 'Suchen...',
    'mod_Autoprovision_edit_template' => 'Bearbeiten einer Vorlage',
    'mod_Autoprovision_end_edit_template' => 'Beenden Sie die Bearbeitung',
    'mod_Autoprovision_other_pbx' => 'Telefonbuch',
    'mod_Autoprovision_other_pbx_name' => 'Name der Telefonzentrale',
    'mod_Autoprovision_other_pbx_address' => 'PBX-Netzwerkadresse',
    'mod_Autoprovision_templates_header' => 'Bei der Beschreibung einer Vorlage können Sie folgende Parameter verwenden: <b>{SIP_USER_NAME}</b> – Mitarbeitername <b>{SIP_NUM}</b> – interne Nummer (Login) <b>{SIP_PASS}</b> – Passwort',
    'mod_Autoprovision_templates_users_header' => 'Bei der Beschreibung einer MAC-Adresse ist die Verwendung des Symbols <b>%</b> zulässig, was „beliebiger Zeichensatz“ bedeutet <br>
Die Vorlage <b>805e0c67%</b> entspricht <b>805e0c670001</b> und <b>805e0c670002</b>',
];
