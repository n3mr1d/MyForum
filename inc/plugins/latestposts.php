<?php
/**
* latest posts sidebar
*
* Website: http://mybebb.org
* Skype: daniel_mit1
*
**/

/* Hooks */
$plugins->add_hook("index_end", "latestposts");

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

function latestposts_info()
{
	global $lang;
	$lang->load("latestposts");
	return array(
		"name"          => $lang->plugname,
		"description"   => $lang->plugdesc,
		"website"       => "http://myhebb.org/",
		"author"        => "DanielM",
		"authorsite"    => "http://myhebb.org/",
		"version"       => "5.1.1",
		"guid"          => "leatestposts",
		"compatibility" => "18*"
	);
}

function latestposts_install()
{
	global $db, $lang;
	$lang->load("latestposts");
	$new_setting_group = array(
		"name" => "latestposts",
		"title" => $lang->settings_name,
		"disporder" => 1,
		"isdefault" => 0,
		"description" => ""
	);

	$gid = $db->insert_query("settinggroups", $new_setting_group);

	$settings[] = array(
		"name" => "latestposts_threadcount",
		"title" => $lang->num_posts_to_show,
		"description" => "",
		"optionscode" => "text",
		"disporder" => 1,
		"value" => 15,
		"gid" => $gid
	);

	$settings[] = array(
		"name" => "latestposts_forumskip",
		"title" => $lang->forums_to_skip,
		"description" => $lang->forums_to_skip_desc,
		"optionscode" => "text",
		"disporder" => 2,
		"value" => NULL,
		"gid" => $gid
	);

	$settings[] = array(
		"name" => "latestposts_showtime",
		"title" => $lang->latestposts_showtime,
		"description" => "",
		"optionscode" => "yesno",
		"disporder" => 3,
		"value" => 1,
		"gid" => $gid
	);

	$settings[] = array(
		"name" => "latestposts_rightorleft",
		"title" => $lang->rightorleft,
		"optionscode" => "select
		right=".$lang->latestposts_right."
		left=".$lang->latestposts_left,
		"disporder" => 4,
		"description" => "",
		"value" => "right",
		"gid" => $gid
	);

	foreach($settings as $array => $content)
	{
		$db->insert_query("settings", $content);
	}
	rebuild_settings();

	require_once(MYBB_ROOT."admin/inc/functions_themes.php");

	// Add stylesheet to the master template so it becomes inherited.
	$stylesheet = "\n.latestpost {
		padding: 2px 10px;
	}";
	code;
	$css = array(
		'sid' => NULL,
		'name' => 'latestposts.css',
		'tid' => '1',
		'stylesheet' => $db->escape_string($stylesheet),
		'cachefile' => 'latestposts.css',
		'lastmodified' => TIME_NOW,
	);
	$db->insert_query('themestylesheets', $css);
	cache_stylesheet(1, "latestposts.css", $stylesheet);
	update_theme_stylesheet_list(1, false, true);
}

function latestposts_is_installed()
{
	global $db;
	$query = $db->simple_select("settinggroups", "*", "name='latestposts'");
	if($db->num_rows($query))
	{
		return TRUE;
	}
	return FALSE;
}

