<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;

return [
    'mod_Autoprovision_header' => 'Als de module is ingeschakeld, wordt het SIP-account "<b>apv-miko-pbx</b>" beschikbaar op de PBX.
<br>Als u uw telefoon automatisch wilt configureren, moet u deze terugzetten naar de fabrieksinstellingen.
<br>Als de telefoon voor de eerste keer verbinding maakt met de PBX, wordt deze geregistreerd bij de "<b>apv-miko-pbx</b>"-account.
<br>Om de telefoon te configureren, moet u vanaf de telefoon "<b>%extension%</b>" bellen, waarbij XXX het interne nummer op de PBX is.
<br><br>
Automatische configuratie is alleen mogelijk voor toestellen in hetzelfde lokale netwerk, voor telefoons van <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_additional_params' => 'Overige instellingen',
    'mod_Autoprovision_mac_white' => 'MAC-whitelist',
    'mod_Autoprovision_mac_black' => 'MAC-blacklist',
    'mod_Autoprovision_pbx_host' => 'PBX DNS naam',
    'mod_Autoprovision_Extension' => 'Instelpatroon',
    'SubHeaderModuleAutoprovision' => 'Bulk IP-telefoon installatie',
    'BreadcrumbModuleAutoprovision' => 'Autoprovision module',
    'mo_ModuleAutoprovision' => 'Autoprovision module',
    'repModuleAutoprovision' => 'Module - %represent%',
    'mod_Autoprovision_phone_settings_title' => 'Telefoon instellingen',
    'mod_Autoprovision_phone_templates' => 'Sjablonen voor instellingen',
    'mod_Autoprovision_general_settings' => 'URI-instellingen',
    'mod_Autoprovision_pnp' => 'PnP-instellingen',
    'mod_Autoprovision_addNew' => 'Toevoegen',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Steekproef',
    'mod_Autoprovision_phone_settings_user' => 'Medewerker',
    'mod_Autoprovision_phone_settings_mac' => 'Mac adres',
    'mod_Autoprovision_template_name' => 'Naam',
    'mod_Autoprovision_search_tags' => 'Zoekopdracht...',
    'mod_Autoprovision_edit_template' => 'Een sjabloon bewerken',
    'mod_Autoprovision_end_edit_template' => 'Voltooi het bewerken',
    'mod_Autoprovision_other_pbx' => 'Telefoonboek',
    'mod_Autoprovision_other_pbx_name' => 'Naam van de telefooncentrale',
    'mod_Autoprovision_other_pbx_address' => 'PBX-netwerkadres',
    'mod_Autoprovision_templates_header' => 'Bij het beschrijven van een sjabloon kunt u de volgende parameters gebruiken: <b>{SIP_USER_NAME}</b> - naam van de werknemer <b>{SIP_NUM}</b> - intern nummer (login) <b>{SIP_PASS}</b> - wachtwoord',
    'mod_Autoprovision_templates_users_header' => 'Bij het beschrijven van een MAC-adres is het toegestaan om het symbool <b>%</b> te gebruiken, wat \'elke reeks tekens\' betekent <br>
De sjabloon <b>805e0c67%</b> komt overeen met <b>805e0c670001</b> en <b>805e0c670002</b>',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_other_pbx_header' => '<b>Warning!</b> The phone book must be accessible on every PBX at the URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>
List every address of the PBXes from which the phone book should be fetched.<br>',
    'mod_Autoprovision_templates_uri_header' => '<b>Warning!</b> All URIs are resolved relative to the base value <b>/pbxcore/api/autoprovision-http</b><br>
When describing a URI you may use the symbol <b>%</b> meaning "any set of characters". <br>
The URI <b>/%/%/test.cfg</b> will match <b>/1/2/test.cfg</b> and <b>/test/test3/test.cfg</b>',
];
