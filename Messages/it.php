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
    'repModuleAutoprovision' => 'Modulo -% rappresentante%',
    'fw_moduleautoprovisionDescription' => 'Autoprovisioning - consegna della configurazione dei telefoni via HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Porta TCP dedicata servita da ModuleAutoprovision su HTTP semplice (senza redirect HTTPS).<br>I telefoni IP scaricano i file di configurazione da questa porta durante il provisioning.<br>Consentire il traffico solo dalla rete locale dei telefoni.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — Server TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'L\'UDP/69 è gestito dal server TFTP integrato nel modulo, scritto in PHP puro.<br>Utilizzato da telefoni e firmware che preferiscono DHCP option 66 (TFTP) al PnP multicast — storicamente Snom, alcuni firmware Fanvil e reti instradate dove il multicast non attraversa il confine L3.<br><b>Protocollo in chiaro:</b> aprire la porta solo nel segmento LAN in cui si trovano i telefoni.',
    'mo_ModuleAutoprovision' => 'Modulo di configurazione automatica del telefono',
    'BreadcrumbModuleAutoprovision' => 'Modulo di configurazione automatica del telefono',
    'SubHeaderModuleAutoprovision' => 'Aiuto nella configurazione dei telefoni SIP',
    'mod_Autoprovision_Extension' => 'Modello numero interno',
    'mod_Autoprovision_pbx_host' => 'Indirizzo del server per la registrazione del telefono',
    'mod_Autoprovision_http_port' => 'Porta HTTP per l\'autoprovisioning',
    'mod_Autoprovision_http_port_hint' => 'Porta TCP dedicata del modulo per la distribuzione delle configurazioni via HTTP puro — non è soggetta al reindirizzamento HTTPS globale. I telefoni devono contattare il PBX su questa porta; la regola del firewall viene aggiunta automaticamente.',
    'mod_Autoprovision_mac_black' => 'Lista nera degli indirizzi MAC dei telefoni',
    'mod_Autoprovision_mac_white' => 'Elenco indirizzi MAC del telefono',
    'mod_Autoprovision_additional_params' => 'Opzioni aggiuntive',
    'mod_Autoprovision_tftp_enabled' => 'Abilita provisioning via TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Avvia un server TFTP in PHP puro su UDP/69 che fornisce le stesse configurazioni per MAC del canale HTTP, oltre ai firmware dalla scheda «Firmware».<br>Utile quando il PnP multicast è bloccato (tipico nelle WiFi d\'ufficio e nelle reti instradate) oppure quando il telefono preferisce DHCP option 66 (Snom, alcuni firmware Fanvil).<br>Nessun binario esterno — gira all\'interno del worker del modulo. <b>Protocollo in chiaro</b>: abilitare solo su una LAN affidabile. La regola del firewall per UDP/69 viene aperta automaticamente.',
    'mod_Autoprovision_phone_settings_title' => 'Impostazioni del telefono',
    'mod_Autoprovision_phone_templates' => 'Modelli di impostazioni',
    'mod_Autoprovision_general_settings' => 'Impostazioni dell\'URI',
    'mod_Autoprovision_pnp' => 'Impostazioni PnP',
    'mod_Autoprovision_addNew' => 'Aggiungere',
    'mod_Autoprovision_load_examples' => 'Carica modelli di esempio',
    'mod_Autoprovision_load_examples_hint' => 'Aggiunge i modelli di esempio inclusi per Yealink, Fanvil, Snom, Grandstream e Htek. I modelli con nomi già esistenti vengono saltati, quindi il pulsante può essere premuto più volte senza rischio di duplicati.',
    'mod_Autoprovision_load_examples_installed' => 'Modelli di esempio installati',
    'mod_Autoprovision_load_examples_already_present' => 'Tutti i modelli di esempio sono già installati.',
    'mod_Autoprovision_load_examples_failed' => 'Impossibile installare i modelli di esempio',
    'mod_Autoprovision_load_examples_partial' => 'Alcuni modelli di esempio non sono stati installati',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Nella pagina sono presenti modifiche non salvate. Il caricamento degli esempi ricaricherà la pagina e le annullerà. Continuare?',
    'mod_Autoprovision_load_examples_post_only' => 'È richiesta una richiesta POST.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Campione',
    'mod_Autoprovision_phone_settings_user' => 'Dipendente',
    'mod_Autoprovision_phone_settings_mac' => 'Indirizzo MAC',
    'mod_Autoprovision_template_name' => 'Nome',
    'mod_Autoprovision_search_tags' => 'Ricerca...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Modifica di un modello',
    'mod_Autoprovision_end_edit_template' => 'Termina la modifica',
    'mod_Autoprovision_other_pbx' => 'Rubrica telefonica',
    'mod_Autoprovision_other_pbx_name' => 'Nome della centrale telefonica',
    'mod_Autoprovision_other_pbx_address' => 'Indirizzo di rete PBX',
    'mod_Autoprovision_templates_header' => 'Quando si descrive un modello, è possibile utilizzare i seguenti parametri: <b>{SIP_USER_NAME}</b> - nome del dipendente <b>{SIP_NUM}</b> - numero interno (login) <b>{SIP_PASS}</b> - password',
    'mod_Autoprovision_other_pbx_header' => '<b>Attenzione!</b> La rubrica deve essere accessibile su ogni PBX all\'URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Elencare tutti gli indirizzi dei PBX dai quali deve essere recuperata la rubrica.<br>',
    'mod_Autoprovision_templates_users_header' => 'Quando si descrive un indirizzo MAC, è consentito utilizzare il simbolo <b>%</b> - che significa "qualsiasi insieme di caratteri" <br>
