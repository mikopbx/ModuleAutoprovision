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
    'repModuleAutoprovision' => 'モジュール-％represent％',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - HTTP経由での電話機設定配信',
    'fw_moduleautoprovisionDescriptionHint' => 'ModuleAutoprovision が平文 HTTP (HTTPS リダイレクトなし) で提供する専用 TCP ポートです。<br>IP 電話はプロビジョニング時にこのポートから設定ファイルを取得します。<br>電話機が接続されているローカルネットワークに対してのみアクセスを許可してください。',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP サーバー（UDP/69）',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 はモジュール組み込みの純粋 PHP 製 TFTP サーバーが提供します。<br>マルチキャスト PnP より DHCP option 66（TFTP）を優先する電話機やファームウェアが使用します。歴史的には Snom、一部の Fanvil ファームウェア、およびマルチキャストが L3 境界を越えないルーティング ネットワークです。<br><b>平文プロトコル：</b> 電話機が存在する LAN セグメントでのみポートを開放してください。',
    'mo_ModuleAutoprovision' => '自動電話セットアップモジュール',
    'BreadcrumbModuleAutoprovision' => '自動電話セットアップモジュール',
    'SubHeaderModuleAutoprovision' => 'SIP電話の設定を支援する',
    'mod_Autoprovision_Extension' => '内線番号テンプレート',
    'mod_Autoprovision_pbx_host' => '電話登録用のサーバーアドレス',
    'mod_Autoprovision_http_port' => 'オートプロビジョニング HTTP ポート',
    'mod_Autoprovision_http_port_hint' => '純粋 HTTP で設定ファイルを配信するモジュール専用の TCP ポート — グローバルな HTTPS リダイレクトの対象外です。電話機はこのポートで PBX にアクセスする必要があり、ファイアウォール規則は自動的に追加されます。',
    'mod_Autoprovision_mac_black' => '電話のMACアドレスのブラックリスト',
    'mod_Autoprovision_mac_white' => '電話のMACアドレスのホワイトリスト',
    'mod_Autoprovision_additional_params' => '追加オプション',
    'mod_Autoprovision_tftp_enabled' => 'TFTP プロビジョニングを有効化（UDP/69）',
    'mod_Autoprovision_tftp_enabled_hint' => 'UDP/69 上で純粋 PHP 製の TFTP サーバーを起動し、HTTP チャネルと同じ MAC 単位の設定ファイル、および「ファームウェア」タブのファームウェア ファイルを配信します。<br>マルチキャスト PnP がブロックされている場合（オフィス WiFi やルーティング ネットワークで一般的）、または電話機が DHCP option 66 を優先する場合（Snom、一部の Fanvil ファームウェア）に有用です。<br>外部バイナリは不要 — モジュールのワーカー内で動作します。<b>平文プロトコル</b>：信頼できる LAN でのみ有効にしてください。UDP/69 のファイアウォール規則は自動的に開きます。',
    'mod_Autoprovision_phone_settings_title' => '電話の設定',
    'mod_Autoprovision_phone_templates' => '設定テンプレート',
    'mod_Autoprovision_general_settings' => 'URI設定',
    'mod_Autoprovision_pnp' => 'PnP設定',
    'mod_Autoprovision_addNew' => '追加',
    'mod_Autoprovision_load_examples' => 'サンプル テンプレートを読み込む',
    'mod_Autoprovision_load_examples_hint' => 'Yealink、Fanvil、Snom、Grandstream、Htek の組み込みサンプル テンプレートを追加します。既存の名前のテンプレートはスキップされるため、重複作成のリスクなくボタンを繰り返し押せます。',
    'mod_Autoprovision_load_examples_installed' => 'サンプル テンプレートをインストールしました',
    'mod_Autoprovision_load_examples_already_present' => 'すべてのサンプル テンプレートは既にインストールされています。',
    'mod_Autoprovision_load_examples_failed' => 'サンプル テンプレートのインストールに失敗しました',
    'mod_Autoprovision_load_examples_partial' => '一部のサンプル テンプレートをインストールできませんでした',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'ページに未保存の変更があります。サンプルを読み込むとページが再読み込みされ、変更は破棄されます。続行しますか？',
    'mod_Autoprovision_load_examples_post_only' => 'POST リクエストが必要です。',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'サンプル',
    'mod_Autoprovision_phone_settings_user' => '従業員',
    'mod_Autoprovision_phone_settings_mac' => 'Macアドレス',
    'mod_Autoprovision_template_name' => '名前',
    'mod_Autoprovision_search_tags' => '検索...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'テンプレートの編集',
    'mod_Autoprovision_end_edit_template' => '編集を終了する',
    'mod_Autoprovision_other_pbx' => '電話帳',
    'mod_Autoprovision_other_pbx_name' => '電話交換局の名前',
    'mod_Autoprovision_other_pbx_address' => 'PBXネットワークアドレス',
    'mod_Autoprovision_templates_header' => 'テンプレートを記述する場合、次のパラメータを使用できます。 <b>{SIP_USER_NAME}</b> - 従業員名 <b>{SIP_NUM}</b> - 内部番号 (ログイン) <b>{SIP_PASS}</b> - パスワード',
    'mod_Autoprovision_other_pbx_header' => '<b>注意！</b> 電話帳は各 PBX で URI <b>/pbxcore/api/autoprovision-http/phonebook</b> からアクセス可能である必要があります<br>電話帳を取得すべき PBX のすべてのアドレスを列挙してください。<br>',
    'mod_Autoprovision_templates_users_header' => 'MAC アドレスを記述するときは、「任意の文字セット」を意味する記号 <b>%</b> を使用できます。 <br>
