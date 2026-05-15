<?php
return [
    /*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */
    'mod_Autoprovision_additional_params' => 'Lisävaihtoehdot',
    'mod_Autoprovision_phone_settings_title' => 'Puhelimen asetukset',
    'mod_Autoprovision_phone_templates' => 'Asetusmallit',
    'mod_Autoprovision_general_settings' => 'URI-asetukset',
    'mod_Autoprovision_pnp' => 'PnP-asetukset',
    'mod_Autoprovision_addNew' => 'Lisätä',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Näyte',
    'mod_Autoprovision_phone_settings_user' => 'Työntekijä',
    'mod_Autoprovision_phone_settings_mac' => 'MAC-osoite',
    'mod_Autoprovision_template_name' => 'Nimi',
    'mod_Autoprovision_search_tags' => 'Haku...',
    'mod_Autoprovision_edit_template' => 'Mallin muokkaaminen',
    'mod_Autoprovision_end_edit_template' => 'Viimeistele muokkaus',
    'mod_Autoprovision_other_pbx' => 'Puhelinluettelo',
    'mod_Autoprovision_other_pbx_name' => 'Puhelinkeskuksen nimi',
    'mod_Autoprovision_other_pbx_address' => 'PBX-verkko-osoite',
    'mod_Autoprovision_templates_header' => 'Mallia kuvattaessa voit käyttää seuraavia parametreja: <b>{SIP_USER_NAME}</b> - työntekijän nimi <b>{SIP_NUM}</b> - sisäinen numero (kirjautumistunnus) <b>{SIP_PASS}</b> - salasana',
    'mod_Autoprovision_templates_users_header' => 'MAC-osoitetta kuvattaessa on sallittua käyttää symbolia <b>%</b> - mikä tarkoittaa "mitä tahansa merkkijoukkoa" <br>
Kuvio <b>805e0c67%</b> vastaa <b>805e0c670001</b> ja <b>805e0c670002</b>',
    'mod_Autoprovision_header' => 'Jos moduuli on käytössä, SIP-tili "<b>apv-miko-pbx</b>" tulee saataville PBX:ssä
<br>Jos haluat määrittää puhelimen automaattisesti, sinun on palautettava sen tehdasasetukset.
<br>Jos puhelin muodostaa yhteyden PBX:ään ensimmäistä kertaa, se rekisteröidään tilille <b>apv-miko-pbx</b>.
<br>Jotta voit määrittää puhelimesi, sinun on soitettava siitä numeroon <b>%extension%</b>, jossa XXX on PBX:n sisäinen numero.
<br><br>
Automaattinen määritys on mahdollista vain yrityksen paikallisverkossa <b>Yealink-, Snom-, Fanvil</b>-puhelimissa.',
    'repModuleAutoprovision' => 'Moduuli - %represent%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - puhelinten asetusten toimitus HTTP:n yli',
    'fw_moduleautoprovisionDescriptionHint' => 'Erillinen TCP-portti, jota ModuleAutoprovision palvelee tavallisella HTTP:llä (ei HTTPS-uudelleenohjausta).<br>IP-puhelimet hakevat konfiguraatiotiedostonsa tästä portista provisioinnin aikana.<br>Avaa pääsy vain sille paikallisverkolle, jossa puhelimet ovat.',
    'mo_ModuleAutoprovision' => 'Automaattinen puhelimen konfigurointimoduuli',
    'BreadcrumbModuleAutoprovision' => 'Automaattinen puhelimen konfigurointimoduuli',
    'SubHeaderModuleAutoprovision' => 'Apua SIP-puhelimien käyttöönotossa',
    'mod_Autoprovision_Extension' => 'Laajennusnumeromalli',
    'mod_Autoprovision_pbx_host' => 'Palvelimen osoite puhelimitse rekisteröitymistä varten',
    'mod_Autoprovision_mac_black' => 'Musta lista puhelinten MAC-osoitteista',
    'mod_Autoprovision_mac_white' => 'Valkoinen luettelo puhelinten MAC-osoitteista',
    'mod_Autoprovision_filter_posts' => 'Select…',
];