Il modello <b>805e0c67%</b> corrisponderà a <b>805e0c670001</b> e <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Attenzione!</b> Tutti gli URI sono costruiti rispetto al valore di base <b>/pbxcore/api/autoprovision-http</b><br>Nella descrizione di un URI è consentito usare il simbolo <b>%</b> — che significa „qualsiasi sequenza di caratteri“.<br>L\'URI <b>/%/%/test.cfg</b> corrisponderà a <b>/1/2/test.cfg</b> e a <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Se il modulo è abilitato, sul PBX diventa disponibile l\'account SIP "<b>apv-miko-pbx</b>".
<br>Per configurare automaticamente il telefono, è necessario ripristinarlo alle impostazioni di fabbrica.
<br>Se il telefono si connette al PBX per la prima volta, verrà registrato sull\'account "<b>apv-miko-pbx</b>".
<br>Per configurare il telefono, è necessario chiamare da esso "<b>%interno%</b>", dove XXX è il numero interno del PBX.
<br><br>
La configurazione automatica è possibile solo per la rete locale dell\'azienda, per i telefoni <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Caricate i file firmware che il PBX servirà ai telefoni via HTTP dalla porta di provisioning. Usate nei vostri modelli il segnaposto <b>{FIRMWARE_URL}</b> per inserire l\'URL di download.',
    'mod_Autoprovision_firmware_drop_hint' => 'Trascina qui un file firmware o clicca per selezionarlo',
    'mod_Autoprovision_firmware_browse' => 'Scegli file',
    'mod_Autoprovision_firmware_vendor' => 'Produttore',
    'mod_Autoprovision_firmware_model' => 'Modello',
    'mod_Autoprovision_firmware_version' => 'Versione',
    'mod_Autoprovision_firmware_notes' => 'Note',
    'mod_Autoprovision_firmware_filename' => 'File',
    'mod_Autoprovision_firmware_size' => 'Dimensione',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Modifica metadati firmware',
    'mod_Autoprovision_firmware_save' => 'Salva',
    'mod_Autoprovision_firmware_cancel' => 'Annulla',
];
