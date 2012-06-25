<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$multipleId = sanitize_string($_REQUEST['multipleId']);

if (trim($groupId) == "") {exit;}

//validate group and requesting user access rights
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 || $_SESSION['userLevel'] != 3) {

	//if the user is not an admin, validate that the user is allowed to access the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}

}

if (!is_array($multipleId)) {exit;}
	
foreach($multipleId as $id) {
	
	//update the publishState based on its current state
	mysql_query("UPDATE events SET publishState = IF(publishState = 'Unpublished', 'Published', IF(publishState = 'Published', 'Unpublished', publishState)) WHERE id = '{$id}' AND groupId = '{$groupId}'");
	
}

?>