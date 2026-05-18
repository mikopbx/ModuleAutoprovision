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
    'repModuleAutoprovision' => 'Module -% représentant%',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - distribution de la configuration des téléphones via HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Port TCP dédié servi par ModuleAutoprovision en HTTP simple (pas de redirection HTTPS).<br>Les téléphones IP récupèrent leurs fichiers de configuration depuis ce port pendant le provisioning.<br>Autorisez uniquement le réseau local dans lequel se trouvent les téléphones.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — Serveur TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'Le port UDP/69 est servi par le serveur TFTP intégré au module, écrit en PHP pur.<br>Utilisé par les téléphones et firmwares qui préfèrent DHCP option 66 (TFTP) au PnP multicast — historiquement Snom, certains firmwares Fanvil, ainsi que les réseaux routés où le multicast ne franchit pas la frontière L3.<br><b>Protocole en clair :</b> n\'ouvrez le port que dans le segment LAN où se trouvent les téléphones.',
    'mo_ModuleAutoprovision' => 'Module de configuration téléphonique automatique',
    'BreadcrumbModuleAutoprovision' => 'Module de configuration téléphonique automatique',
    'SubHeaderModuleAutoprovision' => 'Aide à la configuration des téléphones SIP',
    'mod_Autoprovision_Extension' => 'Modèle de numéro de poste',
    'mod_Autoprovision_pbx_host' => 'Adresse du serveur pour l\'enregistrement par téléphone',
    'mod_Autoprovision_http_port' => 'Port HTTP d\'autoprovisionnement',
    'mod_Autoprovision_http_port_hint' => 'Port TCP dédié du module pour la diffusion des configurations en HTTP pur — non soumis à la redirection globale vers HTTPS. Les téléphones doivent joindre le PBX sur ce port ; la règle de pare-feu est ajoutée automatiquement.',
    'mod_Autoprovision_mac_black' => 'Liste noire des adresses MAC des téléphones',
    'mod_Autoprovision_mac_white' => 'Liste blanche des adresses MAC du téléphone',
    'mod_Autoprovision_additional_params' => 'Options supplémentaires',
    'mod_Autoprovision_tftp_enabled' => 'Activer le provisionnement TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Démarre un serveur TFTP en PHP pur sur UDP/69 qui sert les mêmes configurations par MAC que le canal HTTP, ainsi que les firmwares de l\'onglet «Firmware».<br>Utile lorsque le PnP multicast est bloqué (typique du WiFi de bureau et des réseaux routés) ou lorsque le téléphone préfère DHCP option 66 (Snom, certains firmwares Fanvil).<br>Aucun binaire externe — s\'exécute dans le worker du module. <b>Protocole en clair</b> : à n\'activer que dans un LAN de confiance. La règle de pare-feu pour UDP/69 est ouverte automatiquement.',
    'mod_Autoprovision_phone_settings_title' => 'Les paramètres du téléphone',
    'mod_Autoprovision_phone_templates' => 'Modèles de paramètres',
    'mod_Autoprovision_general_settings' => 'Paramètres d\'URI',
    'mod_Autoprovision_pnp' => 'Paramètres PnP',
    'mod_Autoprovision_addNew' => 'Ajouter',
    'mod_Autoprovision_load_examples' => 'Charger les modèles d\'exemple',
    'mod_Autoprovision_load_examples_hint' => 'Ajoute les modèles d\'exemple intégrés pour Yealink, Fanvil, Snom, Grandstream et Htek. Les modèles dont les noms existent déjà sont ignorés, le bouton peut donc être cliqué plusieurs fois sans risque de doublons.',
    'mod_Autoprovision_load_examples_installed' => 'Modèles d\'exemple installés',
    'mod_Autoprovision_load_examples_already_present' => 'Tous les modèles d\'exemple sont déjà installés.',
    'mod_Autoprovision_load_examples_failed' => 'Échec de l\'installation des modèles d\'exemple',
    'mod_Autoprovision_load_examples_partial' => 'Certains modèles d\'exemple n\'ont pas été installés',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Des modifications non enregistrées sont présentes sur la page. Le chargement des exemples rechargera la page et les annulera. Continuer ?',
    'mod_Autoprovision_load_examples_post_only' => 'Requête POST requise.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Échantillon',
    'mod_Autoprovision_phone_settings_user' => 'Employé',
    'mod_Autoprovision_phone_settings_mac' => 'Adresse Mac',
    'mod_Autoprovision_template_name' => 'Nom',
    'mod_Autoprovision_search_tags' => 'Recherche...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Modification d\'un modèle',
    'mod_Autoprovision_end_edit_template' => 'Terminer la modification',
    'mod_Autoprovision_other_pbx' => 'Annuaire',
    'mod_Autoprovision_other_pbx_name' => 'Nom du central téléphonique',
    'mod_Autoprovision_other_pbx_address' => 'Adresse du réseau PBX',
    'mod_Autoprovision_templates_header' => 'Lors de la description d\'un modèle, vous pouvez utiliser les paramètres suivants : <b>{SIP_USER_NAME}</b> - nom de l\'employé <b>{SIP_NUM}</b> - numéro interne (login) <b>{SIP_PASS}</b> - mot de passe',
    'mod_Autoprovision_other_pbx_header' => '<b>Attention !</b> L\'annuaire téléphonique doit être accessible sur chaque PBX via l\'URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Listez toutes les adresses des PBX dont l\'annuaire doit être récupéré.<br>',
    'mod_Autoprovision_templates_users_header' => 'Lors de la description d\'une adresse MAC, il est permis d\'utiliser le symbole <b>%</b> - signifiant « n\'importe quel jeu de caractères » <br>
