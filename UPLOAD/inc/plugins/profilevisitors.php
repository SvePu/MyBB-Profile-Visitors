<?php

/**
 *  Main plugin file for Profile Visitors Plugin for MyBB 1.8
 *  Copyright © 2014 - 2023 SvePu
 *  Last change: 2022-01-05 - v 2.1
 *  Licensed under the GNU GPL, version 3
 */

if (!defined('IN_MYBB'))
{
    die('This file cannot be accessed directly.');
}

if (defined('THIS_SCRIPT'))
{
    global $templatelist;

    if (isset($templatelist))
    {
        $templatelist .= ',';
    }

    if (THIS_SCRIPT == 'member.php')
    {
        $templatelist .= 'member_profile_visitors, member_profile_visitors_visitor, member_profile_visitors_header_info, member_profile_visitors_header_all, member_profile_visitors_footer';
    }
    elseif (THIS_SCRIPT == 'misc.php')
    {
        $templatelist .= 'misc_profile_visitors_orderarrow, misc_profile_visitors_visitor_avatar, misc_profile_visitors_visitor, misc_profile_visitors';
    }
}

if (defined('IN_ADMINCP'))
{
    $plugins->add_hook('admin_config_settings_begin', 'profilevisitors_settings');
    $plugins->add_hook('admin_config_settings_change', 'profilevisitors_settings_check');
    $plugins->add_hook("admin_settings_print_peekers", 'profilevisitors_settings_peekers');
    $plugins->add_hook('datahandler_user_delete_start', 'profilevisitors_deleted_user');
}
else
{
    $plugins->add_hook('member_profile_end', 'profilevisitors_member_profile');
    $plugins->add_hook('misc_start', 'profilevisitors_misc');
    $plugins->add_hook('fetch_wol_activity_end', 'profilevisitors_wol');
    $plugins->add_hook('build_friendly_wol_location_end', 'profilevisitors_build_wol');
}

function profilevisitors_info()
{
    global $plugins_cache, $mybb, $db, $lang;
    $lang->load("config_profilevisitors");

    $info = array(
        'name'          => 'MyBB Profile Visitors',
        'description'   => $db->escape_string($lang->profilevisitors_desc),
        'website'       => 'https://github.com/SvePu/MyBB-Profile-Visitors',
        'author'        => 'SvePu',
        'authorsite'    => 'https://github.com/SvePu',
        'version'       => '2.1',
        'compatibility' => '18*',
        'codename'      => 'profilevisitors'
    );

    $info_desc = '';
    $gid_result = $db->simple_select('settinggroups', 'gid', "name = 'profilevisitors'", array('limit' => 1));
    $settings_group = $db->fetch_array($gid_result);
    if (!empty($settings_group['gid']))
    {
        $info_desc .= "<span style=\"font-size: 0.9em;\">(~<a href=\"index.php?module=config-settings&action=change&gid=" . $settings_group['gid'] . "\"> " . $db->escape_string($lang->setting_group_profilevisitors) . " </a>~)</span>";
    }

    if (!empty($plugins_cache) && is_array($plugins_cache) && is_array($plugins_cache['active']) && isset($plugins_cache['active']['profilevisitors']))
    {
        $info_desc .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right;" target="_blank" />
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="VGQ4ZDT8M7WS2" />
<input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
</form>';
    }


    if ($info_desc != '')
    {
        $info['description'] = $info_desc . '<br />' . $info['description'];
    }

    return $info;
}

