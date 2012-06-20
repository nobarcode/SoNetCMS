<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$id = sanitize_string($_REQUEST['id']);

if (trim($groupId) == "" || trim($id) == "" || trim($_SESSION['username']) == "") {$error = 1;}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

if ($error != 1) {
	
	$result = mysql_query("SELECT sticky FROM conversations WHERE groupId = '{$groupId}' AND id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	if ($row->sticky == 0) { 
		
		mysql_query("UPDATE conversations SET sticky = 1 WHERE groupId = '{$groupId}' AND id = '{$id}'");
		
	} else {
		
		mysql_query("UPDATE conversations SET sticky = 0 WHERE groupId = '{$groupId}' AND id = '{$id}'");
		
	}
	
}

?>