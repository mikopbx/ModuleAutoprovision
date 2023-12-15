<?php
return [
    /**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
    'repModuleAutoprovision' => 'Autoprovision module - %repesent%',
    'mo_ModuleAutoprovision' => 'The autoprovision module',
    'BreadcrumbModuleAutoprovision' => 'The autoprovision module',
    'SubHeaderModuleAutoprovision' => 'Bulk ip-phones setup',
    'mod_Autoprovision_Extension' => 'Provision pattern command',
    'mod_Autoprovision_pbx_host' => 'The PBX DNS name',
    'mod_Autoprovision_mac_black' => 'Black MAC address list',
    'mod_Autoprovision_mac_white' => 'White MAC address list',
    'mod_Autoprovision_additional_params' => 'Additional settings',
    'mod_Autoprovision_header' => 'If the module is enabled, the SIP account "<b>apv-miko-pbx</b>" becomes available on the PBX.
<br>To automatically configure your phone, you need to reset it to factory settings.
<br>If the phone connects to the PBX for the first time, it will be registered to the "<b>apv-miko-pbx</b>" account.
<br>To configure the phone, you need to call "<b>%extension%</b>" from it, where XXX is the internal number on the PBX.
<br><br>
Autoconfiguration is possible only for the local network of the enterprise, for phones <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_end_edit_template' => 'Finish editing',
    'mod_Autoprovision_phone_settings_title' => 'Phone settings',
    'mod_Autoprovision_phone_templates' => 'Settings templates',
    'mod_Autoprovision_general_settings' => 'URI Settings',
    'mod_Autoprovision_pnp' => 'PnP Settings',
    'mod_Autoprovision_addNew' => 'Add',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Sample',
    'mod_Autoprovision_phone_settings_user' => 'Employee',
    'mod_Autoprovision_phone_settings_mac' => 'MAC Address',
    'mod_Autoprovision_template_name' => 'Name',
    'mod_Autoprovision_search_tags' => 'Search...',
    'mod_Autoprovision_edit_template' => 'Editing a template',
    'mod_Autoprovision_other_pbx' => 'Phone book',
    'mod_Autoprovision_other_pbx_name' => 'Name of the telephone exchange',
    'mod_Autoprovision_other_pbx_address' => 'PBX network address',
    'mod_Autoprovision_templates_header' => 'When describing a template, you can use the following parameters: <b>{SIP_USER_NAME}</b> - employee name <b>{SIP_NUM}</b> - internal number (login) <b>{SIP_PASS}</b> - password',
    'mod_Autoprovision_templates_users_header' => 'When describing a MAC address, it is allowed to use the symbol <b>%</b> - meaning “any set of characters” <br>
The template <b>805e0c67%</b> will match <b>805e0c670001</b> and <b>805e0c670002</b>',
];
