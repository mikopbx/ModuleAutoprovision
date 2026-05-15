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
    'repModuleAutoprovision' => 'Mô-đun -% represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - phân phối cấu hình điện thoại qua HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Cổng TCP riêng được ModuleAutoprovision phục vụ qua HTTP thuần (không chuyển hướng HTTPS).<br>Điện thoại IP tải các tệp cấu hình của mình từ cổng này trong quá trình provisioning.<br>Chỉ mở quyền truy cập cho mạng nội bộ nơi các điện thoại được kết nối.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — Máy chủ TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 do máy chủ TFTP tích hợp trong module, viết bằng PHP thuần, phục vụ.<br>Được sử dụng bởi các điện thoại và firmware ưu tiên DHCP option 66 (TFTP) thay vì PnP multicast — lịch sử là Snom, một số firmware Fanvil, cũng như các mạng định tuyến nơi multicast không vượt qua ranh giới L3.<br><b>Giao thức văn bản thuần:</b> chỉ mở cổng trong phân đoạn LAN nơi đặt các điện thoại.',
    'mo_ModuleAutoprovision' => 'Mô-đun cài đặt điện thoại tự động',
    'BreadcrumbModuleAutoprovision' => 'Mô-đun cài đặt điện thoại tự động',
    'SubHeaderModuleAutoprovision' => 'Trợ giúp trong việc thiết lập điện thoại SIP',
    'mod_Autoprovision_Extension' => 'Mẫu số máy lẻ',
    'mod_Autoprovision_pbx_host' => 'Địa chỉ máy chủ để đăng ký điện thoại',
    'mod_Autoprovision_http_port' => 'Cổng HTTP cấu hình tự động',
    'mod_Autoprovision_http_port_hint' => 'Cổng TCP riêng của module để cung cấp cấu hình qua HTTP thuần — không thuộc diện chuyển hướng HTTPS toàn cục. Điện thoại phải kết nối PBX qua cổng này; quy tắc tường lửa được thêm tự động.',
    'mod_Autoprovision_mac_black' => 'Danh sách đen địa chỉ MAC của điện thoại',
    'mod_Autoprovision_mac_white' => 'Danh sách cho phép địa chỉ MAC của điện thoại',
    'mod_Autoprovision_additional_params' => 'Tùy chọn bổ sung',
    'mod_Autoprovision_tftp_enabled' => 'Bật cấp phát qua TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Khởi chạy máy chủ TFTP bằng PHP thuần trên UDP/69, cung cấp các cấu hình theo từng MAC giống kênh HTTP, cùng các tệp firmware từ tab «Firmware».<br>Hữu ích khi PnP multicast bị chặn (thường gặp ở WiFi văn phòng và mạng định tuyến) hoặc khi điện thoại ưu tiên DHCP option 66 (Snom, một số firmware Fanvil).<br>Không cần binary bên ngoài — chạy bên trong worker của module. <b>Giao thức văn bản thuần</b>: chỉ bật trong LAN tin cậy. Quy tắc tường lửa cho UDP/69 được mở tự động.',
    'mod_Autoprovision_phone_settings_title' => 'Cài đặt điện thoại',
    'mod_Autoprovision_phone_templates' => 'Mẫu cài đặt',
    'mod_Autoprovision_general_settings' => 'Cài đặt URI',
    'mod_Autoprovision_pnp' => 'Cài đặt PnP',
    'mod_Autoprovision_addNew' => 'Thêm vào',
    'mod_Autoprovision_load_examples' => 'Tải các mẫu ví dụ',
    'mod_Autoprovision_load_examples_hint' => 'Thêm các mẫu ví dụ đi kèm cho Yealink, Fanvil, Snom, Grandstream và Htek. Các mẫu trùng tên đã tồn tại sẽ bị bỏ qua, nên có thể bấm nút nhiều lần mà không lo trùng lặp.',
    'mod_Autoprovision_load_examples_installed' => 'Đã cài đặt các mẫu ví dụ',
    'mod_Autoprovision_load_examples_already_present' => 'Tất cả các mẫu ví dụ đã được cài đặt.',
    'mod_Autoprovision_load_examples_failed' => 'Không thể cài đặt các mẫu ví dụ',
    'mod_Autoprovision_load_examples_partial' => 'Một số mẫu ví dụ không được cài đặt',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Có thay đổi chưa lưu trên trang. Tải các ví dụ sẽ tải lại trang và hủy chúng. Tiếp tục?',
    'mod_Autoprovision_load_examples_post_only' => 'Yêu cầu POST là bắt buộc.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Vật mẫu',
    'mod_Autoprovision_phone_settings_user' => 'Người lao động',
    'mod_Autoprovision_phone_settings_mac' => 'Địa chỉ MAC',
    'mod_Autoprovision_template_name' => 'Tên',
    'mod_Autoprovision_search_tags' => 'Tìm kiếm...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Chỉnh sửa mẫu',
    'mod_Autoprovision_end_edit_template' => 'Hoàn tất chỉnh sửa',
    'mod_Autoprovision_other_pbx' => 'Danh bạ điện thoại',
    'mod_Autoprovision_other_pbx_name' => 'Tên tổng đài điện thoại',
    'mod_Autoprovision_other_pbx_address' => 'Địa chỉ mạng PBX',
    'mod_Autoprovision_templates_header' => 'Khi mô tả mẫu, bạn có thể sử dụng các tham số sau: <b>{SIP_USER_NAME</b> - tên nhân viên <b>{SIP_NUM</b> - số nội bộ (đăng nhập) <b>{SIP_PASS</b> - mật khẩu',
    'mod_Autoprovision_other_pbx_header' => '<b>Chú ý!</b> Danh bạ phải truy cập được trên mỗi PBX tại URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Liệt kê tất cả địa chỉ của các PBX cần lấy danh bạ.<br>',
    'mod_Autoprovision_templates_users_header' => 'Khi mô tả địa chỉ MAC, được phép sử dụng ký hiệu <b>%</b> - nghĩa là “bất kỳ bộ ký tự nào” <br>
