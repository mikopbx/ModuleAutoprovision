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
    'repModuleAutoprovision' => 'โมดูล - % ตัวแทน%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - การส่งการตั้งค่าโทรศัพท์ผ่าน HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'พอร์ต TCP เฉพาะที่ ModuleAutoprovision ให้บริการผ่าน HTTP ธรรมดา (ไม่มีการเปลี่ยนเส้นทางไป HTTPS)<br>โทรศัพท์ IP จะดึงไฟล์การตั้งค่าจากพอร์ตนี้ระหว่างการตั้งค่าอัตโนมัติ<br>เปิดการเข้าถึงเฉพาะเครือข่ายภายในที่โทรศัพท์ตั้งอยู่เท่านั้น',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — เซิร์ฟเวอร์ TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 ให้บริการโดยเซิร์ฟเวอร์ TFTP ที่ฝังในโมดูล เขียนด้วย PHP ล้วน<br>ใช้งานโดยโทรศัพท์และเฟิร์มแวร์ที่นิยม DHCP option 66 (TFTP) มากกว่า PnP มัลติแคสต์ — เช่น Snom เฟิร์มแวร์ Fanvil บางรุ่น และเครือข่ายที่กำหนดเส้นทางซึ่งมัลติแคสต์ไม่ข้าม L3<br><b>โปรโตคอลข้อความธรรมดา:</b> เปิดพอร์ตเฉพาะในเซกเมนต์ LAN ที่โทรศัพท์อยู่',
    'mo_ModuleAutoprovision' => 'โมดูลการกำหนดค่าโทรศัพท์อัตโนมัติ',
    'BreadcrumbModuleAutoprovision' => 'โมดูลการกำหนดค่าโทรศัพท์อัตโนมัติ',
    'SubHeaderModuleAutoprovision' => 'ช่วยในการตั้งค่าโทรศัพท์ SIP',
    'mod_Autoprovision_Extension' => 'แม่แบบหมายเลขส่วนขยาย',
    'mod_Autoprovision_pbx_host' => 'ที่อยู่เซิร์ฟเวอร์สำหรับการลงทะเบียนโทรศัพท์',
    'mod_Autoprovision_http_port' => 'พอร์ต HTTP สำหรับ Autoprovisioning',
    'mod_Autoprovision_http_port_hint' => 'พอร์ต TCP เฉพาะของโมดูลสำหรับส่งคอนฟิกผ่าน HTTP ล้วน — ไม่อยู่ภายใต้การรีไดเรกต์ HTTPS ส่วนกลาง โทรศัพท์ต้องติดต่อ PBX ที่พอร์ตนี้ กฎไฟร์วอลล์ถูกเพิ่มอัตโนมัติ',
    'mod_Autoprovision_mac_black' => 'บัญชีดำของที่อยู่ MAC ของโทรศัพท์',
    'mod_Autoprovision_mac_white' => 'รายการที่อยู่ MAC ของโทรศัพท์สีขาว',
    'mod_Autoprovision_additional_params' => 'ตัวเลือกพิเศษ',
    'mod_Autoprovision_tftp_enabled' => 'เปิดใช้ Provisioning ผ่าน TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'เริ่มเซิร์ฟเวอร์ TFTP ใน PHP ล้วนที่ UDP/69 ซึ่งให้บริการคอนฟิกต่อ MAC เหมือนช่อง HTTP รวมถึงเฟิร์มแวร์จากแท็บ «Firmware»<br>มีประโยชน์เมื่อ PnP มัลติแคสต์ถูกบล็อก (พบในไวไฟสำนักงานและเครือข่ายที่กำหนดเส้นทาง) หรือเมื่อโทรศัพท์นิยม DHCP option 66 (Snom เฟิร์มแวร์ Fanvil บางรุ่น)<br>ไม่ใช้ไบนารีภายนอก — ทำงานในเวิร์กเกอร์ของโมดูล <b>โปรโตคอลข้อความธรรมดา</b>: เปิดใช้เฉพาะใน LAN ที่ไว้วางใจได้ กฎไฟร์วอลล์สำหรับ UDP/69 จะเปิดอัตโนมัติ',
    'mod_Autoprovision_phone_settings_title' => 'การตั้งค่าโทรศัพท์',
    'mod_Autoprovision_phone_templates' => 'แม่แบบการตั้งค่า',
    'mod_Autoprovision_general_settings' => 'การตั้งค่า URI',
    'mod_Autoprovision_pnp' => 'การตั้งค่า PnP',
    'mod_Autoprovision_addNew' => 'เพิ่ม',
    'mod_Autoprovision_load_examples' => 'โหลดเทมเพลตตัวอย่าง',
    'mod_Autoprovision_load_examples_hint' => 'เพิ่มเทมเพลตตัวอย่างที่มาในตัวสำหรับ Yealink, Fanvil, Snom, Grandstream และ Htek เทมเพลตที่ชื่อมีอยู่แล้วจะถูกข้าม ดังนั้นจึงคลิกปุ่มซ้ำได้โดยไม่เสี่ยงสร้างซ้ำ',
    'mod_Autoprovision_load_examples_installed' => 'ติดตั้งเทมเพลตตัวอย่างแล้ว',
    'mod_Autoprovision_load_examples_already_present' => 'เทมเพลตตัวอย่างทั้งหมดถูกติดตั้งไว้แล้ว',
    'mod_Autoprovision_load_examples_failed' => 'ติดตั้งเทมเพลตตัวอย่างไม่สำเร็จ',
    'mod_Autoprovision_load_examples_partial' => 'เทมเพลตตัวอย่างบางส่วนติดตั้งไม่สำเร็จ',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'มีการเปลี่ยนแปลงที่ยังไม่ได้บันทึกในหน้านี้ การโหลดตัวอย่างจะรีโหลดหน้าและละทิ้งการเปลี่ยนแปลง ดำเนินการต่อหรือไม่?',
    'mod_Autoprovision_load_examples_post_only' => 'ต้องใช้คำขอ POST',
    'mod_Autoprovision_templates_uri_uri' => 'ยูอาร์ไอ',
    'mod_Autoprovision_templates_uri_template' => 'ตัวอย่าง',
    'mod_Autoprovision_phone_settings_user' => 'พนักงาน',
    'mod_Autoprovision_phone_settings_mac' => 'หมายเลขทางกายภาพ',
    'mod_Autoprovision_template_name' => 'ชื่อ',
    'mod_Autoprovision_search_tags' => 'ค้นหา...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'การแก้ไขเทมเพลต',
    'mod_Autoprovision_end_edit_template' => 'แก้ไขให้เสร็จสิ้น',
    'mod_Autoprovision_other_pbx' => 'สมุดโทรศัพท์',
    'mod_Autoprovision_other_pbx_name' => 'ชื่อของการแลกเปลี่ยนทางโทรศัพท์',
    'mod_Autoprovision_other_pbx_address' => 'ที่อยู่เครือข่าย PBX',
    'mod_Autoprovision_templates_header' => 'เมื่ออธิบายเทมเพลต คุณสามารถใช้พารามิเตอร์ต่อไปนี้: <b>{SIP_USER_NAME}</b> - ชื่อพนักงาน <b>{SIP_NUM}</b> - หมายเลขภายใน (เข้าสู่ระบบ) <b>{SIP_PASS}</b> - รหัสผ่าน',
    'mod_Autoprovision_other_pbx_header' => '<b>โปรดทราบ!</b> สมุดโทรศัพท์ต้องเข้าถึงได้บนทุก PBX ที่ URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>ระบุที่อยู่ของ PBX ทุกแห่งที่จะดึงสมุดโทรศัพท์<br>',
    'mod_Autoprovision_templates_users_header' => 'เมื่ออธิบายที่อยู่ MAC อนุญาตให้ใช้สัญลักษณ์ <b>%</b> - หมายถึง “ชุดอักขระใดก็ได้” <br>
