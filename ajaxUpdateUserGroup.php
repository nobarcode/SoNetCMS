<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$name = sanitize_string($_REQUEST['name']);
$restrictViewing = sanitize_string($_REQUEST['restrictViewing']);
$allowEditing = sanitize_string($_REQUEST['allowEditing']);

if (trim($id) == "") {exit;}
if (trim($name) == "") {$error = 1; $errorMessage .= "- Please supply a name.<br>";}

if ($error != 1) {
	
	if (trim($restrictViewing) == "") {
		
		$restrictViewing = '0';
		
	}
	
	if (trim($allowEditing) == "") {
		
		$allowEditing = '0';
		
	}
	
	$result = mysql_query("UPDATE userGroups SET name = '{$name}', restrictViewing = '{$restrictViewing}', allowEditing = '{$allowEditing}' WHERE id = '{$id}'");
	
	$showMessage = "User Group updated successfully.";
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div>$showMessage</div>');";
	print "$('#message_box').show();";
	print "cancelEditUserGroup();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>