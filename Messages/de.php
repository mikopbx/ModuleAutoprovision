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
    'repModuleAutoprovision' => 'Modul -%represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision – Auslieferung der Telefonkonfiguration über HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Dedizierter TCP-Port, den ModuleAutoprovision über reines HTTP bereitstellt (keine HTTPS-Weiterleitung).<br>IP-Telefone beziehen ihre Konfigurationsdateien während des Provisionierens von diesem Port.<br>Öffnen Sie den Zugriff nur für das lokale Netzwerk, in dem die Telefone stehen.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP-Server (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69 wird vom modulinternen TFTP-Server in reinem PHP bedient.<br>Wird von Telefonen und Firmwares verwendet, die DHCP option 66 (TFTP) gegenüber Multicast-PnP bevorzugen — historisch Snom, einige Fanvil-Firmwares sowie geroutete Netzwerke, in denen Multicast die L3-Grenze nicht überschreitet.<br><b>Klartextprotokoll:</b> Port nur in dem LAN-Segment öffnen, in dem die Telefone stehen.',
    'mo_ModuleAutoprovision' => 'Automatisches Telefon-Setup-Modul',
    'BreadcrumbModuleAutoprovision' => 'Automatisches Telefon-Setup-Modul',
    'SubHeaderModuleAutoprovision' => 'Hilfe beim Einrichten von SIP-Telefonen',
    'mod_Autoprovision_Extension' => 'Nebenstellennummernvorlage',
    'mod_Autoprovision_pbx_host' => 'Serveradresse für die Telefonregistrierung',
    'mod_Autoprovision_http_port' => 'HTTP-Port für Autoprovisionierung',
    'mod_Autoprovision_http_port_hint' => 'Dedizierter TCP-Port des Moduls für die Auslieferung von Konfigurationen über reines HTTP — unterliegt nicht der globalen HTTPS-Weiterleitung. Telefone müssen die PBX über diesen Port erreichen; Firewall-Regel wird automatisch hinzugefügt.',
    'mod_Autoprovision_mac_black' => 'Blacklist der MAC-Adressen von Telefonen',
    'mod_Autoprovision_mac_white' => 'Whitelist der MAC-Adresse des Telefons',
    'mod_Autoprovision_additional_params' => 'Erweiterte Einstellungen',
    'mod_Autoprovision_tftp_enabled' => 'TFTP-Provisionierung aktivieren (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Startet einen TFTP-Server in reinem PHP auf UDP/69, der dieselben Per-MAC-Konfigurationen wie der HTTP-Kanal sowie Firmware-Dateien aus dem Tab «Firmware» ausliefert.<br>Nützlich, wenn Multicast-PnP blockiert ist (typisch in Büro-WLANs und gerouteten Netzwerken) oder das Telefon DHCP option 66 bevorzugt (Snom, einige Fanvil-Firmwares).<br>Keine externen Binaries — läuft im Worker des Moduls. <b>Klartextprotokoll</b>: nur in einem vertrauenswürdigen LAN aktivieren. Die Firewall-Regel für UDP/69 wird automatisch geöffnet.',
    'mod_Autoprovision_phone_settings_title' => 'Telefoneinstellungen',
    'mod_Autoprovision_phone_templates' => 'Einstellungsvorlagen',
    'mod_Autoprovision_general_settings' => 'URI-Einstellungen',
    'mod_Autoprovision_pnp' => 'PnP-Einstellungen',
    'mod_Autoprovision_addNew' => 'Hinzufügen',
    'mod_Autoprovision_load_examples' => 'Beispielvorlagen laden',
    'mod_Autoprovision_load_examples_hint' => 'Fügt die mitgelieferten Beispielvorlagen für Yealink, Fanvil, Snom, Grandstream und Htek hinzu. Vorlagen mit bereits vorhandenen Namen werden übersprungen, sodass die Schaltfläche mehrmals geklickt werden kann, ohne Duplikate zu erzeugen.',
    'mod_Autoprovision_load_examples_installed' => 'Beispielvorlagen installiert',
    'mod_Autoprovision_load_examples_already_present' => 'Alle Beispielvorlagen sind bereits installiert.',
    'mod_Autoprovision_load_examples_failed' => 'Beispielvorlagen konnten nicht installiert werden',
    'mod_Autoprovision_load_examples_partial' => 'Einige Beispielvorlagen konnten nicht installiert werden',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Auf der Seite gibt es nicht gespeicherte Änderungen. Das Laden der Beispiele lädt die Seite neu und verwirft sie. Fortfahren?',
    'mod_Autoprovision_load_examples_post_only' => 'POST-Anfrage erforderlich.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Probe',
    'mod_Autoprovision_phone_settings_user' => 'Mitarbeiter',
    'mod_Autoprovision_phone_settings_mac' => 'MAC-Adresse',
    'mod_Autoprovision_template_name' => 'Name',
    'mod_Autoprovision_search_tags' => 'Suchen...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Bearbeiten einer Vorlage',
    'mod_Autoprovision_end_edit_template' => 'Beenden Sie die Bearbeitung',
    'mod_Autoprovision_other_pbx' => 'Telefonbuch',
    'mod_Autoprovision_other_pbx_name' => 'Name der Telefonzentrale',
    'mod_Autoprovision_other_pbx_address' => 'PBX-Netzwerkadresse',
    'mod_Autoprovision_templates_header' => 'Bei der Beschreibung einer Vorlage können Sie folgende Parameter verwenden: <b>{SIP_USER_NAME}</b> – Mitarbeitername <b>{SIP_NUM}</b> – interne Nummer (Login) <b>{SIP_PASS}</b> – Passwort',
    'mod_Autoprovision_other_pbx_header' => '<b>Achtung!</b> Das Telefonbuch muss auf jeder PBX unter der URI <b>/pbxcore/api/autoprovision-http/phonebook</b> erreichbar sein<br>Listen Sie alle Adressen der PBXen auf, von denen das Telefonbuch abgerufen werden soll.<br>',
    'mod_Autoprovision_templates_users_header' => 'Bei der Beschreibung einer MAC-Adresse ist die Verwendung des Symbols <b>%</b> zulässig, was „beliebiger Zeichensatz“ bedeutet <br>