テンプレート <b>805e0c67%</b> は <b>805e0c670001</b> と <b>805e0c670002</b> に一致します。',
    'mod_Autoprovision_templates_uri_header' => '<b>注意！</b> すべての URI は基本値 <b>/pbxcore/api/autoprovision-http</b> に対する相対パスとして構築されます<br>URI の記述では記号 <b>%</b> を使用できます — これは「任意の文字列」を意味します。<br>URI <b>/%/%/test.cfg</b> は <b>/1/2/test.cfg</b> および <b>/test/test3/test.cfg</b> に一致します',
    'mod_Autoprovision_header' => 'モジュールが有効になっている場合、SIP アカウント「<b>apv-miko-pbx</b>」が PBX で使用できるようになります。
<br>携帯電話を自動的に設定するには、携帯電話を工場出荷時の設定にリセットする必要があります。
<br>電話機が初めて PBX に接続すると、「<b>apv-miko-pbx</b>」アカウントに登録されます。
<br>電話を設定するには、そこから「<b>%extension%</b>」に電話する必要があります。XXX は PBX の内部番号です。
<br><br>
自動構成は、企業のローカル ネットワーク、電話機<b>Yealink、Snom、Fanvil</b> でのみ可能です。',
    'mod_Autoprovision_firmware' => 'ファームウェア',
    'mod_Autoprovision_firmware_header' => 'PBX がプロビジョニング ポートから HTTP 経由で電話機に配信するファームウェア ファイルをアップロードしてください。テンプレートでは <b>{FIRMWARE_URL}</b> プレースホルダーを使用して、ダウンロード URL を挿入します。',
    'mod_Autoprovision_firmware_drop_hint' => 'ファームウェア ファイルをここにドラッグするか、クリックして選択してください',
    'mod_Autoprovision_firmware_browse' => 'ファイルを選択',
    'mod_Autoprovision_firmware_vendor' => 'メーカー',
    'mod_Autoprovision_firmware_model' => 'モデル',
    'mod_Autoprovision_firmware_version' => 'バージョン',
    'mod_Autoprovision_firmware_notes' => 'メモ',
    'mod_Autoprovision_firmware_filename' => 'ファイル',
    'mod_Autoprovision_firmware_size' => 'サイズ',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'ファームウェア メタデータの編集',
    'mod_Autoprovision_firmware_save' => '保存',
    'mod_Autoprovision_firmware_cancel' => 'キャンセル',
];
