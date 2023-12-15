<?php
return [
    'mod_Autoprovision_pbx_host' => 'Serveradress för telefonregistrering',
    'mod_Autoprovision_Extension' => 'Mall för anknytningsnummer',
    'mo_ModuleAutoprovision' => 'Automatisk telefoninställningsmodul',
    /**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
    'repModuleAutoprovision' => 'Modul -% representerar%',
    'mod_Autoprovision_additional_params' => 'Ytterligare alternativ',
    'mod_Autoprovision_mac_white' => 'Vitlista för telefon MAC-adresser',
    'mod_Autoprovision_mac_black' => 'Svartlista över MAC-adresser för telefoner',
    'SubHeaderModuleAutoprovision' => 'Hjälp med att ställa in SIP-telefoner',
    'BreadcrumbModuleAutoprovision' => 'Automatisk telefoninställningsmodul',
    'mod_Autoprovision_header' => 'Om modulen är aktiverad blir SIP-kontot "<b>apv-miko-pbx</b>" tillgängligt på telefonväxeln.
<br>För att konfigurera din telefon automatiskt måste du återställa den till fabriksinställningarna.
<br>Om telefonen ansluter till telefonväxeln för första gången kommer den att registreras på "<b>apv-miko-pbx</b>"-kontot.
<br>För att konfigurera telefonen måste du ringa "<b>%extension%</b>" från den, där XXX är det interna numret på telefonväxeln.
<br><br>
Autokonfiguration är endast möjlig för företagets lokala nätverk, för telefoner <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_phone_settings_title' => 'Telefon inställningar',
    'mod_Autoprovision_phone_templates' => 'Inställningar mallar',
    'mod_Autoprovision_general_settings' => 'URI-inställningar',
    'mod_Autoprovision_pnp' => 'PnP-inställningar',
    'mod_Autoprovision_addNew' => 'Lägg till',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Prov',
    'mod_Autoprovision_phone_settings_user' => 'Anställd',
    'mod_Autoprovision_phone_settings_mac' => 'MAC-adress',
    'mod_Autoprovision_template_name' => 'namn',
    'mod_Autoprovision_search_tags' => 'Sök...',
    'mod_Autoprovision_edit_template' => 'Redigera en mall',
    'mod_Autoprovision_end_edit_template' => 'Slutför redigeringen',
    'mod_Autoprovision_other_pbx' => 'Telefonbok',
    'mod_Autoprovision_other_pbx_name' => 'Telefonväxelns namn',
    'mod_Autoprovision_other_pbx_address' => 'PBX nätverksadress',
    'mod_Autoprovision_templates_header' => 'När du beskriver en mall kan du använda följande parametrar: <b>{SIP_USER_NAME}</b> - anställds namn <b>{SIP_NUM}</b> - internt nummer (inloggning) <b>{SIP_PASS}</b> - lösenord',
    'mod_Autoprovision_templates_users_header' => 'När du beskriver en MAC-adress är det tillåtet att använda symbolen <b>%</b> - vilket betyder "alla teckenuppsättningar" <br>
Mallen <b>805e0c67%</b> matchar <b>805e0c670001</b> och <b>805e0c670002</b>',
];