function latestposts_activate()
{
	global $db, $lang;
	$lang->load("latestposts");
	$templates['index_sidebar'] = '<table border="0" class="tborder">
	<thead>
	<tr>
	<td class="thead">
	<div><strong>{$lang->latest_posts_title}</strong></div>
	</td>
	</tr>
	</thead>
	<tbody>
	{$postslist}
	</tbody>
	</table>';
	$templates['index_sidebar_post'] = '<tr>
	<td class="trow1 latestpost" valign="top">
	<strong><a href="{$mybb->settings[\'bburl\']}/showthread.php?tid={$tid}">{$postname}</a></strong><br>
	{$lang->latest_post_by} {$lastposterlink} {$lang->latestposttime}
	</td>
	</tr>';

	foreach($templates as $title => $template) {
		$new_template = array('title' => $db->escape_string($title), 'template' => $db->escape_string($template), 'sid' => '-1', 'version' => '1800', 'dateline' => TIME_NOW);
		$db->insert_query('templates', $new_template);
	}

	require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";

	find_replace_templatesets('index', "#" . preg_quote('{$forums}') . "#i", '<div style="float:{$left};width: 74%;">{$forums}</div>
	<div style="float:{$right};width:25%;">{$sidebar}</div>');
}

function latestposts_deactivate()
{
	global $db;
	$db->delete_query("templates", "title IN('index_sidebar','index_sidebar_post')");

	require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";

	find_replace_templatesets('index', "#" . preg_quote('<div style="float:{$left};width: 74%;">{$forums}</div>
	<div style="float:{$right};width:25%;">{$sidebar}</div>') . "#i", '{$forums}');
}

function latestposts_uninstall()
{
	global $db;
	$query = $db->simple_select("settinggroups", "gid", "name='latestposts'");
	$gid = $db->fetch_field($query, "gid");
	if(!$gid) {
		return;
	}
	$db->delete_query("settinggroups", "name='latestposts'");
	$db->delete_query("settings", "gid=$gid");
	rebuild_settings();

	require_once(MYBB_ROOT."admin/inc/functions_themes.php");

	// Remove latestposts.css from the theme cache directories if it exists
	$query = $db->simple_select("themes", "tid");
	while($tid = $db->fetch_field($query, "tid"))
	{
		$css_file = MYBB_ROOT."cache/themes/theme{$tid}/latestposts.css";
		if(file_exists($css_file))
		unlink($css_file);
	}

	update_theme_stylesheet_list("1", false, true);
}

function latestposts()
{
	global $mybb,$lang, $db, $templates, $postslist, $sidebar, $right, $left;
	$lang->load("latestposts");
	$threadlimit = (int) $mybb->settings['latestposts_threadcount'];
	$where = NULL;

	if(!$threadlimit) {
		$threadlimit = 15;
	}
	if($mybb->settings['latestposts_forumskip']) {
		$where .= " AND t.fid NOT IN(" . $mybb->settings['latestposts_forumskip'] . ") ";
	}
	require_once MYBB_ROOT."inc/functions_search.php";

	$unsearchforums = get_unsearchable_forums();
	if($unsearchforums) {
		$where .= " AND t.fid NOT IN ($unsearchforums)";
	}
	$inactiveforums = get_inactive_forums();
	if($inactiveforums) {
		$where .= " AND t.fid NOT IN ($inactiveforums)";
	}

	$permissions = forum_permissions();
	for($i = 0; $i <= sizeof($permissions); $i++){
		if(isset($permissions[$i]['fid']) && ( $permissions[$i]['canview'] == 0 || $permissions[$i]['canviewthreads'] == 0 ))
		{
			$where .= " AND t.fid <> ".$permissions[$i]['fid'];
		}
	}

	$where .= " AND p.visible <> -1";

	$query = $db->query("
	SELECT t.*, u.username AS userusername, u.usergroup, u.displaygroup, lp.usergroup AS lastusergroup, lp.avatar AS lastposteravatar,lp.displaygroup as lastdisplaygroup, p.visible
	FROM ".TABLE_PREFIX."threads t
	LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=t.uid)
	LEFT JOIN ".TABLE_PREFIX."users lp ON (t.lastposteruid=lp.uid)
	LEFT JOIN ".TABLE_PREFIX."posts p ON (t.tid=p.tid AND replyto = 0)
	WHERE 1=1 {$where}
	ORDER BY t.lastpost DESC
	LIMIT $threadlimit
	");
	while($thread = $db->fetch_array($query)) {
		$tid = $thread['tid'];
		$postname = htmlspecialchars_uni($thread['subject']);
		$lastpostlink = get_thread_link($thread['tid'], "", "lastpost");
		$lastposttimeago = my_date("relative", $thread['lastpost']);
		$lastposter = htmlspecialchars_uni($thread['lastposter']);
		$lastposteruid = $thread['lastposteruid'];
		$avatar = format_avatar($thread['lastposteravatar'], 32, 32);
		$lastposteravatar = $avatar['image'];
		$replies = (int) $thread['replies'];

		if($mybb->settings['latestposts_showtime'] == 1) {
			$lang->latestposttime = $lang->sprintf($lang->latestposttime, $lastposttimeago);
		}
		else{
			$lang->latestposttime =  NULL;
		}

		if($lastposteruid == 0) {
			$lastposterlink = $lastposter;
		}
		else {
			$lastposterlink = build_profile_link(format_name($lastposter, $thread['lastusergroup'], $thread['lastdisplaygroup']), $lastposteruid);
		}

		eval("\$postslist .= \"".$templates->get("index_sidebar_post")."\";");
	}

	if($mybb->settings['latestposts_rightorleft'] == "right") {
		$right = "right";
		$left = "left";
	}
	else {
		$right = "left";
		$left = "right";
	}
	eval("\$sidebar = \"".$templates->get("index_sidebar")."\";");
}

?>