Le modèle <b>805e0c67%</b> correspondra à <b>805e0c670001</b> et <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Attention !</b> Toutes les URI sont construites par rapport à la valeur de base <b>/pbxcore/api/autoprovision-http</b><br>Lors de la description d\'une URI, le symbole <b>%</b> peut être utilisé — il signifie « toute séquence de caractères ».<br>L\'URI <b>/%/%/test.cfg</b> correspondra à <b>/1/2/test.cfg</b> et à <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Si le module est activé, le compte SIP "<b>apv-miko-pbx</b>" devient disponible sur le PBX.
<br>Pour configurer automatiquement votre téléphone, vous devez le réinitialiser aux paramètres d\'usine.
<br>Si le téléphone se connecte au PBX pour la première fois, il sera enregistré sur le compte "<b>apv-miko-pbx</b>".
<br>Pour configurer le téléphone, vous devez appeler "<b>%extension%</b>" depuis celui-ci, où XXX est le numéro interne du PBX.
<br><br>
L\'autoconfiguration n\'est possible que pour le réseau local de l\'entreprise, pour les téléphones <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmwares',
    'mod_Autoprovision_firmware_header' => 'Téléversez des fichiers de firmware que le PBX servira aux téléphones via HTTP sur le port de provisionnement. Utilisez le marqueur <b>{FIRMWARE_URL}</b> dans vos modèles pour insérer l\'URL de téléchargement.',
    'mod_Autoprovision_firmware_drop_hint' => 'Glissez ici un fichier de firmware ou cliquez pour sélectionner',
    'mod_Autoprovision_firmware_browse' => 'Choisir un fichier',
    'mod_Autoprovision_firmware_vendor' => 'Fabricant',
    'mod_Autoprovision_firmware_model' => 'Modèle',
    'mod_Autoprovision_firmware_version' => 'Version',
    'mod_Autoprovision_firmware_notes' => 'Notes',
    'mod_Autoprovision_firmware_filename' => 'Fichier',
    'mod_Autoprovision_firmware_size' => 'Taille',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Modifier les métadonnées du firmware',
    'mod_Autoprovision_firmware_save' => 'Enregistrer',
    'mod_Autoprovision_firmware_cancel' => 'Annuler',
];
