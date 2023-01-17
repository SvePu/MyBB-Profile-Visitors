<?php

/**
 *  Language admin file for Profile Visitors plugin for MyBB 1.8
 *  Language: deutsch_sie
 *  Copyright © 2014 - 2023 SvePu
 *  Last change: 2023-01-17
 */

$l['profilevisitors'] = 'Profile Visitors';
$l['profilevisitors_desc'] = 'Mit diesem Plugin werden Besucher eines Mitglieder-Profils aufgelistet.';
$l['profilevisitors_uninstall'] = 'Profil Besucher - Deinstallation';
$l['profilevisitors_uninstall_message'] = 'Sollen auch alle Plugineinträge aus der Datenbank gelöscht werden?';

$l['setting_profilevisitors_main'] = 'Allgemeine Plugin Einstellungen';
$l['setting_profilevisitors_groups'] = 'Gruppenabhängige Plugin Einstellungen';

$l['setting_group_profilevisitors'] = 'Profil Besucher Einstellungen';
$l['setting_group_profilevisitors_desc'] = 'Einstellungen für das Profil Besucher Plugin';

$l['setting_profilevisitors_enable'] = 'Möchten Sie dieses Extra einschalten?';
$l['setting_profilevisitors_enable_desc'] = 'Wählen Sie JA zum Aktivieren! - Bitte beachten Sie auch die <a href="index.php?module=user-groups">gruppenabhängigen Profil Besucher Einstellungen</a> im "Sonstiges" Tab der Gruppeneinstellungen!';

$l['setting_profilevisitors_canviewown'] = 'Benutzer kann eigene Liste sehen?';
$l['setting_profilevisitors_canviewown_desc'] = 'Wählen Sie JA zum Aktivieren, wenn der Benutzer die eigene Liste sehen kann, auch wenn dieses in seiner Benutzergruppe deaktiviert ist!';

$l['setting_profilevisitors_limit'] = 'Höchstanzahl der Besucher in der Liste?';
$l['setting_profilevisitors_limit_desc'] = 'Setzen Sie ein Limit für die maximale Anzahl der Besucher, die in der Liste im Benutzer-Profil angezeigt werden sollen! (Standard: 10 - 0 deaktiviert die Begrenzung)';

$l['setting_profilevisitors_styled_usernames'] = 'Gruppenbasierenden Benutzernamen-Stil anzeigen?';
$l['setting_profilevisitors_styled_usernames_desc'] = 'Wählen Sie JA zum Aktivieren!';

$l['setting_profilevisitors_allvisits'] = 'Anzahl aller Besucher anzeigen?';
$l['setting_profilevisitors_allvisits_desc'] = 'Wählen Sie JA um die Anzahl aller Besucher anzuzeigen!';

$l['setting_profilevisitors_overviewpage_enable'] = 'Profilbesucher-Seite aktivieren?';
$l['setting_profilevisitors_overviewpage_enable_desc'] = 'Wählen Sie JA um die Profilbesucher-Seite zu aktivieren und einen Link in der Liste im Benutzerprofil einzufügen!';

$l['setting_profilevisitors_overviewpage_perpage'] = 'Anzahl der Einträge pro Seite?';
$l['setting_profilevisitors_overviewpage_perpage_desc'] = 'Legen Sie die Anzahl der Listeneinträge pro Profilbesucher-Seite fest!';

$l['setting_profilevisitors_overviewpage_maxavatarsize'] = 'Besucher-Avatar-Größe';
$l['setting_profilevisitors_overviewpage_maxavatarsize_desc'] = "Die Abmessungen des Besucher-Avatars; Breite und Höhe getrennt durch 'x' oder '|' (z.B. 70|70 oder 70x70).";

$l['setting_groups_canviewprofilevisitors'] = 'Kann Profil-Besucherlisten sehen?';
$l['setting_groups_hideonprofilevisitors'] = 'Unsichtbar auf Profil-Besucherlisten?';

// errors
$l['error_wrong_php_version'] = "Entschuldigung, dieses Plugin ist nicht kompatibel mit der verwendeten PHP-Version - Sie benötigen mindestens PHP-Version {1} oder höher!";
$l['error_setting_profilevisitors_overviewpage_maxavatarsize'] = "Bitte bearbeiten Sie die Einstellung der Besucher-Avatar-Größe!";
