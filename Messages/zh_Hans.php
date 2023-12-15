<?php
return [
    'mod_Autoprovision_mac_black' => '话机MAC地址黑名单',
    'mod_Autoprovision_pbx_host' => '手机注册服务器地址',
    'mod_Autoprovision_Extension' => '分机号码模板',
    'SubHeaderModuleAutoprovision' => '帮助设置 SIP 电话',
    'mo_ModuleAutoprovision' => '自动电话设置模块',
    'mod_Autoprovision_additional_params' => '其他选项',
    'mod_Autoprovision_mac_white' => '手机MAC地址白名单',
    'BreadcrumbModuleAutoprovision' => '自动电话设置模块',
    /**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
    'repModuleAutoprovision' => '模块 -% 表示%',
    'mod_Autoprovision_header' => '如果启用该模块，SIP 帐户“<b>apv-miko-pbx</b>”将在 PBX 上可用。
<br>要自动配置您的手机，您需要将其重置为出厂设置。
<br>如果话机第一次连接PBX，会注册到“<b>apv-miko-pbx</b>”账户。
<br>要配置电话，您需要从电话呼叫“<b>%extension%</b>”，其中 XXX 是 PBX 上的内部号码。
<br><br>
自动配置仅适用于企业本地网络，电话<b>Yealink、Snom、Fanvil</b>。',
    'mod_Autoprovision_phone_settings_title' => '手机设置',
    'mod_Autoprovision_phone_templates' => '设置模板',
    'mod_Autoprovision_general_settings' => 'URI 设置',
    'mod_Autoprovision_pnp' => '即插即用设置',
    'mod_Autoprovision_addNew' => '添加',
    'mod_Autoprovision_templates_uri_uri' => '统一资源标识符',
    'mod_Autoprovision_templates_uri_template' => '样本',
    'mod_Autoprovision_phone_settings_user' => '员工',
    'mod_Autoprovision_phone_settings_mac' => 'MAC地址',
    'mod_Autoprovision_template_name' => '姓名',
    'mod_Autoprovision_search_tags' => '搜索...',
    'mod_Autoprovision_edit_template' => '编辑模板',
    'mod_Autoprovision_end_edit_template' => '完成编辑',
    'mod_Autoprovision_other_pbx' => '电话簿',
    'mod_Autoprovision_other_pbx_name' => '电话交换机名称',
    'mod_Autoprovision_other_pbx_address' => 'PBX 网络地址',
    'mod_Autoprovision_templates_header' => '描述模板时，可以使用以下参数： <b>{SIP_USER_NAME}</b> - 员工姓名 <b>{SIP_NUM}</b> - 内部号码（登录） <b>{SIP_PASS}</b> - 密码',
    'mod_Autoprovision_templates_users_header' => '描述 MAC 地址时，允许使用符号 <b>%</b> - 意思是“任何字符集”<br>
模式 <b>805e0c67%</b> 将匹配 <b>805e0c670001</b> 和 <b>805e0c670002</b>',
];
