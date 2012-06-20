<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$id = sanitize_string($_REQUEST['id']);
$showImage = sanitize_string($_REQUEST['showImage']);

if (trim($groupId) == "" || trim($id) == "") {$error = 1;}

//validate group and requesting user access rights
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3) {

	//if the user is not an admin, validate that the user is allowed to access the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}

}

if ($error != 1) {
	
	$result = mysql_query("SELECT publishState FROM events WHERE id = '{$id}' AND groupId = '{$groupId}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	if ($row->publishState == 'Unpublished') { 
		
		mysql_query("UPDATE events SET datePublished = '{$time}', publishState = 'Published' WHERE id = '{$id}' AND groupId = '{$groupId}'");
		
		if ($showImage == "yes") {
			
			print "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_published.gif\" border=\"0\"> Published";
			
		} else {
			
			print "Published";
			
		}
		
	} else {
		
		mysql_query("UPDATE events SET publishState = 'Unpublished' WHERE id = '{$id}' AND groupId = '{$groupId}'");
		
		if ($showImage == "yes") {
			
			print "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_unpublished.gif\" border=\"0\"> Unpublished";
			
		} else {
			
			print "Unpublished";
			
		}
		
	}
	
}

?>