Die Vorlage <b>805e0c67%</b> entspricht <b>805e0c670001</b> und <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Achtung!</b> Alle URIs werden relativ zum Basiswert <b>/pbxcore/api/autoprovision-http</b> aufgebaut<br>Bei der Beschreibung einer URI kann das Symbol <b>%</b> verwendet werden — es bedeutet „beliebige Zeichenfolge“.<br>Die URI <b>/%/%/test.cfg</b> entspricht <b>/1/2/test.cfg</b> und <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Wenn das Modul aktiviert ist, wird das SIP-Konto „<b>apv-miko-pbx</b>“ auf der TK-Anlage verfügbar.
<br>Um Ihr Telefon automatisch zu konfigurieren, müssen Sie es auf die Werkseinstellungen zurücksetzen.
<br>Wenn das Telefon zum ersten Mal eine Verbindung zur Telefonanlage herstellt, wird es im Konto „<b>apv-miko-pbx</b>“ registriert.
<br>Um das Telefon zu konfigurieren, müssen Sie von dort aus „<b>%extension%</b>“ anrufen, wobei XXX die interne Nummer der Telefonanlage ist.
<br><br>
Die automatische Konfiguration ist nur für das lokale Netzwerk des Unternehmens und für Telefone <b>Yealink, Snom, Fanvil</b> möglich.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Laden Sie Firmware-Dateien hoch, die die PBX über HTTP vom Provisionierungs-Port an Telefone ausliefert. Verwenden Sie in Ihren Vorlagen den Platzhalter <b>{FIRMWARE_URL}</b>, um die Download-URL einzufügen.',
    'mod_Autoprovision_firmware_drop_hint' => 'Firmware-Datei hierher ziehen oder klicken, um auszuwählen',
    'mod_Autoprovision_firmware_browse' => 'Datei auswählen',
    'mod_Autoprovision_firmware_vendor' => 'Hersteller',
    'mod_Autoprovision_firmware_model' => 'Modell',
    'mod_Autoprovision_firmware_version' => 'Version',
    'mod_Autoprovision_firmware_notes' => 'Notizen',
    'mod_Autoprovision_firmware_filename' => 'Datei',
    'mod_Autoprovision_firmware_size' => 'Größe',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Firmware-Metadaten bearbeiten',
    'mod_Autoprovision_firmware_save' => 'Speichern',
    'mod_Autoprovision_firmware_cancel' => 'Abbrechen',
];
