<?php

/**
 *  Language admin file for Profile Visitors plugin for MyBB 1.8
 *  Language: english
 *  Copyright © 2014 - 2023 SvePu
 *  Last change: 2023-01-17
 */

$l['profilevisitors'] = 'Profile Visitors';
$l['profilevisitors_desc'] = 'Add a Box in Users Profiles page with list of Users which Have Visited It';
$l['profilevisitors_uninstall'] = 'Profile Visitors - Uninstallation';
$l['profilevisitors_uninstall_message'] = 'Do you wish to drop all plugin entries from the database?';

$l['setting_profilevisitors_main'] = 'Global Plugin Settings';
$l['setting_profilevisitors_groups'] = 'Group Based Plugin Settings';

$l['setting_group_profilevisitors'] = 'Profile Visitors Settings';
$l['setting_group_profilevisitors_desc'] = 'Setting of the profile visitors plugin';

$l['setting_profilevisitors_enable'] = 'Do you want enable this feature?';
$l['setting_profilevisitors_enable_desc'] = 'Set YES to enable it! - Please also think about <a href="index.php?module=user-groups">Group Based Profile Visitors Settings</a> on group miscellaneous tab!';

$l['setting_profilevisitors_canviewown'] = 'User can view Own List?';
$l['setting_profilevisitors_canviewown_desc'] = 'Set YES to enable it!';

$l['setting_profilevisitors_limit'] = 'How many visitors you want show?';
$l['setting_profilevisitors_limit_desc'] = 'Set the list limit! (default: 10 - set 0 for no limit)';

$l['setting_profilevisitors_styled_usernames'] = 'Show usernames in user group based style?';
$l['setting_profilevisitors_styled_usernames_desc'] = 'Choose YES to enable this feature!';

$l['setting_profilevisitors_allvisits'] = 'Show Number of all Visits?';
$l['setting_profilevisitors_allvisits_desc'] = 'Select YES to show the number of all profile visits.';

$l['setting_profilevisitors_overviewpage_enable'] = 'Enable Profile Visitors Page?';
$l['setting_profilevisitors_overviewpage_enable_desc'] = 'Choose YES to activate the profile visitors page and add a link to the list in the user profile.';

$l['setting_profilevisitors_overviewpage_perpage'] = 'Number of Visitors on one Page Site';
$l['setting_profilevisitors_overviewpage_perpage_desc'] = 'Set number of visitors on one page site.';

$l['setting_profilevisitors_overviewpage_maxavatarsize'] = 'Visitor Avatar Dimensions';
$l['setting_profilevisitors_overviewpage_maxavatarsize_desc'] = "The dimensions of the visitor avatar; width and height separated by 'x' or '|' (e.g. 70|70 or 70x70).";

$l['setting_groups_canviewprofilevisitors'] = 'Can view profile visitors lists?';
$l['setting_groups_hideonprofilevisitors'] = 'Invisible on profile visitors lists?';

// errors
$l['error_wrong_php_version'] = "Sorry, this plugin is not compatible with the setted PHP version - You will need PHP version {1} or above!";
$l['error_setting_profilevisitors_overviewpage_maxavatarsize'] = "Please edit settings of Visitor Avatar Dimensions!";
