<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$name = sanitize_string($_REQUEST['name']);
$restrictViewing = sanitize_string($_REQUEST['restrictViewing']);
$allowEditing = sanitize_string($_REQUEST['allowEditing']);

if (trim($name) == "") {$error = 1; $errorMessage .= "- Please supply a name.<br>";}

$matchRows = mysql_result(mysql_query("SELECT COUNT(1) AS NumRows FROM userGroups WHERE name = '{$name}'"), 0, "NumRows");

if ($matchRows > 0) {$error = 1; $errorMessage .= "- The supplied user group already exists.<br>";}

if ($error != 1) {
	
	if (trim($restrictViewing) == "") {
		
		$restrictViewing = '0';
		
	}
	
	if (trim($allowEditing) == "") {
		
		$allowEditing = '0';
		
	}
	
	$result = mysql_query("INSERT INTO userGroups (name, restrictViewing, allowEditing) VALUES ('{$name}', '{$restrictViewing}', '{$allowEditing}')");
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div>User Group added successfully.</div>');";
	print "$('#message_box').show();";
	print "$('#add_user_group')[0].reset();";
	print "regenerateList('', '', 'asc', 'name', '');";
	exit;
	
} else {

	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>