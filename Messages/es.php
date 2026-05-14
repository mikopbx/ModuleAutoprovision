<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;

return [
    'mo_ModuleAutoprovision' => 'Módulo de configuración automática de teléfono',
    'mod_Autoprovision_additional_params' => 'Opciones adicionales',
    'mod_Autoprovision_mac_white' => 'Lista blanca de direcciones MAC del teléfono',
    'mod_Autoprovision_mac_black' => 'Lista negra de direcciones MAC de teléfonos',
    'mod_Autoprovision_pbx_host' => 'Dirección del servidor para el registro telefónico',
    'mod_Autoprovision_Extension' => 'Plantilla de número de extensión',
    'SubHeaderModuleAutoprovision' => 'Ayuda para configurar teléfonos SIP',
    'BreadcrumbModuleAutoprovision' => 'Módulo de configuración automática de teléfono',
    'repModuleAutoprovision' => 'Módulo -% represent%',
    'mod_Autoprovision_header' => 'Si el módulo está habilitado, la cuenta SIP "<b>apv-miko-pbx</b>" pasa a estar disponible en la centralita.
<br>Para configurar automáticamente su teléfono, debe restablecerlo a la configuración de fábrica.
<br>Si el teléfono se conecta a la PBX por primera vez, quedará registrado en la cuenta "<b>apv-miko-pbx</b>".
<br>Para configurar el teléfono, debe llamar a "<b>%extension%</b>" desde él, donde XXX es el número interno de la PBX.
<br><br>
La configuración automática sólo es posible para la red local de la empresa, para teléfonos <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_phone_settings_title' => 'Ajustes de teléfono',
    'mod_Autoprovision_phone_templates' => 'Plantillas de configuración',
    'mod_Autoprovision_general_settings' => 'Configuración de URI',
    'mod_Autoprovision_pnp' => 'Configuración PnP',
    'mod_Autoprovision_addNew' => 'Agregar',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Muestra',
    'mod_Autoprovision_phone_settings_user' => 'Empleado',
    'mod_Autoprovision_phone_settings_mac' => 'Dirección MAC',
    'mod_Autoprovision_templates_header' => 'Al describir una plantilla, puede utilizar los siguientes parámetros: <b>{SIP_USER_NAME}</b> - nombre del empleado <b>{SIP_NUM}</b> - número interno (inicio de sesión) <b>{SIP_PASS}</b> - contraseña',
    'mod_Autoprovision_templates_users_header' => 'Al describir una dirección MAC, se permite utilizar el símbolo <b>%</b>, que significa "cualquier conjunto de caracteres" <br>
La plantilla <b>805e0c67%</b> coincidirá con <b>805e0c670001</b> y <b>805e0c670002</b>.',
    'mod_Autoprovision_template_name' => 'Nombre',
    'mod_Autoprovision_search_tags' => 'Buscar...',
    'mod_Autoprovision_edit_template' => 'Editar una plantilla',
    'mod_Autoprovision_end_edit_template' => 'Terminar de editar',
    'mod_Autoprovision_other_pbx' => 'Directorio telefónico',
    'mod_Autoprovision_other_pbx_name' => 'Nombre de la central telefónica',
    'mod_Autoprovision_other_pbx_address' => 'Dirección de red PBX',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_other_pbx_header' => '<b>Warning!</b> The phone book must be accessible on every PBX at the URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>
List every address of the PBXes from which the phone book should be fetched.<br>',
    'mod_Autoprovision_templates_uri_header' => '<b>Warning!</b> All URIs are resolved relative to the base value <b>/pbxcore/api/autoprovision-http</b><br>
When describing a URI you may use the symbol <b>%</b> meaning "any set of characters". <br>
The URI <b>/%/%/test.cfg</b> will match <b>/1/2/test.cfg</b> and <b>/test/test3/test.cfg</b>',
];
