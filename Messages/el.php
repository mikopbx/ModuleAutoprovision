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
    'repModuleAutoprovision' => 'Ενότητα Autoprovision',
    'fw_moduleautoprovisionDescription' => 'Autoprovision - παράδοση ρυθμίσεων τηλεφώνων μέσω HTTP',
    'fw_moduleautoprovisionDescriptionHint' => 'Αποκλειστική θύρα TCP που εξυπηρετείται από το ModuleAutoprovision μέσω απλού HTTP (χωρίς ανακατεύθυνση σε HTTPS).<br>Τα IP τηλέφωνα κατεβάζουν τα αρχεία ρυθμίσεών τους από αυτή τη θύρα κατά την αυτόματη ρύθμιση.<br>Ανοίξτε την πρόσβαση μόνο για το τοπικό δίκτυο στο οποίο βρίσκονται τα τηλέφωνα.',
    'fw_AutoprovisionTftpPortDescription' => 'Autoprovision — Διακομιστής TFTP (UDP/69)',
    'fw_AutoprovisionTftpPortDescriptionHint' => 'Το UDP/69 εξυπηρετείται από τον ενσωματωμένο διακομιστή TFTP σε καθαρή PHP.<br>Χρησιμοποιείται από τηλέφωνα και firmware που προτιμούν το DHCP option 66 (TFTP) έναντι του multicast PnP — ιστορικά Snom, ορισμένα firmware Fanvil, καθώς και δρομολογημένα δίκτυα όπου το multicast δεν διασχίζει το όριο L3.<br><b>Πρωτόκολλο απλού κειμένου:</b> ανοίξτε τη θύρα μόνο στο τμήμα LAN όπου βρίσκονται τα τηλέφωνα.',
    'mo_ModuleAutoprovision' => 'Μονάδα αυτόματης διαμόρφωσης τηλεφώνου',
    'BreadcrumbModuleAutoprovision' => 'Μονάδα αυτόματης διαμόρφωσης τηλεφώνου',
    'SubHeaderModuleAutoprovision' => 'Βοήθεια στη ρύθμιση τηλεφώνων SIP',
    'mod_Autoprovision_Extension' => 'Πρότυπο επέκτασης',
    'mod_Autoprovision_pbx_host' => 'Διεύθυνση διακομιστή για εγγραφή τηλεφώνου',
    'mod_Autoprovision_http_port' => 'Θύρα HTTP για autoprovisioning',
    'mod_Autoprovision_http_port_hint' => 'Ξεχωριστή θύρα TCP του ενότητας για παροχή ρυθμίσεων μέσω καθαρού HTTP — δεν υπόκειται στην καθολική ανακατεύθυνση HTTPS. Τα τηλέφωνα πρέπει να επικοινωνούν με το PBX σε αυτή τη θύρα· ο κανόνας firewall προστίθεται αυτόματα.',
    'mod_Autoprovision_mac_black' => 'Μαύρη λίστα MAC τηλεφώνου',
    'mod_Autoprovision_mac_white' => 'Λευκή λίστα διευθύνσεων MAC τηλεφώνου',
    'mod_Autoprovision_additional_params' => 'Επιπλέον επιλογές',
    'mod_Autoprovision_tftp_enabled' => 'Ενεργοποίηση provisioning μέσω TFTP (UDP/69)',
    'mod_Autoprovision_tftp_enabled_hint' => 'Εκκινεί τον ενσωματωμένο διακομιστή TFTP σε καθαρή PHP (UDP/69), που παρέχει τις ίδιες ρυθμίσεις ανά MAC με το κανάλι HTTP, καθώς και αρχεία firmware από την καρτέλα «Firmware».<br>Χρήσιμο όταν το multicast PnP είναι αποκλεισμένο (τυπικό σε WiFi γραφείου και δρομολογημένα δίκτυα) ή όταν το τηλέφωνο προτιμά το DHCP option 66 (Snom, ορισμένα firmware Fanvil).<br>Χωρίς εξωτερικά εκτελέσιμα — εκτελείται μέσα στον worker του module. <b>Πρωτόκολλο απλού κειμένου</b>: ενεργοποιήστε το μόνο σε αξιόπιστο LAN. Ο κανόνας firewall για UDP/69 ανοίγει αυτόματα.',
    'mod_Autoprovision_phone_settings_title' => 'Ρυθμίσεις τηλεφώνου',
    'mod_Autoprovision_phone_templates' => 'Πρότυπα ρυθμίσεων',
    'mod_Autoprovision_general_settings' => 'Ρυθμίσεις URI',
    'mod_Autoprovision_pnp' => 'Ρυθμίσεις PnP',
    'mod_Autoprovision_addNew' => 'Προσθήκη',
    'mod_Autoprovision_load_examples' => 'Φόρτωση παραδειγμάτων προτύπων',
    'mod_Autoprovision_load_examples_hint' => 'Προσθέτει τα ενσωματωμένα παραδείγματα προτύπων για Yealink, Fanvil, Snom, Grandstream και Htek. Πρότυπα με υπάρχοντα ονόματα παρακάμπτονται, οπότε το κουμπί μπορεί να πατηθεί επανειλημμένα χωρίς κίνδυνο διπλοτύπων.',
    'mod_Autoprovision_load_examples_installed' => 'Τα παραδείγματα προτύπων εγκαταστάθηκαν',
    'mod_Autoprovision_load_examples_already_present' => 'Όλα τα παραδείγματα προτύπων είναι ήδη εγκατεστημένα.',
    'mod_Autoprovision_load_examples_failed' => 'Αποτυχία εγκατάστασης παραδειγμάτων προτύπων',
    'mod_Autoprovision_load_examples_partial' => 'Μέρος των παραδειγμάτων προτύπων δεν εγκαταστάθηκε',
    'mod_Autoprovision_load_examples_unsaved_warning' => 'Η σελίδα έχει μη αποθηκευμένες αλλαγές. Η φόρτωση των παραδειγμάτων θα επαναφορτώσει τη σελίδα και θα τις αναιρέσει. Συνέχεια;',
    'mod_Autoprovision_load_examples_post_only' => 'Απαιτείται αίτημα POST.',
    'mod_Autoprovision_templates_uri_uri' => 'URI',
    'mod_Autoprovision_templates_uri_template' => 'Δείγμα',
    'mod_Autoprovision_phone_settings_user' => 'Υπάλληλος',
    'mod_Autoprovision_phone_settings_mac' => 'Διεύθυνση MAC',
    'mod_Autoprovision_template_name' => 'Ονομα',
    'mod_Autoprovision_search_tags' => 'Αναζήτηση...',
    'mod_Autoprovision_filter_posts' => 'Select…',
    'mod_Autoprovision_edit_template' => 'Επεξεργασία προτύπου',
    'mod_Autoprovision_end_edit_template' => 'Ολοκληρώστε την επεξεργασία',
    'mod_Autoprovision_other_pbx' => 'Τηλεφωνικό κατάλογο',
    'mod_Autoprovision_other_pbx_name' => 'Όνομα του τηλεφωνικού κέντρου',
    'mod_Autoprovision_other_pbx_address' => 'Διεύθυνση δικτύου PBX',
    'mod_Autoprovision_templates_header' => 'Κατά την περιγραφή ενός προτύπου, μπορείτε να χρησιμοποιήσετε τις ακόλουθες παραμέτρους: <b>{SIP_USER_NAME}</b> - όνομα υπαλλήλου <b>{SIP_NUM}</b> - εσωτερικός αριθμός (είσοδος) <b>{SIP_PASS}</b> - κωδικός πρόσβασης',
    'mod_Autoprovision_other_pbx_header' => '<b>Προσοχή!</b> Ο τηλεφωνικός κατάλογος πρέπει να είναι προσβάσιμος σε κάθε PBX στο URI <b>/pbxcore/api/autoprovision-http/phonebook</b><br>Αναγράψτε όλες τις διευθύνσεις των PBX από τα οποία πρέπει να ληφθεί ο τηλεφωνικός κατάλογος.<br>',
    'mod_Autoprovision_templates_users_header' => 'Κατά την περιγραφή μιας διεύθυνσης MAC, επιτρέπεται η χρήση του συμβόλου <b>%</b> - που σημαίνει "οποιοδήποτε σύνολο χαρακτήρων" <br>
