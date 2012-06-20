<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$username = sanitize_string($_REQUEST['username']);

if (trim($groupId) == "" || trim($username) == "" || trim($_SESSION['username']) == "") {$error = 1;}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

//validate that the user being edited is in the requested group and NOT the owner
$result = mysql_query("SELECT groups.id, groups.name FROM groupsMembers INNER JOIN groups ON groups.id = groupsMembers.parentId WHERE groupsMembers.parentId = '{$groupId}' AND groupsMembers.username = '{$username}' AND groupsMembers.memberLevel != '1'");

if (mysql_num_rows($result) == 0) {
	
	$error = 1;
	
}

$row = mysql_fetch_object($result);
$groupName = $row->name;

if ($error != 1) {
	
	$result = mysql_query("SELECT memberLevel FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$username}' LIMIT 1");
	$row = mysql_fetch_object($result);

	if ($row->memberLevel == '3') { 
		
		mysql_query("UPDATE groupsMembers SET memberLevel = '2' WHERE parentId = '{$groupId}' AND username = '{$username}'");
		
	} elseif ($row->memberLevel == '2') {
		
		mysql_query("UPDATE groupsMembers SET memberLevel = '3' WHERE parentId = '{$groupId}' AND username = '{$username}'");
		
	}
	
}

?>