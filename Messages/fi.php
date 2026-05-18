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
    'repModuleAutoprovision' => 'Moduuli - %represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - puhelinten asetusten toimitus HTTP:n yli',
    'fw_moduleautoprovisionDescriptionHint' => 'Erillinen TCP-portti, jota ModuleAutoprovision palvelee tavallisella HTTP:llä (ei HTTPS-uudelleenohjausta).<br>IP-puhelimet hakevat konfiguraatiotiedostonsa tästä portista provisioinnin aikana.<br>Avaa pääsy vain sille paikallisverkolle, jossa puhelimet ovat.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — TFTP-palvelin (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'UDP/69:ää palvelee moduulin sisäänrakennettu, puhtaalla PHP:llä toteutettu TFTP-palvelin.<br>Käyttävät puhelimet ja firmwaret, jotka suosivat DHCP option 66 (TFTP) -vaihtoehtoa multicast PnP:n sijaan — historiallisesti Snom, jotkin Fanvil-firmwaret sekä reititetyt verkot, joissa multicast ei ylitä L3-rajaa.<br><b>Selkoteksti-protokolla:</b> avaa portti vain siinä LAN-segmentissä, jossa puhelimet sijaitsevat.',
    'mo_ModuleAutoprovision' => 'Automaattinen puhelimen konfigurointimoduuli',
    'BreadcrumbModuleAutoprovision' => 'Automaattinen puhelimen konfigurointimoduuli',
    'SubHeaderModuleAutoprovision' => 'Apua SIP-puhelimien käyttöönotossa',
    'mod_Autoprovision_Extension' => 'Laajennusnumeromalli',
    'mod_Autoprovision_pbx_host' => 'Palvelimen osoite puhelimitse rekisteröitymistä varten',
    'mod_Autoprovision_http_port' => 'Autoprovisioinnin HTTP-portti',
    'mod_Autoprovision_http_port_hint' => 'Moduulin oma TCP-portti konfiguraatioiden jakeluun puhtaan HTTP:n yli — ei kuulu yleisen HTTPS-uudelleenohjauksen piiriin. Puhelinten tulee yhdistää PBX:ään tämän portin kautta; palomuurisääntö lisätään automaattisesti.',
    'mod_Autoprovision_mac_black' => 'Musta lista puhelinten MAC-osoitteista',
    'mod_Autoprovision_mac_white' => 'Valkoinen luettelo puhelinten MAC-osoitteista',
    'mod_Autoprovision_additional_params' => 'Lisävaihtoehdot',
    'mod_Autoprovision_tftp_enabled' => 'Ota TFTP-provisiointi käyttöön (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Käynnistää puhtaaseen PHP:hen perustuvan TFTP-palvelimen UDP-porttiin 69, joka jakaa samat MAC-kohtaiset konfiguraatiot kuin HTTP-kanava, sekä firmware-tiedostot «Firmware»-välilehdeltä.<br>Hyödyllinen, kun multicast PnP on estetty (tyypillistä toimisto-WiFissä ja reititetyissä verkoissa) tai kun puhelin suosii DHCP option 66 -vaihtoehtoa (Snom, jotkin Fanvil-firmwaret).<br>Ei ulkoisia binäärejä — toimii moduulin workerin sisällä. <b>Selkoteksti-protokolla</b>: ota käyttöön vain luotettavassa LANissa. UDP/69:n palomuurisääntö avautuu automaattisesti.',
    'mod_Autoprovision_phone_settings_title' => 'Puhelimen asetukset',
    'mod_Autoprovision_phone_templates' => 'Asetusmallit',
    'mod_Autoprovision_general_settings' => 'URI-asetukset',
    'mod_Autoprovision_pnp' => 'PnP-asetukset',
    'mod_Autoprovision_addNew' => 'Lisätä',
    'mod_Autoprovision_load_examples' => 'Lataa esimerkkimallit',
    'mod_Autoprovision_load_examples_hint' => 'Lisää sisäänrakennetut esimerkkimallit Yealinkille, Fanvilille, Snomille, Grandstreamille ja Htekille. Mallit, joiden nimi on jo olemassa, ohitetaan, joten painiketta voi painaa toistuvasti ilman päällekkäisyyksien riskiä.',
    'mod_Autoprovision_load_examples_installed' => 'Esimerkkimallit asennettu',
    'mod_Autoprovision_load_examples_already_present' => 'Kaikki esimerkkimallit on jo asennettu.',
    'mod_Autoprovision_load_examples_failed' => 'Esimerkkimallien asennus epäonnistui',
    'mod_Autoprovision_load_examples_partial' => 'Osa esimerkkimalleista jäi asentamatta',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Sivulla on tallentamattomia muutoksia. Esimerkkien lataaminen lataa sivun uudelleen ja hylkää ne. Jatketaanko?',
    'mod_Autoprovision_load_examples_post_only' => 'POST-pyyntö vaaditaan.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Näyte',
    'mod_Autoprovision_phone_settings_user' => 'Työntekijä',
    'mod_Autoprovision_phone_settings_mac' => 'MAC-osoite',
    'mod_Autoprovision_template_name' => 'Nimi',
    'mod_Autoprovision_search_tags' => 'Haku...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Mallin muokkaaminen',
    'mod_Autoprovision_end_edit_template' => 'Viimeistele muokkaus',
    'mod_Autoprovision_other_pbx' => 'Puhelinluettelo',
    'mod_Autoprovision_other_pbx_name' => 'Puhelinkeskuksen nimi',
    'mod_Autoprovision_other_pbx_address' => 'PBX-verkko-osoite',
    'mod_Autoprovision_templates_header' => 'Mallia kuvattaessa voit käyttää seuraavia parametreja: <b>{SIP_USER_NAME}</b> - työntekijän nimi <b>{SIP_NUM}</b> - sisäinen numero (kirjautumistunnus) <b>{SIP_PASS}</b> - salasana',
    'mod_Autoprovision_other_pbx_header' => '<b>Huomio!</b> Puhelinluettelon tulee olla jokaisesta PBX:stä saavutettavissa URI:ssä <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Listaa kaikki niiden PBX:ien osoitteet, joista puhelinluettelo on noudettava.<br>',
    'mod_Autoprovision_templates_users_header' => 'MAC-osoitetta kuvattaessa on sallittua käyttää symbolia <b>%</b> - mikä tarkoittaa "mitä tahansa merkkijoukkoa" <br>