Το πρότυπο <b>805e0c67%</b> θα ταιριάζει με <b>805e0c670001</b> και <b>805e0c670002</b>',
    'mod_Autoprovision_templates_uri_header' => '<b>Προσοχή!</b> Όλα τα URI κατασκευάζονται σχετικά με τη βασική τιμή <b>/pbxcore/api/autoprovision-http</b><br>Κατά την περιγραφή ενός URI επιτρέπεται η χρήση του συμβόλου <b>%</b> — που σημαίνει «οποιοδήποτε σύνολο χαρακτήρων».<br>Το URI <b>/%/%/test.cfg</b> θα αντιστοιχεί στο <b>/1/2/test.cfg</b> και στο <b>/test/test3/test.cfg</b>',
    'mod_Autoprovision_header' => 'Εάν η λειτουργική μονάδα είναι ενεργοποιημένη, ο λογαριασμός SIP "<b>apv-miko-pbx</b>" γίνεται διαθέσιμος στο PBX.
<br>Για να διαμορφώσετε αυτόματα το τηλέφωνό σας, πρέπει να το επαναφέρετε στις εργοστασιακές ρυθμίσεις.
<br>Εάν το τηλέφωνο συνδεθεί στο PBX για πρώτη φορά, θα εγγραφεί στον λογαριασμό "<b>apv-miko-pbx</b>".
<br>Για να διαμορφώσετε το τηλέφωνο, πρέπει να καλέσετε το "<b>%extension%</b>" από αυτό, όπου XXX είναι ο εσωτερικός αριθμός στο PBX.
<br><br>
Η αυτόματη διαμόρφωση είναι δυνατή μόνο για το τοπικό δίκτυο της επιχείρησης, για τηλέφωνα <b>Yealink, Snom, Fanvil</b>.',
    'mod_Autoprovision_firmware' => 'Firmware',
    'mod_Autoprovision_firmware_header' => 'Ανεβάστε αρχεία firmware που το PBX θα παρέχει στα τηλέφωνα μέσω HTTP από τη θύρα provisioning. Χρησιμοποιήστε στα πρότυπα τον placeholder <b>{FIRMWARE_URL}</b> για να εισαγάγετε το URL λήψης.',
    'mod_Autoprovision_firmware_drop_hint' => 'Σύρετε εδώ ένα αρχείο firmware ή κάντε κλικ για επιλογή',
    'mod_Autoprovision_firmware_browse' => 'Επιλογή αρχείου',
    'mod_Autoprovision_firmware_vendor' => 'Κατασκευαστής',
    'mod_Autoprovision_firmware_model' => 'Μοντέλο',
    'mod_Autoprovision_firmware_version' => 'Έκδοση',
    'mod_Autoprovision_firmware_notes' => 'Σημειώσεις',
    'mod_Autoprovision_firmware_filename' => 'Αρχείο',
    'mod_Autoprovision_firmware_size' => 'Μέγεθος',
    'mod_Autoprovision_firmware_sha256' => 'SHA-256',
    'mod_Autoprovision_firmware_edit_title' => 'Επεξεργασία μεταδεδομένων firmware',
    'mod_Autoprovision_firmware_save' => 'Αποθήκευση',
    'mod_Autoprovision_firmware_cancel' => 'Ακύρωση',
];
