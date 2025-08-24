<?php

// Author: Paul H. ( https://community.mybb.com/user-37431.html )
// Code Changes By: Brian. ( https://community.mybb.com/user-115119.html )
// bans.php created on 10-15-17
// Support: https://community.mybb.com/thread-213593.html

define('IN_MYBB',1);
require_once('global.php');
define("PAGINATION",20);
$lang->load("modcp");
add_breadcrumb("Banned Users");
if(isset($mybb->input['asc'])) {
	$options['order_dir'] = 'asc';
	$ascdesc = '&asc';
	$ascdesci = '&desc'; } else {
	$options['order_dir'] = 'desc';
	$ascdesc = '&desc';
	$ascdesci = '&asc'; }
switch($mybb->input['sortby']) {
	case 'issued':
	$options['order_by'] = 'dateline';
	break;
	case 'lifted':
	$options['order_by'] = 'lifted';
	break;
	default:
	$options['order_by'] = 'dateline'; }
if(isset($mybb->input['page'])) {
$page = (int)$mybb->input['page']; } else {
$page = 1; }
$extra = "&orderby={$options['order_by']}{$ascdesc}";
$query = $db->simple_select("banned", "COUNT(uid) AS count");
$bannum = $db->fetch_field($query, "count");
$multipage = multipage($bannum,PAGINATION,$page,'bans.php?page={page}'.$extra);
$options['limit'] = PAGINATION;
$options['limit_start'] = ($page - 1) * PAGINATION;
$query = $db->simple_select('banned','*',null,$options);
$bans = '<tr>
<td class="tcat" align="center"><span class="smalltext"><strong>'.$lang->username.'</strong></span></td>
<td class="tcat" align="center"><span class="smalltext"><strong>'.$lang->reason.'</strong></span></td>
<td class="tcat" align="center"><span class="smalltext"><strong>'.$lang->ban_bannedby.'</strong></span></td>
<td class="tcat" align="center"><span class="smalltext"><strong><a href="bans.php?sortby=issued'.$ascdesci.'">Ban Date</a></strong></span></td>
<td class="tcat" align="center"><span class="smalltext"><strong><a href="bans.php?sortby=lifted'.$ascdesci.'">Unban Date</a></strong></span></td>
<td class="tcat" align="center"><span class="smalltext"><strong>User Email</strong></span></td>
<td class="tcat" align="center"><span class="smalltext"><strong>Registration IP</strong></span></td>
<td class="tcat" align="center"><span class="smalltext"><strong>Latest IP</strong></span></td>
</tr>';
$banlist = '';
$bantimes = fetch_ban_times();
while($banned = $db->fetch_array($query)) {
	$user = get_user($banned['uid']);
	$bannedby = get_user($banned['admin']);
	if($banned['lifted'] == 'perm' || $banned['lifted'] == '' || $banned['bantime'] == 'perm' || $banned['bantime'] == '---') {
	$banlength = $lang->permanent;
	$timeremaining = $lang->na; } else {
	$banlength = $bantimes[$banned['bantime']];
	$remaining = $banned['lifted']-TIME_NOW;
	$timeremaining = nice_time($remaining, array('short' => 1, 'seconds' => false))."";
	if($remaining < 3600) {
	$timeremaining = "<span style=\"color: red;\">({$timeremaining} {$lang->ban_remaining})</span>"; }
	else if($remaining < 86400) {
	$timeremaining = "<span style=\"color: maroon;\">({$timeremaining} {$lang->ban_remaining})</span>"; }
	else if($remaining < 604800) {
	$timeremaining = "<span style=\"color: green;\">({$timeremaining} {$lang->ban_remaining})</span>"; } else {
	$timeremaining = "({$timeremaining} {$lang->ban_remaining})"; }
	$timeremaining = my_date($mybb->settings['dateformat'],$banned['lifted'])." <br/ ><span class=\"smalltext\">{$timeremaining}</span>"; }
$banlist .= "<tr>
<td class=\"trow1\" align=\"center\">".build_profile_link($user['username'], $user['uid'])."</td>
<td class=\"trow1\" align=\"center\">{$banned['reason']}</td>
<td class=\"trow1\" align=\"center\">".build_profile_link($bannedby['username'], $bannedby['uid'])."</td>
<td class=\"trow1\" align=\"center\">".my_date($mybb->settings['dateformat'],$banned['dateline'])."</td>
<td class=\"trow1\" align=\"center\">{$timeremaining}</td>
<td class=\"trow1\" align=\"center\">".build_profile_link($user['email'], $user['uid'])."</td>
<td class=\"trow1\" align=\"center\">".my_inet_ntop($db->unescape_binary($user['regip']))."</td>
<td class=\"trow1\" align=\"center\">".build_profile_link($user['lastip'], $user['uid'])."</td>
</tr>";}
if($banlist == '') {
$banlist = '<tr><td colspan="8">'.$lang->no_banned.'</td></tr>';}
$bans .= $banlist;
$page = "<html>
<head>
<title>Banned Users</title>
{$headerinclude}
</head>
<body>
{$header}
<br />

<table border=\"0\" cellspacing=\"{$theme['borderwidth']}\" cellpadding=\"{$theme['tablespace']}\" class=\"tborder\">
<tr>
<td class=\"thead\" colspan=\"8\"><span class=\"smalltext\"><strong>Banned Users List</strong></span></td>
</tr>
{$bans}
</table>

{$multipage}
{$footer}
</body>
</html>";

output_page($page);
?>
