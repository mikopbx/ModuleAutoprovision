<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;

return [
    'mod_Autoprovision_mac_black' => 'Liste noire des adresses MAC des téléphones',
    'mod_Autoprovision_pbx_host' => 'Adresse du serveur pour l\'enregistrement par téléphone',
    'mod_Autoprovision_Extension' => 'Modèle de numéro de poste',
    'mod_Autoprovision_additional_params' => 'Options supplémentaires',
    'mod_Autoprovision_mac_white' => 'Liste blanche des adresses MAC du téléphone',
    'SubHeaderModuleAutoprovision' => 'Aide à la configuration des téléphones SIP',
    'BreadcrumbModuleAutoprovision' => 'Module de configuration téléphonique automatique',
    'mo_ModuleAutoprovision' => 'Module de configuration téléphonique automatique',
    'repModuleAutoprovision' => 'Module -% représentant%',
    'mod_Autoprovision_header' => 'Si le module est activé, le compte SIP "<b>apv-miko-pbx</b>" devient disponible sur le PBX.
<br>Pour configurer automatiquement votre téléphone, vous devez le réinitialiser aux paramètres d\'usine.
<br>Si le téléphone se connecte au PBX pour la première fois, il sera enregistré sur le compte "<b>apv-miko-pbx</b>".
<br>Pour configurer le téléphone, vous devez appeler "<b>%extension%</b>" depuis celui-ci, où XXX est le numéro interne du PBX.
<br><br>
L\'autoconfiguration n\'est possible que pour le réseau local de l\'entreprise, pour les téléphones <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_phone_settings_title' => 'Les paramètres du téléphone',
    'mod_Autoprovision_phone_templates' => 'Modèles de paramètres',
    'mod_Autoprovision_general_settings' => 'Paramètres d\'URI',
    'mod_Autoprovision_pnp' => 'Paramètres PnP',
    'mod_Autoprovision_addNew' => 'Ajouter',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Échantillon',
    'mod_Autoprovision_phone_settings_user' => 'Employé',
    'mod_Autoprovision_phone_settings_mac' => 'Adresse Mac',
    'mod_Autoprovision_template_name' => 'Nom',
    'mod_Autoprovision_search_tags' => 'Recherche...',
    'mod_Autoprovision_edit_template' => 'Modification d\'un modèle',
    'mod_Autoprovision_end_edit_template' => 'Terminer la modification',
    'mod_Autoprovision_other_pbx' => 'Annuaire',
    'mod_Autoprovision_other_pbx_name' => 'Nom du central téléphonique',
    'mod_Autoprovision_other_pbx_address' => 'Adresse du réseau PBX',
    'mod_Autoprovision_templates_header' => 'Lors de la description d\'un modèle, vous pouvez utiliser les paramètres suivants : <b>{SIP_USER_NAME}</b> - nom de l\'employé <b>{SIP_NUM}</b> - numéro interne (login) <b>{SIP_PASS}</b> - mot de passe',
    'mod_Autoprovision_templates_users_header' => 'Lors de la description d\'une adresse MAC, il est permis d\'utiliser le symbole <b>%</b> - signifiant « n\'importe quel jeu de caractères » <br>
Le modèle <b>805e0c67%</b> correspondra à <b>805e0c670001</b> et <b>805e0c670002</b>',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_other_pbx_header' => '<b>Warning!</b> The phone book must be accessible on every PBX at the URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>
List every address of the PBXes from which the phone book should be fetched.<br>',
    'mod_Autoprovision_templates_uri_header' => '<b>Warning!</b> All URIs are resolved relative to the base value <b>/pbxcore/api/autoprovision-http</b><br>
When describing a URI you may use the symbol <b>%</b> meaning "any set of characters". <br>
The URI <b>/%/%/test.cfg</b> will match <b>/1/2/test.cfg</b> and <b>/test/test3/test.cfg</b>',
];
