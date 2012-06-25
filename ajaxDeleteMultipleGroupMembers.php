<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$multipleId = sanitize_string($_REQUEST['multipleId']);

if (trim($groupId) == "" || !is_array($multipleId) || trim($_SESSION['username']) == "") {exit}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

foreach($multipleId as $id) {
	
	mysql_query("DELETE FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$id}' AND memberLevel != '1'");
	
}

?>