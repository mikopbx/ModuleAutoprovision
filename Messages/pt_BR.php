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
    'fw_moduleautoprovisionDescription' => 'Autoprovisionamento - entrega da configuração dos telefones via HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Porta TCP dedicada servida pelo ModuleAutoprovision em HTTP simples (sem redirecionamento para HTTPS).<br>Os telefones IP obtêm seus arquivos de configuração desta porta durante o provisionamento.<br>Abra o acesso apenas para a rede local em que os telefones estão.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — Servidor TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 atendido pelo servidor TFTP em PHP puro integrado ao módulo.<br>Usado por telefones e firmwares que preferem DHCP option 66 (TFTP) ao PnP multicast — historicamente Snom, alguns firmwares Fanvil, além de redes roteadas em que o multicast não cruza a fronteira L3.<br><b>Protocolo em texto simples:</b> abra a porta apenas no segmento LAN onde os telefones estão.',
    'mo_ModuleAutoprovision' => 'Auto Provisionamento',
    'BreadcrumbModuleAutoprovision' => 'Auto Provisionamento',
    'SubHeaderModuleAutoprovision' => 'Ajuda na configuração de telefones SIP',
    'mod_Autoprovision_Extension' => 'Modelo de número de ramal',
    'mod_Autoprovision_pbx_host' => 'Endereço do servidor para registro do telefone',
    'mod_Autoprovision_http_port' => 'Porta HTTP de autoprovisionamento',
    'mod_Autoprovision_http_port_hint' => 'Porta TCP dedicada do módulo para entregar configurações via HTTP puro — não está sujeita ao redirecionamento global para HTTPS. Os telefones devem contatar a PBX nesta porta; a regra de firewall é adicionada automaticamente.',
    'mod_Autoprovision_mac_black' => 'Lista negra de endereços MAC de telefones',
    'mod_Autoprovision_mac_white' => 'Lista de permissões de endereços MAC de telefone',
    'mod_Autoprovision_additional_params' => 'Opções extras',
    'mod_Autoprovision_tftp_enabled' => 'Habilitar provisionamento via TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Inicia um servidor TFTP em PHP puro na UDP/69 que entrega as mesmas configurações por MAC do canal HTTP, além de firmwares da aba «Firmware».<br>Útil quando o PnP multicast está bloqueado (típico em WiFi de escritório e redes roteadas) ou quando o telefone prefere DHCP option 66 (Snom, alguns firmwares Fanvil).<br>Sem binários externos — executa dentro do worker do módulo. <b>Protocolo em texto simples</b>: ative apenas em uma LAN confiável. A regra de firewall para UDP/69 é aberta automaticamente.',
    'mod_Autoprovision_phone_settings_title' => 'Configurações do telefone',
    'mod_Autoprovision_phone_templates' => 'Modelos de configurações',
    'mod_Autoprovision_general_settings' => 'Configurações de URI',
    'mod_Autoprovision_pnp' => 'Configurações PnP',
    'mod_Autoprovision_addNew' => 'Adicionar',
    'mod_Autoprovision_load_examples' => 'Carregar modelos de exemplo',
    'mod_Autoprovision_load_examples_hint' => 'Adiciona os modelos de exemplo incluídos para Yealink, Fanvil, Snom, Grandstream e Htek. Modelos com nomes já existentes são ignorados, portanto o botão pode ser clicado repetidamente sem risco de duplicatas.',
    'mod_Autoprovision_load_examples_installed' => 'Modelos de exemplo instalados',
    'mod_Autoprovision_load_examples_already_present' => 'Todos os modelos de exemplo já estão instalados.',
    'mod_Autoprovision_load_examples_failed' => 'Não foi possível instalar os modelos de exemplo',
    'mod_Autoprovision_load_examples_partial' => 'Parte dos modelos de exemplo não foi instalada',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Há alterações não salvas na página. Carregar os exemplos recarregará a página e as descartará. Continuar?',
    'mod_Autoprovision_load_examples_post_only' => 'É necessária uma requisição POST.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Amostra',
    'mod_Autoprovision_phone_settings_user' => 'Funcionário',
    'mod_Autoprovision_phone_settings_mac' => 'Endereço MAC',
    'mod_Autoprovision_template_name' => 'Nome',
    'mod_Autoprovision_search_tags' => 'Procurar...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Editando um modelo',
    'mod_Autoprovision_end_edit_template' => 'Concluir a edição',
    'mod_Autoprovision_other_pbx' => 'Lista telefônica',
    'mod_Autoprovision_other_pbx_name' => 'Nome da central telefônica',
    'mod_Autoprovision_other_pbx_address' => 'Endereço de rede PABX',
    'mod_Autoprovision_templates_header' => 'Ao descrever um modelo, você pode usar os seguintes parâmetros: <b>{SIP_USER_NAME}</b> - nome do funcionário <b>{SIP_NUM}</b> - número interno (login) <b>{SIP_PASS}</b> - senha',
    'mod_Autoprovision_other_pbx_header' => '<b>Atenção!</b> A lista telefônica deve estar acessível em cada PBX no URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Liste todos os endereços das PBXs das quais a lista telefônica deve ser obtida.<br>',
    'mod_Autoprovision_templates_users_header' => 'Ao descrever um endereço MAC, é permitido usar o símbolo <b>%</b> - que significa “qualquer conjunto de caracteres” <br>
O modelo <b>805e0c67%</b> corresponderá a <b>805e0c670001</b> e <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Atenção!</b> Todos os URIs são construídos em relação ao valor base <b>/pbxcore/api/autoprovision-http</b><br>Na descrição de um URI é permitido usar o símbolo <b>%</b> — que significa „qualquer conjunto de caracteres“.<br>O URI <b>/%/%/test.cfg</b> corresponderá a <b>/1/2/test.cfg</b> e a <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Se o módulo estiver habilitado, a conta SIP "<b>apv-miko-pbx</b>" fica disponível no PBX.
<br>Para configurar seu telefone automaticamente, você precisa redefini-lo para as configurações de fábrica.
<br>Se o telefone se conectar ao PBX pela primeira vez, ele será registrado na conta "<b>apv-miko-pbx</b>".
<br>Para configurar seu telefone, você precisa ligar para "<b>%extension%</b>" dele, onde XXX é o número interno do PBX.
<br><br>
A configuração automática é possível apenas para a rede local da empresa, para telefones <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmwares',
    'mod_Autoprovision_firmware_header' => 'Carregue arquivos de firmware que a PBX entregará aos telefones via HTTP pela porta de provisionamento. Use o marcador <b>{FIRMWARE_URL}</b> nos seus modelos para inserir a URL de download.',
    'mod_Autoprovision_firmware_drop_hint' => 'Arraste um arquivo de firmware aqui ou clique para escolher',
    'mod_Autoprovision_firmware_browse' => 'Escolher arquivo',
    'mod_Autoprovision_firmware_vendor' => 'Fabricante',
    'mod_Autoprovision_firmware_model' => 'Modelo',
    'mod_Autoprovision_firmware_version' => 'Versão',
    'mod_Autoprovision_firmware_notes' => 'Notas',
    'mod_Autoprovision_firmware_filename' => 'Arquivo',
    'mod_Autoprovision_firmware_size' => 'Tamanho',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Editar metadados do firmware',
    'mod_Autoprovision_firmware_save' => 'Salvar',
    'mod_Autoprovision_firmware_cancel' => 'Cancelar',
];
