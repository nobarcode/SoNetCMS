<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

if (trim($id) == "") {$error = 1;}

if ($error != 1) {
	
	$result = mysql_query("SELECT publishState FROM announcements WHERE id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	if ($row->publishState == 'Unpublished') { 
		
		mysql_query("UPDATE announcements SET publishState = 'Published' WHERE id = '{$id}'");
		print "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_published.gif\" border=\"0\"> Published";
		
	} else {
		
		mysql_query("UPDATE announcements SET publishState = 'Unpublished' WHERE id = '{$id}'");
		print "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_unpublished.gif\" border=\"0\"> Unpublished";
		
	}
	
}

?>