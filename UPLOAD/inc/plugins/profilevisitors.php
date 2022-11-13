<?php

/**
 *  Main plugin file for Profile Visitors plugin for MyBB 1.8
 *  Copyright © 2015 Svepu
 *  Last change: 2022-11-12 - v 2.0
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
        $templatelist .= 'member_profile_visitors';
    }
}

if (defined('IN_ADMINCP'))
{
    $plugins->add_hook('admin_config_settings_begin', 'profilevisitors_settings');
}
else
{
    $plugins->add_hook('member_profile_end', 'profilevisitors_run');
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
        'version'       => '2.0',
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

    if (is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active']['profilevisitors'])
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

    $template = array(
        'title' => 'member_profile_visitors',
        'template' => $db->escape_string('<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
    <tr>
        <td class="thead"><strong>{$profilevisitors_header}</strong><span class="smalltext">{$profilevisitors_header_info}</span>{$allprofilevisitors}</td>
    </tr>
    <tr>
        <td class="trow1">{$profilevisitors}</td>
    </tr>
</table>
<br />'),
        'version' => 2,
        'sid' => -2,
        'dateline' => TIME_NOW
    );

    $db->insert_query("templates", $template);

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

    $db->delete_query("templates", "title IN('member_profile_visitors')");

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

function profilevisitors_cleanup()
{
    global $db;

    if (file_exists(MYBB_ROOT . ".pvdb_unlock.no"))
    {
        @unlink(MYBB_ROOT . ".pvdb_unlock.no");
    }

    $db->delete_query("templates", "title IN('userprofile_profilevisitors')");
}

function profilevisitors_run()
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


        $profilevisitors_header = $db->escape_string($lang->profilevisitors_header);
        $profilevisitors_header_info = $allprofilevisitors = '';
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
                while ($data = $db->fetch_array($query))
                {
                    $date = my_date($mybb->settings['dateformat'], $data['dateline']);
                    $time = my_date($mybb->settings['timeformat'], $data['dateline']);

                    if ($mybb->settings['profilevisitors_styled_usernames'] == 1)
                    {
                        $username = build_profile_link(format_name(htmlspecialchars_uni($data['username']), $data['usergroup'], $data['displaygroup']), $data['vid']);
                    }
                    else
                    {
                        $username = build_profile_link(htmlspecialchars_uni($data['username']), $data['vid']);
                    }

                    $profilevisitors .= $comma . "<span title='(" . $date . " - " . $time . ")'>" . $username . "</span>";
                    $comma = ", ";
                }

                if ($mybb->settings['profilevisitors_limit'] > 0)
                {
                    $profilevisitors_header_info = ' (' . $lang->sprintf($db->escape_string($lang->profilevisitors_header_info), (int)$mybb->settings['profilevisitors_limit']) . ')';
                }

                if ($mybb->settings['profilevisitors_allvisits'] == 1)
                {
                    $query = $db->simple_select("profilevisitors", "COUNT(*) AS allvisits", "uid = '{$myuid}'");
                    $allvisits = (int)$db->fetch_field($query, 'allvisits');
                    $allprofilevisitors = '<span class="smalltext" style="float:right;">' . $lang->sprintf($db->escape_string($lang->profilevisitors_header_all), $allvisits) . '</span>';
                }
            }
        }

        eval("\$profilevisits = \"" . $templates->get("member_profile_visitors") . "\";");
    }
}
