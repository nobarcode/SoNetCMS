<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$multipleId = sanitize_string($_REQUEST['multipleId']);

if (trim($groupId) == "" || !is_array($multipleId) || trim($_SESSION['username']) == "") {$error = 1;}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

if ($error != 1) {
	
	for ($x = 0; $x < count($multipleId); $x++) {
		
		mysql_query("DELETE FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$multipleId[$x]}' AND memberLevel != '1'");
		
	}
	
}

?>