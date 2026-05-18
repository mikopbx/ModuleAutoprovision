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
    'repModuleAutoprovision' => 'Módulo -% represent%',
    'fw_moduleautoprovisionDescription' => 'Aprovisionamiento automático: entrega de configuración de teléfonos por HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Puerto TCP dedicado servido por ModuleAutoprovision sobre HTTP plano (sin redirección a HTTPS).<br>Los teléfonos IP descargan sus archivos de configuración desde este puerto durante el aprovisionamiento.<br>Abra el acceso únicamente para la red local en la que se encuentran los teléfonos.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — Servidor TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 servido por el servidor TFTP en PHP puro integrado en el módulo.<br>Lo utilizan teléfonos y firmwares que prefieren DHCP option 66 (TFTP) sobre PnP multicast — históricamente Snom, algunos firmwares Fanvil y redes enrutadas donde el multicast no atraviesa la frontera L3.<br><b>Protocolo en texto plano:</b> abra el puerto sólo en el segmento LAN donde están los teléfonos.',
    'mo_ModuleAutoprovision' => 'Módulo de configuración automática de teléfono',
    'BreadcrumbModuleAutoprovision' => 'Módulo de configuración automática de teléfono',
    'SubHeaderModuleAutoprovision' => 'Ayuda para configurar teléfonos SIP',
    'mod_Autoprovision_Extension' => 'Plantilla de número de extensión',
    'mod_Autoprovision_pbx_host' => 'Dirección del servidor para el registro telefónico',
    'mod_Autoprovision_http_port' => 'Puerto HTTP de autoprovisión',
    'mod_Autoprovision_http_port_hint' => 'Puerto TCP dedicado del módulo para entregar configuraciones por HTTP puro — no está sujeto a la redirección global a HTTPS. Los teléfonos deben contactar con la PBX en este puerto; la regla de firewall se añade automáticamente.',
    'mod_Autoprovision_mac_black' => 'Lista negra de direcciones MAC de teléfonos',
    'mod_Autoprovision_mac_white' => 'Lista blanca de direcciones MAC del teléfono',
    'mod_Autoprovision_additional_params' => 'Opciones adicionales',
    'mod_Autoprovision_tftp_enabled' => 'Habilitar provisioning por TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Inicia un servidor TFTP en PHP puro en UDP/69 que entrega las mismas configuraciones por MAC que el canal HTTP, además de firmwares de la pestaña «Firmware».<br>Útil cuando el PnP multicast está bloqueado (típico en WiFi de oficinas y redes enrutadas) o cuando el teléfono prefiere DHCP option 66 (Snom, algunos firmwares Fanvil).<br>Sin binarios externos — se ejecuta dentro del worker del módulo. <b>Protocolo en texto plano</b>: habilite sólo en una LAN de confianza. La regla de firewall para UDP/69 se abre automáticamente.',
    'mod_Autoprovision_phone_settings_title' => 'Ajustes de teléfono',
    'mod_Autoprovision_phone_templates' => 'Plantillas de configuración',
    'mod_Autoprovision_general_settings' => 'Configuración de URI',
    'mod_Autoprovision_pnp' => 'Configuración PnP',
    'mod_Autoprovision_addNew' => 'Agregar',
    'mod_Autoprovision_load_examples' => 'Cargar plantillas de ejemplo',
    'mod_Autoprovision_load_examples_hint' => 'Agrega las plantillas de ejemplo incluidas para Yealink, Fanvil, Snom, Grandstream y Htek. Las plantillas con nombres ya existentes se omiten, por lo que el botón puede pulsarse repetidamente sin riesgo de duplicados.',
    'mod_Autoprovision_load_examples_installed' => 'Plantillas de ejemplo instaladas',
    'mod_Autoprovision_load_examples_already_present' => 'Todas las plantillas de ejemplo ya están instaladas.',
    'mod_Autoprovision_load_examples_failed' => 'No se pudieron instalar las plantillas de ejemplo',
    'mod_Autoprovision_load_examples_partial' => 'Parte de las plantillas de ejemplo no se instalaron',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Hay cambios sin guardar en la página. Cargar los ejemplos recargará la página y los descartará. ¿Continuar?',
    'mod_Autoprovision_load_examples_post_only' => 'Se requiere solicitud POST.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Muestra',
    'mod_Autoprovision_phone_settings_user' => 'Empleado',
    'mod_Autoprovision_phone_settings_mac' => 'Dirección MAC',
    'mod_Autoprovision_template_name' => 'Nombre',
    'mod_Autoprovision_search_tags' => 'Buscar...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Editar una plantilla',
    'mod_Autoprovision_end_edit_template' => 'Terminar de editar',
    'mod_Autoprovision_other_pbx' => 'Directorio telefónico',
    'mod_Autoprovision_other_pbx_name' => 'Nombre de la central telefónica',
    'mod_Autoprovision_other_pbx_address' => 'Dirección de red PBX',
    'mod_Autoprovision_templates_header' => 'Al describir una plantilla, puede utilizar los siguientes parámetros: <b>{SIP_USER_NAME}</b> - nombre del empleado <b>{SIP_NUM}</b> - número interno (inicio de sesión) <b>{SIP_PASS}</b> - contraseña',
    'mod_Autoprovision_other_pbx_header' => '<b>¡Atención!</b> La guía telefónica debe estar accesible en cada PBX en el URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Enumere todas las direcciones de las PBX desde las que se debe obtener la guía telefónica.<br>',
    'mod_Autoprovision_templates_users_header' => 'Al describir una dirección MAC, se permite utilizar el símbolo <b>%</b>, que significa "cualquier conjunto de caracteres" <br>
