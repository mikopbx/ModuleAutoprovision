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
    'repModuleAutoprovision' => 'Moduł -% reprezentatywnych%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - dostarczanie konfiguracji telefonów przez HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Dedykowany port TCP obsługiwany przez ModuleAutoprovision po zwykłym HTTP (bez przekierowania na HTTPS).<br>Telefony IP pobierają z tego portu swoje pliki konfiguracyjne podczas provisioningu.<br>Dostęp należy otworzyć tylko dla sieci lokalnej, w której znajdują się telefony.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — Serwer TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 obsługiwany przez wbudowany serwer TFTP w czystym PHP.<br>Używany przez telefony i firmware preferujące DHCP option 66 (TFTP) zamiast PnP multicastowego — historycznie Snom, niektóre firmware Fanvil oraz sieci routowane, w których multicast nie przekracza granicy L3.<br><b>Protokół jawnotekstowy:</b> port otwieraj wyłącznie w segmencie LAN, w którym znajdują się telefony.',
    'mo_ModuleAutoprovision' => 'Moduł automatycznej konfiguracji telefonu',
    'BreadcrumbModuleAutoprovision' => 'Moduł automatycznej konfiguracji telefonu',
    'SubHeaderModuleAutoprovision' => 'Pomoc w konfiguracji telefonów SIP',
    'mod_Autoprovision_Extension' => 'Szablon numeru wewnętrznego',
    'mod_Autoprovision_pbx_host' => 'Adres serwera do rejestracji telefonu',
    'mod_Autoprovision_http_port' => 'Port HTTP autoprovisioningu',
    'mod_Autoprovision_http_port_hint' => 'Wydzielony port TCP modułu do dostarczania konfiguracji po czystym HTTP — nie podlega globalnemu przekierowaniu na HTTPS. Telefony muszą łączyć się z PBX-em na tym porcie; reguła firewalla jest dodawana automatycznie.',
    'mod_Autoprovision_mac_black' => 'Czarna lista adresów MAC telefonów',
    'mod_Autoprovision_mac_white' => 'Biała lista adresów MAC telefonu',
    'mod_Autoprovision_additional_params' => 'Dodatkowe opcje',
    'mod_Autoprovision_tftp_enabled' => 'Włącz provisioning przez TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Uruchamia serwer TFTP w czystym PHP na UDP/69, który dostarcza te same konfiguracje per-MAC co kanał HTTP, a także firmware z zakładki «Firmware».<br>Przydatny, gdy multicastowy PnP jest zablokowany (typowe w biurowych sieciach WiFi i sieciach routowanych) lub gdy telefon preferuje DHCP option 66 (Snom, niektóre firmware Fanvil).<br>Bez zewnętrznych binarek — działa w workerze modułu. <b>Protokół jawnotekstowy</b>: włączaj wyłącznie w zaufanym LAN-ie. Reguła firewalla dla UDP/69 otwiera się automatycznie.',
    'mod_Autoprovision_phone_settings_title' => 'Ustawienia telefonu',
    'mod_Autoprovision_phone_templates' => 'Szablony ustawień',
    'mod_Autoprovision_general_settings' => 'Ustawienia URI',
    'mod_Autoprovision_pnp' => 'Ustawienia PnP',
    'mod_Autoprovision_addNew' => 'Dodać',
    'mod_Autoprovision_load_examples' => 'Wczytaj przykładowe szablony',
    'mod_Autoprovision_load_examples_hint' => 'Dodaje wbudowane przykładowe szablony dla Yealink, Fanvil, Snom, Grandstream i Htek. Szablony o już istniejących nazwach są pomijane, więc przycisk można naciskać wielokrotnie bez ryzyka duplikatów.',
    'mod_Autoprovision_load_examples_installed' => 'Zainstalowano przykładowe szablony',
    'mod_Autoprovision_load_examples_already_present' => 'Wszystkie przykładowe szablony są już zainstalowane.',
    'mod_Autoprovision_load_examples_failed' => 'Nie udało się zainstalować przykładowych szablonów',
    'mod_Autoprovision_load_examples_partial' => 'Część przykładowych szablonów nie została zainstalowana',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Na stronie znajdują się niezapisane zmiany. Wczytanie przykładów ponownie załaduje stronę i je odrzuci. Kontynuować?',
    'mod_Autoprovision_load_examples_post_only' => 'Wymagane żądanie POST.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Próbka',
    'mod_Autoprovision_phone_settings_user' => 'Pracownik',
    'mod_Autoprovision_phone_settings_mac' => 'Adres MAC',
    'mod_Autoprovision_template_name' => 'Nazwa',
    'mod_Autoprovision_search_tags' => 'Szukaj...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Edycja szablonu',
    'mod_Autoprovision_end_edit_template' => 'Zakończ edycję',
    'mod_Autoprovision_other_pbx' => 'Książka telefoniczna',
    'mod_Autoprovision_other_pbx_name' => 'Nazwa centrali telefonicznej',
    'mod_Autoprovision_other_pbx_address' => 'Adres sieci centrali PBX',
    'mod_Autoprovision_templates_header' => 'Opisując szablon możesz wykorzystać następujące parametry: <b>{SIP_USER_NAME}</b> - imię i nazwisko pracownika <b>{SIP_NUM}</b> - numer wewnętrzny (login) <b>{SIP_PASS}</b> - hasło',
    'mod_Autoprovision_other_pbx_header' => '<b>Uwaga!</b> Książka telefoniczna musi być dostępna w każdej PBX pod URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Wymień wszystkie adresy PBX, z których ma zostać pobrana książka telefoniczna.<br>',
    'mod_Autoprovision_templates_users_header' => 'Przy opisie adresu MAC dopuszczalne jest użycie symbolu <b>%</b> - oznaczającego „dowolny zestaw znaków” <br>