เทมเพลต <b>805e0c67%</b> จะตรงกับ <b>805e0c670001</b> และ <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>โปรดทราบ!</b> URI ทุกตัวสร้างขึ้นโดยอ้างอิงค่าฐาน <b>/pbxcore/api/autoprovision-http</b><br>ในการอธิบาย URI สามารถใช้สัญลักษณ์ <b>%</b> — หมายถึง „ชุดอักขระใด ๆ“<br>URI <b>/%/%/test.cfg</b> จะตรงกับ <b>/1/2/test.cfg</b> และ <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'หากเปิดใช้งานโมดูล บัญชี SIP "<b>apv-miko-pbx</b>" จะพร้อมใช้งานบน PBX
<br>หากต้องการกำหนดค่าโทรศัพท์ของคุณโดยอัตโนมัติ คุณต้องรีเซ็ตเป็นการตั้งค่าจากโรงงาน
<br>หากโทรศัพท์เชื่อมต่อกับ PBX เป็นครั้งแรก โทรศัพท์จะถูกลงทะเบียนในบัญชี "<b>apv-miko-pbx</b>"
<br>ในการตั้งค่าโทรศัพท์ของคุณ คุณต้องโทรไปที่หมายเลข “<b>%extension%</b>” จากนั้น XXX คือหมายเลขภายในของ PBX
<br><br>
การกำหนดค่าอัตโนมัติสามารถทำได้เฉพาะกับเครือข่ายท้องถิ่นขององค์กร สำหรับโทรศัพท์ <b>Yealink, Snom, Fanvil</b>',
    'mod_Autoprovision_firmware' => 'เฟิร์มแวร์',
    'mod_Autoprovision_firmware_header' => 'อัปโหลดไฟล์เฟิร์มแวร์ที่ PBX จะส่งให้โทรศัพท์ผ่าน HTTP จากพอร์ต provisioning ใช้พลีสโฮลเดอร์ <b>{FIRMWARE_URL}</b> ในเทมเพลตเพื่อแทรก URL สำหรับดาวน์โหลด',
    'mod_Autoprovision_firmware_drop_hint' => 'ลากไฟล์เฟิร์มแวร์มาที่นี่หรือคลิกเพื่อเลือก',
    'mod_Autoprovision_firmware_browse' => 'เลือกไฟล์',
    'mod_Autoprovision_firmware_vendor' => 'ผู้ผลิต',
    'mod_Autoprovision_firmware_model' => 'รุ่น',
    'mod_Autoprovision_firmware_version' => 'เวอร์ชัน',
    'mod_Autoprovision_firmware_notes' => 'บันทึก',
    'mod_Autoprovision_firmware_filename' => 'ไฟล์',
    'mod_Autoprovision_firmware_size' => 'ขนาด',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'แก้ไขข้อมูลเมตาของเฟิร์มแวร์',
    'mod_Autoprovision_firmware_save' => 'บันทึก',
    'mod_Autoprovision_firmware_cancel' => 'ยกเลิก',
];