Kuvio <b>805e0c67%</b> vastaa <b>805e0c670001</b> ja <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Huomio!</b> Kaikki URI:t rakennetaan suhteessa perusarvoon <b>/pbxcore/api/autoprovision-http</b><br>URI:n kuvauksessa voidaan käyttää merkkiä <b>%</b> — merkitsee „mitä tahansa merkkijonoa“.<br>URI <b>/%/%/test.cfg</b> vastaa kohteita <b>/1/2/test.cfg</b> ja <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Jos moduuli on käytössä, SIP-tili "<b>apv-miko-pbx</b>" tulee saataville PBX:ssä
<br>Jos haluat määrittää puhelimen automaattisesti, sinun on palautettava sen tehdasasetukset.
<br>Jos puhelin muodostaa yhteyden PBX:ään ensimmäistä kertaa, se rekisteröidään tilille <b>apv-miko-pbx</b>.
<br>Jotta voit määrittää puhelimesi, sinun on soitettava siitä numeroon <b>%extension%</b>, jossa XXX on PBX:n sisäinen numero.
<br><br>
Automaattinen määritys on mahdollista vain yrityksen paikallisverkossa <b>Yealink-, Snom-, Fanvil</b>-puhelimissa.',
    'mod_Autoprovision_firmware' => 'Firmwaret',
    'mod_Autoprovision_firmware_header' => 'Lataa firmware-tiedostoja, jotka PBX jakaa puhelimille HTTP:n kautta provisiointiportista. Käytä malleissasi paikkamerkkiä <b>{FIRMWARE_URL}</b> latauslinkin lisäämiseksi.',
    'mod_Autoprovision_firmware_drop_hint' => 'Vedä firmware-tiedosto tähän tai napsauta valitaksesi',
    'mod_Autoprovision_firmware_browse' => 'Valitse tiedosto',
    'mod_Autoprovision_firmware_vendor' => 'Valmistaja',
    'mod_Autoprovision_firmware_model' => 'Malli',
    'mod_Autoprovision_firmware_version' => 'Versio',
    'mod_Autoprovision_firmware_notes' => 'Muistiinpanot',
    'mod_Autoprovision_firmware_filename' => 'Tiedosto',
    'mod_Autoprovision_firmware_size' => 'Koko',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Muokkaa firmwaren metatietoja',
    'mod_Autoprovision_firmware_save' => 'Tallenna',
    'mod_Autoprovision_firmware_cancel' => 'Peruuta',
];
