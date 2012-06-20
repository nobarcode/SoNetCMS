<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$id = sanitize_string($_REQUEST['id']);

if (trim($groupId) == "" || trim($id) == "") {exit;}

//validate group and requesting user access rights
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4) {

	//if the user is not an admin, validate that the user is allowed to access the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}

}

if (trim($id) != "") {
	
	$result = mysql_query("SELECT usernameCreated, publishState, body FROM events WHERE id = '{$id}' AND groupId = '{$groupId}' LIMIT 1");
		
	//catch ivalid ids
	if (mysql_num_rows($result) > 0) {
		
		//content providers shouldn't be able to edit a published event
		if (($row->publishState == "Published" && $_SESSION['userLevel'] == 4) && $row->usernameCreated != $_SESSION['username']) {
			
			exit;
			
		}
		
		$row = mysql_fetch_object($result);
		print "$row->body";
		
	}
	
}

?>