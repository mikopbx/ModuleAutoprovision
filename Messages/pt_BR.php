<?php
return [
    'mod_Autoprovision_additional_params' => 'Opções extras',
    'mod_Autoprovision_mac_white' => 'Lista de permissões de endereços MAC de telefone',
    'mod_Autoprovision_mac_black' => 'Lista negra de endereços MAC de telefones',
    'mod_Autoprovision_pbx_host' => 'Endereço do servidor para registro do telefone',
    'mod_Autoprovision_Extension' => 'Modelo de número de ramal',
    'SubHeaderModuleAutoprovision' => 'Ajuda na configuração de telefones SIP',
    'BreadcrumbModuleAutoprovision' => 'Auto Provisionamento',
    'mo_ModuleAutoprovision' => 'Auto Provisionamento',
    /**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
    'repModuleAutoprovision' => 'Módulo -% repesent%',
    'mod_Autoprovision_header' => 'Se o módulo estiver habilitado, a conta SIP "<b>apv-miko-pbx</b>" fica disponível no PBX.
<br>Para configurar seu telefone automaticamente, você precisa redefini-lo para as configurações de fábrica.
<br>Se o telefone se conectar ao PBX pela primeira vez, ele será registrado na conta "<b>apv-miko-pbx</b>".
<br>Para configurar seu telefone, você precisa ligar para "<b>%extension%</b>" dele, onde XXX é o número interno do PBX.
<br><br>
A configuração automática é possível apenas para a rede local da empresa, para telefones <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_phone_settings_title' => 'Configurações do telefone',
    'mod_Autoprovision_phone_templates' => 'Modelos de configurações',
    'mod_Autoprovision_general_settings' => 'Configurações de URI',
    'mod_Autoprovision_pnp' => 'Configurações PnP',
    'mod_Autoprovision_addNew' => 'Adicionar',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Amostra',
    'mod_Autoprovision_phone_settings_user' => 'Funcionário',
    'mod_Autoprovision_phone_settings_mac' => 'Endereço MAC',
    'mod_Autoprovision_template_name' => 'Nome',
    'mod_Autoprovision_search_tags' => 'Procurar...',
    'mod_Autoprovision_edit_template' => 'Editando um modelo',
    'mod_Autoprovision_end_edit_template' => 'Concluir a edição',
    'mod_Autoprovision_other_pbx' => 'Lista telefônica',
    'mod_Autoprovision_other_pbx_name' => 'Nome da central telefônica',
    'mod_Autoprovision_other_pbx_address' => 'Endereço de rede PABX',
    'mod_Autoprovision_templates_header' => 'Ao descrever um modelo, você pode usar os seguintes parâmetros: <b>{SIP_USER_NAME}</b> - nome do funcionário <b>{SIP_NUM}</b> - número interno (login) <b>{SIP_PASS}</b> - senha',
    'mod_Autoprovision_templates_users_header' => 'Ao descrever um endereço MAC, é permitido usar o símbolo <b>%</b> - que significa “qualquer conjunto de caracteres” <br>
O modelo <b>805e0c67%</b> corresponderá a <b>805e0c670001</b> e <b>805e0c670002</b>',
];