Mẫu <b>805e0c67%</b> sẽ khớp với <b>805e0c670001</b> và <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Chú ý!</b> Mọi URI được xây dựng tương đối so với giá trị cơ sở <b>/pbxcore/api/autoprovision-http</b><br>Khi mô tả URI có thể dùng ký hiệu <b>%</b> — nghĩa là „bất kỳ tập ký tự nào“.<br>URI <b>/%/%/test.cfg</b> sẽ khớp với <b>/1/2/test.cfg</b> và <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Nếu mô-đun được bật, tài khoản SIP "<b>apv-miko-pbx</b>" sẽ khả dụng trên PBX.
<br>Để tự động định cấu hình điện thoại của bạn, bạn cần đặt lại điện thoại về cài đặt gốc.
<br>Nếu điện thoại kết nối với PBX lần đầu tiên, nó sẽ được đăng ký vào tài khoản "<b>apv-miko-pbx</b>".
<br>Để định cấu hình điện thoại, bạn cần gọi "<b>%extension%</b>" từ điện thoại đó, trong đó XXX là số nội bộ trên PBX.
<br><br>
Tự động cấu hình chỉ có thể thực hiện được đối với mạng cục bộ của doanh nghiệp, dành cho điện thoại <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Tải lên các tệp firmware mà PBX sẽ phục vụ cho điện thoại qua HTTP từ cổng provisioning. Trong các mẫu, dùng placeholder <b>{FIRMWARE_URL}</b> để chèn URL tải xuống.',
    'mod_Autoprovision_firmware_drop_hint' => 'Kéo một tệp firmware vào đây hoặc bấm để chọn',
    'mod_Autoprovision_firmware_browse' => 'Chọn tệp',
    'mod_Autoprovision_firmware_vendor' => 'Nhà sản xuất',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Phiên bản',
    'mod_Autoprovision_firmware_notes' => 'Ghi chú',
    'mod_Autoprovision_firmware_filename' => 'Tệp',
    'mod_Autoprovision_firmware_size' => 'Kích thước',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Chỉnh sửa metadata firmware',
    'mod_Autoprovision_firmware_save' => 'Lưu',
    'mod_Autoprovision_firmware_cancel' => 'Hủy',
];