Szablon <b>805e0c67%</b> będzie pasował do szablonów <b>805e0c670001</b> i <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Uwaga!</b> Wszystkie URI są budowane względem wartości bazowej <b>/pbxcore/api/autoprovision-http</b><br>Przy opisie URI dopuszczalne jest użycie symbolu <b>%</b> — oznaczającego „dowolny ciąg znaków“.<br>URI <b>/%/%/test.cfg</b> będzie pasować do <b>/1/2/test.cfg</b> i do <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Jeżeli moduł jest włączony, w centrali staje się dostępne konto SIP „<b>apv-miko-pbx</b>”.
<br>Aby automatycznie skonfigurować telefon, musisz zresetować go do ustawień fabrycznych.
<br>Jeśli telefon połączy się z centralą po raz pierwszy, zostanie zarejestrowany na koncie „<b>apv-miko-pbx</b>”.
<br>Aby skonfigurować telefon należy wywołać z niego "<b>%extension%</b>", gdzie XXX to numer wewnętrzny centrali.
<br><br>
Autokonfiguracja możliwa jest tylko dla sieci lokalnej przedsiębiorstwa, dla telefonów <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Załaduj pliki firmware, które PBX będzie udostępniać telefonom przez HTTP z portu provisioningowego. W szablonach użyj symbolu zastępczego <b>{FIRMWARE_URL}</b>, aby wstawić URL do pobrania.',
    'mod_Autoprovision_firmware_drop_hint' => 'Przeciągnij tutaj plik firmware lub kliknij, aby wybrać',
    'mod_Autoprovision_firmware_browse' => 'Wybierz plik',
    'mod_Autoprovision_firmware_vendor' => 'Producent',
    'mod_Autoprovision_firmware_model' => 'Model',
    'mod_Autoprovision_firmware_version' => 'Wersja',
    'mod_Autoprovision_firmware_notes' => 'Notatki',
    'mod_Autoprovision_firmware_filename' => 'Plik',
    'mod_Autoprovision_firmware_size' => 'Rozmiar',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Edycja metadanych firmware',
    'mod_Autoprovision_firmware_save' => 'Zapisz',
    'mod_Autoprovision_firmware_cancel' => 'Anuluj',
];
