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
	
	$result = mysql_query("SELECT activeState FROM featuredDocuments WHERE id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);

	if ($row->activeState == 'Inactive') { 
		
		mysql_query("UPDATE featuredDocuments SET activeState = 'Active' WHERE id = '{$id}'");
		
		print "Active";
		
	} else {
		
		mysql_query("UPDATE featuredDocuments SET activeState = 'Inactive' WHERE id = '{$id}'");
		
		print "Inactive";
		
	}
	
}

?>