function profilevisitors_install()
{
    global $db, $lang;
    $lang->load('config_profilevisitors');

    profilevisitors_cleanup();

    // Add DB Tables
    $collation = $db->build_create_table_collation();

    if (!$db->table_exists('profilevisitors'))
    {
        switch ($db->type)
        {
            case "pgsql":
                $db->write_query("CREATE TABLE " . TABLE_PREFIX . "profilevisitors (
                    id serial,
                    uid int NOT NULL default '0',
                    vid int NOT NULL default '0',
                    dateline int NOT NULL default '0',
                    PRIMARY KEY (id)
                );");
                break;
            case "sqlite":
                $db->write_query("CREATE TABLE " . TABLE_PREFIX . "profilevisitors (
                    id INTEGER PRIMARY KEY,
                    uid int NOT NULL default '0',
                    vid int NOT NULL default '0',
                    dateline int NOT NULL default '0'
                );");
                break;
            default:
                $db->write_query("CREATE TABLE " . TABLE_PREFIX . "profilevisitors (
                    id int unsigned NOT NULL auto_increment,
                    uid int unsigned NOT NULL default '0',
                    vid int unsigned NOT NULL default '0',
                    dateline int unsigned NOT NULL default '0',
                    PRIMARY KEY (id)
                ) ENGINE=MyISAM{$collation};");
                break;
        }
    }

    // Add Templates
    $templates = array(
        'member_profile_visitors' => '<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
    <tr>
        <td class="thead"><strong>{$lang->profilevisitors_header}</strong>{$profilevisitors_header_info}{$profilevisitors_header_all}</td>
    </tr>
    <tr>
        <td class="trow1">{$profilevisitors}</td>
    </tr>
    {$profilevisitors_footer}
</table>
<br />',
        'member_profile_visitors_visitor' => '{$comma}<span title="({$visitdate} - {$visittime})">{$visitor[\'profilelink\']}</span>',
        'member_profile_visitors_header_info' => '<span class="smalltext">({$lang->profilevisitors_header_info})</span>',
        'member_profile_visitors_header_all' => '<span class="smalltext" style="float:right;">{$lang->profilevisitors_header_all}</span>',
        'member_profile_visitors_footer' => '<tr>
    <td class="tfoot"><span class="smalltext" style="float:right;"><a href="misc.php?action=profile_visitors&amp;uid={$myuid}">{$lang->profilevisitors_footer}</a></span></td>
</tr>',
        'misc_profile_visitors' => '<html>
    <head>
        <title>{$mybb->settings[\'bbname\']} - {$lang->profilevisitors_visitors}</title>
        {$headerinclude}
    </head>
    <body>
        {$header}
        <table width="100%" border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
            <tr>
                <td class="thead" colspan="3"><strong>{$lang->profilevisitors_visitors}</strong></td>
            </tr>
            <tr>
                <td class="tcat" width="1%" align="center"><span class="smalltext"><strong>{$lang->profilevisitors_visitor_avatar}</strong></span></td>
                <td class="tcat"><span class="smalltext"><strong><a href="misc.php?action=profile_visitors&amp;uid={$uid}&amp;sortby=username&amp;order=asc">{$lang->profilevisitors_visitor_username}</a> {$orderarrow[\'username\']}</strong></span></td>
                <td class="tcat" width="30%" align="center"><span class="smalltext"><strong><a href="misc.php?action=profile_visitors&amp;uid={$uid}&amp;sortby=dateline&amp;order=desc">{$lang->profilevisitors_visitor_visittime}</a> {$orderarrow[\'dateline\']}</strong></span></td>
            </tr>
            {$profile_visitors_row}
            <tr>
                <td class="tfoot" colspan="3">&nbsp;</td>
            </tr>
        </table>
        <div class="float_left">{$multipage}</div>
        {$footer}
    </body>
</html>',
        'misc_profile_visitors_visitor' => '<tr>
    <td class="{$altbg}" align="center">{$visitor[\'avatar\']}</td>
    <td class="{$altbg}">{$visitor[\'profilelink\']}</td>
    <td class="{$altbg}" width="30%" align="center">{$visitor[\'lastvisit\']}</td>
</tr>',
        'misc_profile_visitors_visitor_avatar' => '<img src="{$useravatar[\'image\']}" alt="{$useravatar[\'alt\']}" {$useravatar[\'width_height\']} />',
        'misc_profile_visitors_orderarrow' => '<span class="smalltext">[<a href="misc.php?action=profile_visitors&amp;uid={$uid}&amp;sortby={$sortby}&amp;order={$oppsortnext}">{$oppsort}</a>]</span>'
    );

    foreach ($templates as $name => $template)
    {
        $addtemplate = array(
            'title' => $db->escape_string($name),
            'template' => $db->escape_string($template),
            'version' => 2,
            'sid' => -2,
            'dateline' => TIME_NOW
        );

        $db->insert_query('templates', $addtemplate);
        unset($addtemplate);
    }

    // Add Settings
    $query = $db->simple_select('settinggroups', 'MAX(disporder) AS disporder');
    $disporder = (int)$db->fetch_field($query, 'disporder');

    $setting_group = array(
        'name' => 'profilevisitors',
        "title" => $db->escape_string($lang->setting_group_profilevisitors),
        "description" => $db->escape_string($lang->setting_group_profilevisitors_desc),
        'isdefault' => 0
    );

    $setting_group['disporder'] = ++$disporder;

    $gid = (int)$db->insert_query('settinggroups', $setting_group);

    $settings = array(
        'enable' => array(
            'optionscode' => 'yesno',
            'value' => 1
        ),
        'showgroups' => array(
            'optionscode' => 'groupselect',
            'value' => '2,3,4,6'
        ),
        'limit' => array(
            'optionscode' => 'numeric \n min=0',
            'value' => '10',
        ),
        'hidegroups' => array(
            'optionscode' => 'groupselect',
            'value' => '4'
        ),
        'styled_usernames' => array(
            'optionscode' => 'yesno',
            'value' => 1
        ),
        'allvisits' => array(
            'optionscode' => 'yesno',
            'value' => 1
        ),
        'overviewpage_enable' => array(
            'optionscode' => 'yesno',
            'value' => 1
        ),
        'overviewpage_groups' => array(
            'optionscode' => 'groupselect',
            'value' => '2,3,4,6'
        ),
        'overviewpage_maxavatarsize' => array(
            'optionscode' => 'text',
            'value' => '70x70'
        )
    );

    $disporder = 0;

    foreach ($settings as $name => $setting)
    {
        $name = "profilevisitors_{$name}";

        $setting['name'] = $db->escape_string($name);

        $lang_var_title = "setting_{$name}";
        $lang_var_description = "setting_{$name}_desc";

        $setting['title'] = $db->escape_string($lang->{$lang_var_title});
        $setting['description'] = $db->escape_string($lang->{$lang_var_description});
        $setting['disporder'] = $disporder;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
        ++$disporder;
    }

    rebuild_settings();
}

function profilevisitors_is_installed()
{
    global $mybb;

    if (isset($mybb->settings['profilevisitors_enable']))
    {
        return true;
    }
    return false;
}

function profilevisitors_uninstall()
{
    global $db, $mybb;

    if ($mybb->request_method != 'post')
    {
        global $page, $lang;
        $lang->load('config_profilevisitors');
        $page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin=profilevisitors', $lang->profilevisitors_uninstall_message, $lang->profilevisitors_uninstall);
    }

    $db->delete_query("templates", "title LIKE 'member_profile_visitors%' OR title LIKE 'misc_profile_visitors%'");

    $db->delete_query("settinggroups", "name='profilevisitors'");
    $db->delete_query("settings", "name LIKE 'profilevisitors_%'");

    rebuild_settings();

    if (!isset($mybb->input['no']) && $db->table_exists('profilevisitors'))
    {
        $db->drop_table('profilevisitors');
    }
}

function profilevisitors_activate()
{
    require MYBB_ROOT . '/inc/adminfunctions_templates.php';
    find_replace_templatesets('member_profile', '#{\$modoptions}#', "{\$profilevisits}\n{\$modoptions}");
}

function profilevisitors_deactivate()
{
    require MYBB_ROOT . '/inc/adminfunctions_templates.php';
    find_replace_templatesets('member_profile', '#\{\$profilevisits\}\n#', '', 0);
}

function profilevisitors_settings()
{
    global $lang;
    $lang->load('config_profilevisitors');
}

function profilevisitors_settings_check()
{
    global $mybb;

    if (!$mybb->request_method == "post")
    {
        return;
    }
    else
    {
        global $db, $lang;

        $gid = (int)$mybb->input['gid'];

        $query = $db->simple_select('settinggroups', 'gid', "name = 'profilevisitors'", array('limit' => 1));
        $plugin_gid = $db->fetch_field($query, 'gid');

        if ($gid == (int)$plugin_gid)
        {
            if (isset($mybb->input['upsetting']['profilevisitors_hidegroups']) && $mybb->input['upsetting']['profilevisitors_hidegroups'] == 'all')
            {
                flash_message($lang->error_setting_profilevisitors_hidegroups_all_hided, 'error');
                admin_redirect("index.php?module=config-settings&action=change&gid=" . $gid);
            }

            if (isset($mybb->input['upsetting']['profilevisitors_overviewpage_maxavatarsize']))
            {
                if (preg_match("/\b\d+[|x]{1}\d+\b/i", $mybb->input['upsetting']['profilevisitors_overviewpage_maxavatarsize']))
                {
                    $mybb->input['upsetting']['profilevisitors_overviewpage_maxavatarsize'] = str_replace('|', 'x', my_strtolower($mybb->input['upsetting']['profilevisitors_overviewpage_maxavatarsize']));
                }
                else
                {
                    flash_message($lang->error_setting_profilevisitors_overviewpage_maxavatarsize, 'error');
                    admin_redirect("index.php?module=config-settings&action=change&gid=" . $gid);
                }
            }
        }
    }
}

function profilevisitors_settings_peekers(&$peekers)
{
    $peekers[] = 'new Peeker($(".setting_profilevisitors_enable"), $("#row_setting_profilevisitors_showgroups, #row_setting_profilevisitors_limit, #row_setting_profilevisitors_hidegroups, #row_setting_profilevisitors_styled_usernames, #row_setting_profilevisitors_allvisits, #row_setting_profilevisitors_overviewpage_enable, #row_setting_profilevisitors_overviewpage_groups, #row_setting_profilevisitors_overviewpage_maxavatarsize"), 1, true)';
    $peekers[] = 'new Peeker($(".setting_profilevisitors_allvisits"), $("#row_setting_profilevisitors_overviewpage_enable, #row_setting_profilevisitors_overviewpage_groups, #row_setting_profilevisitors_overviewpage_maxavatarsize"), 1, true)';
    $peekers[] = 'new Peeker($(".setting_profilevisitors_overviewpage_enable"), $("#row_setting_profilevisitors_overviewpage_groups, #row_setting_profilevisitors_overviewpage_maxavatarsize"), 1, true)';
}

function profilevisitors_cleanup()
{
    global $db;

    if (file_exists(MYBB_ROOT . ".pvdb_unlock.no"))
    {
        @unlink(MYBB_ROOT . ".pvdb_unlock.no");
    }

    $db->delete_query("templates", "title IN('userprofile_profilevisitors')");
}

function profilevisitors_deleted_user($users)
{
    global $db;

    foreach ($users->delete_uids as $key => $uid)
    {
        if ($db->table_exists('profilevisitors'))
        {
            $db->delete_query("profilevisitors", "uid={$uid} OR vid={$uid}");
        }
    }
}

function profilevisitors_member_profile()
{
    global $mybb;

    if ($mybb->settings['profilevisitors_enable'] != 1)
    {
        return;
    }

    global $db, $memprofile, $profilevisits;

    $myuid = (int)$memprofile['uid'];
    $vuid = (int)$mybb->user['uid'];

    if ($vuid && $vuid != $myuid)
    {
        $where = "uid = '" . $myuid . "' AND vid = '" . $vuid . "'";

        $query = $db->simple_select('profilevisitors', '*', $where);

        if (!$db->num_rows($query))
        {
            $newinsert = array(
                'uid' => (int)$myuid,
                'vid' => (int)$vuid,
                'dateline' => TIME_NOW
            );

            $db->insert_query('profilevisitors', $newinsert);
        }
        else
        {
            $update = array(
                'dateline' => TIME_NOW
            );

            $db->update_query("profilevisitors", $update, $where);
        }
    }

    $profilevisits = '';

    if (is_member($mybb->settings['profilevisitors_showgroups']) || $mybb->settings['profilevisitors_showgroups'] == '-1')
    {
        global $lang, $templates, $theme, $profilevisitors;
        $lang->load("profilevisitors");

        $lang->profilevisitors_header = $db->escape_string($lang->profilevisitors_header);
        $profilevisitors_header_info = $profilevisitors_header_all = $profilevisitors_footer = '';
        $profilevisitors = $db->escape_string($lang->profilevisitors_novisitors);

        if ($mybb->settings['profilevisitors_hidegroups'] != "-1")
        {
            $where = "WHERE pv.uid = '{$myuid}'";

            if (!empty($mybb->settings['profilevisitors_hidegroups']))
            {
                $where .= ' AND u.usergroup NOT IN (' . $mybb->settings['profilevisitors_hidegroups'] . ')';
            }

            $limit = "";
            if ($mybb->settings['profilevisitors_limit'] > 0)
            {
                $limit = "LIMIT " . (int)$mybb->settings['profilevisitors_limit'];
            }

            $query = $db->query("
                SELECT pv.vid, pv.dateline, u.username, u.usergroup, u.displaygroup
                FROM " . TABLE_PREFIX . "profilevisitors pv
                LEFT JOIN " . TABLE_PREFIX . "users u ON (u.uid=pv.vid)
                {$where}
                ORDER BY pv.dateline DESC
                {$limit}
            ");

            if ($db->num_rows($query))
            {
                $profilevisitors = $comma = '';
                while ($visitor = $db->fetch_array($query))
                {
                    $visitdate = my_date($mybb->settings['dateformat'], $visitor['dateline']);
                    $visittime = my_date($mybb->settings['timeformat'], $visitor['dateline']);

                    if ($mybb->settings['profilevisitors_styled_usernames'] == 1)
                    {
                        $visitor['username'] = format_name(htmlspecialchars_uni($visitor['username']), $visitor['usergroup'], $visitor['displaygroup']);
                    }
                    else
                    {
                        $visitor['username'] = htmlspecialchars_uni($visitor['username']);
                    }

                    $visitor['profilelink'] = build_profile_link($visitor['username'], $visitor['vid']);

                    eval('$profilevisitors .= "' . $templates->get('member_profile_visitors_visitor', 1, 0) . '";');
                    $comma = ", ";
                }

                if ($mybb->settings['profilevisitors_limit'] > 0)
                {
                    $lang->profilevisitors_header_info = $lang->sprintf($db->escape_string($lang->profilevisitors_header_info), (int)$mybb->settings['profilevisitors_limit']);
                    eval('$profilevisitors_header_info = "' . $templates->get('member_profile_visitors_header_info') . '";');
                }

                if ($mybb->settings['profilevisitors_allvisits'] == 1)
                {
                    $query = $db->simple_select("profilevisitors", "COUNT(*) AS allvisits", "uid = '{$myuid}'");
                    $allvisits = (int)$db->fetch_field($query, 'allvisits');

                    $lang->profilevisitors_header_all = $lang->sprintf($db->escape_string($lang->profilevisitors_header_all), $allvisits);
                    eval('$profilevisitors_header_all = "' . $templates->get('member_profile_visitors_header_all') . '";');
                }

                if ($mybb->settings['profilevisitors_overviewpage_enable'] == 1 && is_member($mybb->settings['profilevisitors_overviewpage_groups']))
                {
                    $lang->profilevisitors_footer = $lang->sprintf($db->escape_string($lang->profilevisitors_footer), htmlspecialchars_uni($memprofile['username']));
                    eval('$profilevisitors_footer = "' . $templates->get('member_profile_visitors_footer') . '";');
                }
            }
        }

        eval('$profilevisits = "' . $templates->get('member_profile_visitors') . '";');
    }
}

function profilevisitors_misc()
{
    global $mybb;

    if (!isset($mybb->settings['profilevisitors_enable']) || (isset($mybb->settings['profilevisitors_enable']) && $mybb->settings['profilevisitors_enable'] != 1))
    {
        return;
    }

    $mybb->input['action'] = $mybb->get_input('action');

    if (!$mybb->input['action'] || $mybb->input['action'] != "profile_visitors")
    {
        return;
    }

    global $lang;
    $lang->load("profilevisitors");

    if ($mybb->settings['profilevisitors_overviewpage_enable'] != 1)
    {
        error($lang->error_profilevisitors_overviewpage_disabled);
    }

    if ($mybb->usergroup['canviewprofiles'] != 1 || !is_member($mybb->settings['profilevisitors_overviewpage_groups']))
    {
        error_no_permission();
    }

    $uid = $mybb->get_input('uid', MyBB::INPUT_INT);
    if (empty($uid))
    {
        error($lang->error_profilevisitors_uid_missing);
    }

    $user = get_user($uid);
    if (empty($user) || !is_array($user))
    {
        error($lang->error_profilevisitors_not_exists);
    }

    global $db, $headerinclude, $header, $theme, $templates, $footer;

    $profile_visitors = $profile_visitors_row = '';

    $where = "WHERE pv.uid = '{$uid}' AND u.uid != 0";

    if (!empty($mybb->settings['profilevisitors_hidegroups']))
    {
        $where .= ' AND u.usergroup NOT IN (' . $mybb->settings['profilevisitors_hidegroups'] . ')';
    }

    $query = $db->query("
            SELECT COUNT(vid) AS visitors
            FROM " . TABLE_PREFIX . "profilevisitors pv
            LEFT JOIN " . TABLE_PREFIX . "users u ON (u.uid=pv.vid)
            {$where}
        ");

    $numvisitors = $db->fetch_field($query, "visitors");

    if ($numvisitors > 0)
    {
        if (!isset($lang->desc))
        {
            $lang->load("memberlist");
        }

        $mybb->input['order'] = htmlspecialchars_uni($mybb->get_input('order'));
        $ordersel = array('asc' => '', 'desc');
        switch (my_strtolower($mybb->input['order']))
        {
            case "asc":
                $sortordernow = "asc";
                $ordersel['asc'] = "selected=\"selected\"";
                $oppsort = $lang->desc;
                $oppsortnext = "desc";
                break;
            default:
                $sortordernow = "desc";
                $ordersel['desc'] = "selected=\"selected\"";
                $oppsort = $lang->asc;
                $oppsortnext = "asc";
                break;
        }

        $sortby = htmlspecialchars_uni($mybb->get_input('sortby'));
        switch ($mybb->get_input('sortby'))
        {
            case "username":
                $sortfield = "u.username";
                break;
            default:
                $sortby = "dateline";
                $sortfield = "pv.dateline";
                $mybb->input['sortby'] = "dateline";
                break;
        }
        $orderarrow = $sortsel = array('username' => '', 'dateline' => '');
        $sortsel[$sortby] = "selected=\"selected\"";

        eval("\$orderarrow['$sortby'] = \"" . $templates->get("misc_profile_visitors_orderarrow") . "\";");



        if (!$mybb->settings['membersperpage'])
        {
            $per_page = 10;
        }
        else
        {
            $per_page = $mybb->settings['membersperpage'];
        }

        $page = $mybb->get_input('page', MyBB::INPUT_INT);
        if ($page && $page > 0)
        {
            $start = ($page - 1) * $per_page;
        }
        else
        {
            $start = 0;
            $page = 1;
        }

        if ($mybb->input['order'] || ($sortby && $sortby != "dateline"))
        {
            $page_url = "misc.php?action=profile_visitors&uid={$uid}&sortby={$sortby}&order={$sortordernow}";
        }
        else
        {
            $page_url = "misc.php?action=profile_visitors&uid={$uid}";
        }

        $multipage = multipage($numvisitors, $per_page, $page, $page_url);

        $query_visitors = $db->query("
                SELECT pv.vid, pv.dateline, u.username, u.usergroup, u.displaygroup, u.avatar, u.avatardimensions
                FROM " . TABLE_PREFIX . "profilevisitors pv
                LEFT JOIN " . TABLE_PREFIX . "users u ON (u.uid=pv.vid)
                {$where}
                ORDER BY {$sortfield} {$sortordernow}
                LIMIT {$start}, {$per_page}
            ");

        $altbg = alt_trow();
        while ($visitor = $db->fetch_array($query_visitors))
        {
            $visitor['username'] = htmlspecialchars_uni($visitor['username']);

            if (!isset($mybb->settings['profilevisitors_overviewpage_maxavatarsize']) || empty($mybb->settings['profilevisitors_overviewpage_maxavatarsize']))
            {
                $mybb->settings['profilevisitors_overviewpage_maxavatarsize'] = $mybb->settings['maxavatardims'];
            }

            $useravatar = format_avatar($visitor['avatar'], $visitor['avatardimensions'], my_strtolower($mybb->settings['profilevisitors_overviewpage_maxavatarsize']));
            $useravatar['alt'] = $lang->avatar . '-' . $visitor['username'];

            eval("\$visitor['avatar'] = \"" . $templates->get("misc_profile_visitors_visitor_avatar") . "\";");

            $visitor['profilelink'] = build_profile_link(format_name($visitor['username'], $visitor['usergroup'], $visitor['displaygroup']), $visitor['vid']);
            $visitor['lastvisit'] = my_date("relative", $visitor['dateline']);

            eval('$profile_visitors_row .= "' . $templates->get('misc_profile_visitors_visitor', 1, 0) . '";');

            $altbg = alt_trow();
        }

        $userlink = get_profile_link($uid);

        $lang->profilevisitors_profile = $lang->sprintf($lang->profilevisitors_profile, $user['username']);
        $lang->profilevisitors_visitors = $lang->sprintf($lang->profilevisitors_visitors, $user['username']);

        add_breadcrumb($lang->profilevisitors_profile, $userlink);
        add_breadcrumb($lang->profilevisitors_visitors);
        eval('$profile_visitors = "' . $templates->get('misc_profile_visitors') . '";');
        output_page($profile_visitors);
        exit;
    }
    else
    {
        error($lang->profilevisitors_novisitors);
    }
}

function profilevisitors_wol(&$user_activity)
{
    global $parameters, $uid_list;

    if ($user_activity['activity'] == "misc")
    {
        if ($parameters['action'] == "profile_visitors")
        {
            if (isset($parameters['uid']) && $parameters['uid'] > 0)
            {
                $uid_list[$parameters['uid']] = $parameters['uid'];
                $user_activity['uid'] = (int)$parameters['uid'];
            }
            $user_activity['activity'] = "misc_profile_visitors";
        }
    }
}

function profilevisitors_build_wol(&$plugin_array)
{
    global $lang, $mybb, $uid_list, $usernames;
    $lang->load("profilevisitors");

    if ($plugin_array['user_activity']['activity'] == "misc_profile_visitors")
    {
        if (!empty($usernames[$plugin_array['user_activity']['uid']]))
        {
            $plugin_array['location_name'] = $lang->sprintf($lang->profilevisitors_wol_profile_visitors, "misc.php?action=profile_visitors&amp;uid={$plugin_array['user_activity']['uid']}", get_profile_link($plugin_array['user_activity']['uid']), $usernames[$plugin_array['user_activity']['uid']]);
        }
    }
}
