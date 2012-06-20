<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$showImage = sanitize_string($_REQUEST['showImage']);

if (trim($id) == "") {$error = 1;}

if ($error != 1) {
	
	$result = mysql_query("SELECT category, publishState FROM events WHERE id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup = new CategoryUserGroupValidator();
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	if (!$userGroup->allowEditing()) {exit;}
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	if ($row->publishState == 'Unpublished') { 
		
		mysql_query("UPDATE events SET datePublished = '{$time}', publishState = 'Published' WHERE id = '{$id}'");
		
		if ($showImage == "yes") {
			
			print "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_published.gif\" border=\"0\"> Published";
			
		} else {
			
			print "Published";
			
		}
		
	} else {
		
		mysql_query("UPDATE events SET publishState = 'Unpublished' WHERE id = '{$id}'");
		
		if ($showImage == "yes") {
			
			print "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_unpublished.gif\" border=\"0\"> Unpublished";
			
		} else {
			
			print "Unpublished";
			
		}
		
	}
	
}

?>