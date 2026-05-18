<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */

return [
    'repModuleAutoprovision' => 'Autoprovision module - %represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - phone configuration delivery over HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Dedicated TCP port served by ModuleAutoprovision over plain HTTP (no HTTPS redirect).<br>IP phones fetch their config files from this port during provisioning.<br>Open access only for the local network the phones live on.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision - TFTP server (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 served by the in-module pure-PHP TFTP server.<br>Used by phones / firmware that prefer DHCP option 66 (TFTP) over multicast PnP — Snom historically, some Fanvil firmwares, and routed networks where multicast does not cross the L3 boundary.<br><b>Plain-text protocol:</b> open only on the LAN segment that the phones live on.',
    'mo_ModuleAutoprovision' => 'The autoprovision module',
    'BreadcrumbModuleAutoprovision' => 'The autoprovision module',
    'SubHeaderModuleAutoprovision' => 'Bulk ip-phones setup',
    'mod_Autoprovision_Extension' => 'Provision pattern command',
    'mod_Autoprovision_pbx_host' => 'The PBX DNS name',
    'mod_Autoprovision_http_port' => 'Provisioning HTTP port',
    'mod_Autoprovision_http_port_hint' => 'Dedicated TCP port served by the module on plain HTTP (no HTTPS redirect). Phones must reach the PBX on this port; firewall is opened automatically.',
    'mod_Autoprovision_mac_black' => 'Black MAC address list',
    'mod_Autoprovision_mac_white' => 'White MAC address list',
    'mod_Autoprovision_additional_params' => 'Additional settings',
    'mod_Autoprovision_tftp_enabled' => 'Enable TFTP provisioning (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Starts a pure-PHP TFTP server on UDP/69 that delivers the same per-MAC vendor config as the HTTP channel, plus any firmware blob from the Firmware tab.<br>Useful when multicast PnP is blocked (most office WiFi, routed networks) or when a phone prefers DHCP option 66 over multicast (Snom, some Fanvil firmwares).<br>No extra binaries — runs inside the module worker. <b>Plain-text protocol</b>: only enable on a trusted LAN. The firewall rule for UDP/69 is opened automatically.',
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
    'mod_Autoprovision_load_examples' => 'Load example templates',
    'mod_Autoprovision_load_examples_hint' => 'Adds the bundled Yealink, Fanvil, Snom, Grandstream and Htek example templates. Templates that already exist (matched by name) are skipped, so it is safe to click more than once.',
    'mod_Autoprovision_load_examples_installed' => 'Example templates installed',
    'mod_Autoprovision_load_examples_already_present' => 'All example templates are already installed.',
    'mod_Autoprovision_load_examples_failed' => 'Failed to install example templates',
    'mod_Autoprovision_load_examples_partial' => 'Some example templates failed to install',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'You have unsaved changes on this page. Loading example templates will reload the page and discard them. Continue?',
    'mod_Autoprovision_load_examples_post_only' => 'POST request required.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Sample',
    'mod_Autoprovision_phone_settings_user' => 'Employee',
    'mod_Autoprovision_phone_settings_mac' => 'MAC Address',
    'mod_Autoprovision_template_name' => 'Name',
    'mod_Autoprovision_search_tags' => 'Search...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Editing a template',
    'mod_Autoprovision_other_pbx' => 'Phone book',
    'mod_Autoprovision_other_pbx_name' => 'Name of the telephone exchange',
    'mod_Autoprovision_other_pbx_address' => 'PBX network address',
    'mod_Autoprovision_other_pbx_header' => '<b>Warning!</b> The phone book must be accessible on every PBX at the URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>
List every address of the PBXes from which the phone book should be fetched.<br>',
    'mod_Autoprovision_templates_header' => 'When describing a template, you can use the following parameters: <b>{SIP_USER_NAME}</b> - employee name <b>{SIP_NUM}</b> - internal number (login) <b>{SIP_PASS}</b> - password',
    'mod_Autoprovision_templates_users_header' => 'When describing a MAC address, it is allowed to use the symbol <b>%</b> - meaning "any set of characters" <br>
The template <b>805e0c67%</b> will match <b>805e0c670001</b> and <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Warning!</b> All URIs are resolved relative to the base value <b>/pbxcore/api/autoprovision-http</b><br>
When describing a URI you may use the symbol <b>%</b> meaning "any set of characters". <br>
The URI <b>/%/%/test.cfg</b> will match <b>/1/2/test.cfg</b> and <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Upload phone firmware blobs that this PBX will serve to phones over HTTP on the provisioning port. Use the <b>{FIRMWARE_URL}</b> placeholder in your templates to inject the download URL.',
    'mod_Autoprovision_firmware_drop_hint' => 'Drag a firmware file here or click to choose',
    'mod_Autoprovision_firmware_browse' => 'Choose file',
    'mod_Autoprovision_firmware_vendor' => 'Vendor',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Version',
    'mod_Autoprovision_firmware_notes' => 'Notes',
    'mod_Autoprovision_firmware_filename' => 'File',
    'mod_Autoprovision_firmware_size' => 'Size',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Edit firmware metadata',
    'mod_Autoprovision_firmware_save' => 'Save',
    'mod_Autoprovision_firmware_cancel' => 'Cancel',
];