La plantilla <b>805e0c67%</b> coincidirá con <b>805e0c670001</b> y <b>805e0c670002</b>.',
    'mod_Autoprovision_templates_uri_header' => '<b>¡Atención!</b> Todas las URI se construyen relativas al valor base <b>/pbxcore/api/autoprovision-http</b><br>Al describir un URI puede usarse el símbolo <b>%</b> — que significa «cualquier conjunto de caracteres».<br>La URI <b>/%/%/test.cfg</b> coincidirá con <b>/1/2/test.cfg</b> y con <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Si el módulo está habilitado, la cuenta SIP "<b>apv-miko-pbx</b>" pasa a estar disponible en la centralita.
<br>Para configurar automáticamente su teléfono, debe restablecerlo a la configuración de fábrica.
<br>Si el teléfono se conecta a la PBX por primera vez, quedará registrado en la cuenta "<b>apv-miko-pbx</b>".
<br>Para configurar el teléfono, debe llamar a "<b>%extension%</b>" desde él, donde XXX es el número interno de la PBX.
<br><br>
La configuración automática sólo es posible para la red local de la empresa, para teléfonos <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmwares',
    'mod_Autoprovision_firmware_header' => 'Suba archivos de firmware que la PBX entregará a los teléfonos por HTTP desde el puerto de provisioning. Use el marcador <b>{FIRMWARE_URL}</b> en sus plantillas para insertar la URL de descarga.',
    'mod_Autoprovision_firmware_drop_hint' => 'Arrastre aquí un archivo de firmware o haga clic para seleccionar',
    'mod_Autoprovision_firmware_browse' => 'Elegir archivo',
    'mod_Autoprovision_firmware_vendor' => 'Fabricante',
    'mod_Autoprovision_firmware_model' => 'Modelo',
    'mod_Autoprovision_firmware_version' => 'Versión',
    'mod_Autoprovision_firmware_notes' => 'Notas',
    'mod_Autoprovision_firmware_filename' => 'Archivo',
    'mod_Autoprovision_firmware_size' => 'Tamaño',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Editar metadatos del firmware',
    'mod_Autoprovision_firmware_save' => 'Guardar',
    'mod_Autoprovision_firmware_cancel' => 'Cancelar',
];
