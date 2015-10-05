<?php
/*
	Main plugin file for Profile Visitors plugin for MyBB 1.8
	Copyright © 2015 Svepu
	Last change: 2015-10-05 - v 1.2
	Licensed under the GNU GPL, version 3
*/

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(my_strpos($_SERVER['PHP_SELF'], 'member.php'))
{
	global $templatelist;
	if(isset($templatelist)){$templatelist .= ',';}
	$templatelist .= 'userprofile_profilevisitors';
}

function profilevisitors_info()
{
	global $plugins_cache, $mybb, $db, $lang;
	$lang->load("config_profilevisitors");
	
    $info = array(
			'name'			=>	$db->escape_string($lang->plugin_name),
			'description' 	=>	$db->escape_string($lang->plugin_desc),
			'website'     	=>	'https://github.com/SvePu/Profile-Visitors',
			'author'      	=>	'SvePu',
			'authorsite'  	=>	'https://github.com/SvePu',
			'version'     	=>	'1.2',
			'compatibility'	=>	'18*',
			'codename'		=>	'profilevisitors',
			'guid'		   	=>	''
	);
	
	$info_desc = '';
	$gid_result = $db->simple_select('settinggroups', 'gid', "name = 'profilevisitors_settings'", array('limit' => 1));
	$settings_group = $db->fetch_array($gid_result);
	if(!empty($settings_group['gid']))
	{
		$info_desc .= "<span style=\"font-size: 0.9em;\">(~<a href=\"index.php?module=config-settings&action=change&gid=".$settings_group['gid']."\"> ".$db->escape_string($lang->profilevisitors_settings_title)." </a>~)</span>";
	}
    
    if(is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active']['profilevisitors'])
    {
		$info_desc .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right;" target="_blank" />
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="VGQ4ZDT8M7WS2" />
<input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
</form>';
	}
	
	if(file_exists(MYBB_ROOT.".pvdb_unlock.no"))
	{
		$info_delinfo = "<span style=\"line-height: 2.5em;\">".$db->escape_string($lang->profilevisitors_delinfo)."</span>";
	}
	else if (file_exists(MYBB_ROOT.".pvdb_unlock.yes"))
	{
		$info_delinfo = "<span style=\"line-height: 2.5em; color:red;\">".$db->escape_string($lang->profilevisitors_delinfo_warning)."</span>";
	}
	else
	{
		$info_delinfo = "";
	}
	
	if($info_desc != '')
	{
		$info['description'] = $info_desc.'<br />'.$info['description'].'<br />'.$info_delinfo;
	}
    
    return $info;
}

function profilevisitors_is_installed()
{
	global $mybb;

	if(isset($mybb->settings['profilevisitors_enable']))
	{
		return true;
	}
	return false;
}

function profilevisitors_install()
{
	global $db, $mybb, $lang;
	
	$lang->load("config_profilevisitors");
	
	$db->write_query("CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."profilevisitors` (
						`id` int(10) NOT NULL auto_increment,
						`uid` int( 10 ) NOT NULL ,
						`vid` int( 10 ) NOT NULL ,
						`time` int( 50 ) NOT NULL,
						PRIMARY KEY  (`id`)
						) ENGINE=MyISAM");
						

	$template_upv = array(
		'title'		=> 'userprofile_profilevisitors',
		'template'	=> $db->escape_string('<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<tr>
		<td class="thead"><strong>{$profilevisitors_header}</strong><span class="smalltext">{$profilevisitors_header_info}</span>{$allprofilevisitors}</td>
	</tr>
	<tr>
		<td class="trow1">{$profilevisitors}</td>
	</tr>
</table>
<br />'),
		'sid' 		=> '-1',
		'version' 	=> '',
		'dateline' 	=> time()
	);

	$db->insert_query("templates", $template_upv);
	
	$query_add = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$rows = $db->fetch_field($query_add, "rows");
    $profilevisitors_group = array(
		"name" 			=>	"profilevisitors_settings",
		"title" 		=>	$db->escape_string($lang->profilevisitors_settings_title),
		"description" 	=>	$db->escape_string($lang->profilevisitors_settings_title_desc),
		"disporder"		=> 	$rows+1,
		"isdefault" 	=>  0
	);
    $gid = $db->insert_query("settinggroups", $profilevisitors_group);
	
	$profilevisitors_setting_array = array(
		'profilevisitors_enable' => array(
			'title'			=> $db->escape_string($lang->profilevisitors_enable_title),
			'description'  	=> $db->escape_string($lang->profilevisitors_enable_title_desc),
			'optionscode'  	=> 'yesno',
			'value'        	=> 1,
			'disporder'		=> 1
		),
		'profilevisitors_limit' => array(
			'title'			=> $db->escape_string($lang->profilevisitors_limit_title),
			'description' 	=> $db->escape_string($lang->profilevisitors_limit_title_desc),
			'optionscode'  	=> 'numeric',
			'value'        	=> 10,
			'disporder'		=> 2
		),
		'profilevisitors_styled_usernames' => array(
			'title'			=> $db->escape_string($lang->profilevisitors_styled_usernames_title),
			'description'  	=> $db->escape_string($lang->profilevisitors_styled_usernames_title_desc),
			'optionscode'  	=> 'yesno',
			'value'        	=> 1,
			'disporder'		=> 3
		),
		'profilevisitors_groupselect' => array(
			'title'			=> $db->escape_string($lang->profilevisitors_groupselect_title),
			'description' 	=> $db->escape_string($lang->profilevisitors_groupselect_title_desc),
			'optionscode'  	=> 'groupselect',
			'value'        	=> 4,
			"disporder"		=> 4
		),
	);

	foreach($profilevisitors_setting_array as $name => $setting)
	{
		$setting['name'] = $name;
		$setting['gid'] = $gid;

		$db->insert_query('settings', $setting);
	}
	
	rebuild_settings();
}


function profilevisitors_activate()
{
	global $mybb;
	
	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('member_profile', '#{\$modoptions}#', "{\$profilevisits}\n{\$modoptions}");
}


function profilevisitors_deactivate()
{
	global $mybb;

	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('member_profile', '#\{\$profilevisits\}\n#', '', 0);
}

function profilevisitors_uninstall()
{
	global $mybb, $db;

	if(file_exists(MYBB_ROOT.".pvdb_unlock.yes"))
	{
		$db->write_query("DROP TABLE `".TABLE_PREFIX."profilevisitors`");
		rename(MYBB_ROOT.".pvdb_unlock.yes", MYBB_ROOT.".pvdb_unlock.no");
	}
	$db->delete_query("templates","title IN('userprofile_profilevisitors')");
	
	$result = $db->simple_select('settinggroups', 'gid', "name = 'profilevisitors_settings'", array('limit' => 1));
	$pv_group = $db->fetch_array($result);
	
	if(!empty($pv_group['gid']))
	{
		$db->delete_query('settinggroups', "gid='{$pv_group['gid']}'");
		$db->delete_query('settings', "gid='{$pv_group['gid']}'");
		rebuild_settings();
	}
	
}

function profilevisitors_run(){

	global $mybb, $db, $lang, $templates, $theme, $profilevisitors, $memprofile, $profilevisits ;
	
	if ($mybb->settings['profilevisitors_enable'] == 1)
	{
		$myuid = (int)$memprofile['uid'];
		$vuid = (int)$mybb->user['uid'];
		$time = time();
		$limit = !empty($mybb->settings['profilevisitors_limit']) && $mybb->settings['profilevisitors_limit'] > 0 ? $mybb->settings['profilevisitors_limit'] : 10;
		
		$lang->load("profilevisitors");
		$profilevisitors_header = $db->escape_string($lang->profilevisitors_header);

		$query = $db->simple_select('profilevisitors', '*', "uid = '".$myuid."' and vid = '".$vuid."'");

		if($vuid != "0" and $vuid != $myuid)
		{
			if($db->num_rows($query) < 1)
			{
				$db->write_query("INSERT INTO `".TABLE_PREFIX."profilevisitors` VALUES (NULL,'$myuid', '$vuid','$time')");
			}
			else
			{
				$db->update_query("profilevisitors", array('time' => $time),  "uid = '".$myuid."' and vid = '".$vuid."'");
			}
		}
		
		if (!empty($mybb->settings['profilevisitors_groupselect']))
		{
			$pvhidden = ' AND u.usergroup NOT IN ("'.$mybb->settings['profilevisitors_groupselect'].'")';
		}
		else
		{
			$pvhidden = '';
		}
				
		$query = $db->query("SELECT v.uid, v.vid, v.time, u.uid, u.username, u.usergroup, u.displaygroup
							FROM ".TABLE_PREFIX."profilevisitors v
							LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=v.vid)
							WHERE v.uid = '{$myuid}'{$pvhidden}
							ORDER BY v.time 
							DESC LIMIT {$limit}");

		if ($db->num_rows($query) > 0 && $mybb->settings['profilevisitors_groupselect'] != "-1")
		{
			while($data = $db->fetch_array($query))
			{
				if(!empty(get_user($data['vid'])))
				{
					$date = my_date($mybb->settings['dateformat'], $data['time']);
					$time = my_date($mybb->settings['timeformat'], $data['time']);
					if ($mybb->settings['profilevisitors_styled_usernames'] == 1)
					{
						$username = build_profile_link(format_name(htmlspecialchars_uni($data['username']), $data['usergroup'], $data['displaygroup']), $data['uid']);
					}
					else
					{
						$username = build_profile_link(htmlspecialchars_uni($data['username']), $data['uid']);
					}				
					$profilevisitors = $profilevisitors."<span title='(".$date." - ".$time.")'>".$username."</span>, ";
				}
			}
			
			$profilevisitors_header_info = ' ('.$lang->sprintf($db->escape_string($lang->profilevisitors_header_info), $limit).')';
			$allpv = $db->simple_select("profilevisitors", "id", "uid = '{$myuid}'");
			$allprofilevisitors = '<span class="smalltext" style="float:right;">'.$db->escape_string($lang->profilevisitors_header_all).$allpv->num_rows.'</span>';
		}
		else
		{
			$profilevisitors_header_info = '';
			$allprofilevisitors = '';
			$profilevisitors = $db->escape_string($lang->profilevisitors_novisitors);
		}
		
		eval("\$profilevisits = \"".$templates->get("userprofile_profilevisitors")."\";"); 
	}
}
$plugins->add_hook("member_profile_end", "profilevisitors_run");
