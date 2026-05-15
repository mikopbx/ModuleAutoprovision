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
    'repModuleAutoprovision' => '模块 -% 表示%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - 通过 HTTP 分发电话配置',
    'fw_moduleautoprovisionDescriptionHint' => 'ModuleAutoprovision 通过纯 HTTP（无 HTTPS 重定向）提供的专用 TCP 端口。<br>IP 电话在自动配置过程中从该端口获取其配置文件。<br>仅向电话所在的本地网络开放访问。',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP 服务器（UDP/69）',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 由模块内置的纯 PHP TFTP 服务器提供服务。<br>用于优先使用 DHCP option 66（TFTP）而非多播 PnP 的电话和固件 — 历史上是 Snom、部分 Fanvil 固件，以及多播无法越过 L3 边界的路由网络。<br><b>明文协议：</b> 仅在电话所在的 LAN 网段中开放此端口。',
    'mo_ModuleAutoprovision' => '自动电话设置模块',
    'BreadcrumbModuleAutoprovision' => '自动电话设置模块',
    'SubHeaderModuleAutoprovision' => '帮助设置 SIP 电话',
    'mod_Autoprovision_Extension' => '分机号码模板',
    'mod_Autoprovision_pbx_host' => '手机注册服务器地址',
    'mod_Autoprovision_http_port' => '自动配置 HTTP 端口',
    'mod_Autoprovision_http_port_hint' => '模块用于通过纯 HTTP 下发配置的专用 TCP 端口 — 不受全局 HTTPS 重定向影响。电话必须通过此端口访问 PBX；防火墙规则将自动添加。',
    'mod_Autoprovision_mac_black' => '话机MAC地址黑名单',
    'mod_Autoprovision_mac_white' => '手机MAC地址白名单',
    'mod_Autoprovision_additional_params' => '其他选项',
    'mod_Autoprovision_tftp_enabled' => '启用 TFTP 配置（UDP/69）',
    'mod_Autoprovision_tftp_enabled_hint' => '在 UDP/69 上启动一个纯 PHP TFTP 服务器，提供与 HTTP 通道相同的按 MAC 配置，以及来自「固件」标签页的固件文件。<br>当多播 PnP 被阻止时（在办公室 WiFi 和路由网络中常见）或电话偏好 DHCP option 66 时（Snom，部分 Fanvil 固件）很有用。<br>无需外部二进制 — 在模块的 worker 中运行。<b>明文协议</b>：仅在可信 LAN 中启用。UDP/69 的防火墙规则会自动开启。',
    'mod_Autoprovision_phone_settings_title' => '手机设置',
    'mod_Autoprovision_phone_templates' => '设置模板',
    'mod_Autoprovision_general_settings' => 'URI 设置',
    'mod_Autoprovision_pnp' => '即插即用设置',
    'mod_Autoprovision_addNew' => '添加',
    'mod_Autoprovision_load_examples' => '加载示例模板',
    'mod_Autoprovision_load_examples_hint' => '添加 Yealink、Fanvil、Snom、Grandstream 和 Htek 的内置示例模板。已存在同名的模板将被跳过，因此可以反复点击该按钮，而不会产生重复。',
    'mod_Autoprovision_load_examples_installed' => '示例模板已安装',
    'mod_Autoprovision_load_examples_already_present' => '所有示例模板都已安装。',
    'mod_Autoprovision_load_examples_failed' => '示例模板安装失败',
    'mod_Autoprovision_load_examples_partial' => '部分示例模板未能安装',
    'mod_Autoprovision_load_examples_unsaved_warning' => '页面上有未保存的更改。加载示例将重新加载页面并丢弃更改。是否继续？',
    'mod_Autoprovision_load_examples_post_only' => '需要 POST 请求。',
    'mod_Autoprovision_templates_uri_uri' => '统一资源标识符',
    'mod_Autoprovision_templates_uri_template' => '样本',
    'mod_Autoprovision_phone_settings_user' => '员工',
    'mod_Autoprovision_phone_settings_mac' => 'MAC地址',
    'mod_Autoprovision_template_name' => '姓名',
    'mod_Autoprovision_search_tags' => '搜索...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => '编辑模板',
    'mod_Autoprovision_end_edit_template' => '完成编辑',
    'mod_Autoprovision_other_pbx' => '电话簿',
    'mod_Autoprovision_other_pbx_name' => '电话交换机名称',
    'mod_Autoprovision_other_pbx_address' => 'PBX 网络地址',
    'mod_Autoprovision_templates_header' => '描述模板时，可以使用以下参数： <b>{SIP_USER_NAME}</b> - 员工姓名 <b>{SIP_NUM}</b> - 内部号码（登录） <b>{SIP_PASS}</b> - 密码',
    'mod_Autoprovision_other_pbx_header' => '<b>注意！</b> 电话簿必须在每个 PBX 上的 URI <b>/pbxcore/api/autoprovision-http/phonebook</b> 处可访问<br>列出所有需要获取电话簿的 PBX 地址。<br>',
    'mod_Autoprovision_templates_users_header' => '描述 MAC 地址时，允许使用符号 <b>%</b> - 意思是“任何字符集”<br>
模式 <b>805e0c67%</b> 将匹配 <b>805e0c670001</b> 和 <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>注意！</b> 所有 URI 都基于基础值 <b>/pbxcore/api/autoprovision-http</b> 构建<br>在描述 URI 时可以使用符号 <b>%</b> — 表示「任意字符序列」。<br>URI <b>/%/%/test.cfg</b> 将匹配 <b>/1/2/test.cfg</b> 和 <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => '如果启用该模块，SIP 帐户“<b>apv-miko-pbx</b>”将在 PBX 上可用。
<br>要自动配置您的手机，您需要将其重置为出厂设置。
<br>如果话机第一次连接PBX，会注册到“<b>apv-miko-pbx</b>”账户。
<br>要配置电话，您需要从电话呼叫“<b>%extension%</b>”，其中 XXX 是 PBX 上的内部号码。
<br><br>
自动配置仅适用于企业本地网络，电话<b>Yealink、Snom、Fanvil</b>。',
    'mod_Autoprovision_firmware' => '固件',
    'mod_Autoprovision_firmware_header' => '上传 PBX 将通过 HTTP 从配置端口为电话提供的固件文件。在模板中使用占位符 <b>{FIRMWARE_URL}</b> 来插入下载 URL。',
    'mod_Autoprovision_firmware_drop_hint' => '将固件文件拖到此处，或点击以选择',
    'mod_Autoprovision_firmware_browse' => '选择文件',
    'mod_Autoprovision_firmware_vendor' => '厂商',
    'mod_Autoprovision_firmware_model' => '型号',
    'mod_Autoprovision_firmware_version' => '版本',
    'mod_Autoprovision_firmware_notes' => '备注',
    'mod_Autoprovision_firmware_filename' => '文件',
    'mod_Autoprovision_firmware_size' => '大小',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => '编辑固件元数据',
    'mod_Autoprovision_firmware_save' => '保存',
    'mod_Autoprovision_firmware_cancel' => '取消',
];
