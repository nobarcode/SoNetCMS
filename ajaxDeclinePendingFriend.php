<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$owner = sanitize_string($_REQUEST['owner']);

if (trim($owner) == "" || trim($_SESSION['username']) == "") {
	
	exit;
	
}

$result = mysql_query("DELETE FROM friends WHERE owner = '{$owner}' AND friend = '{$_SESSION['username']}' and status = 'pending'");

if ($result) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Friend request declined.');";
	print "$('#message_box').show();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Unknown error! Please try your request again.');";
	print "$('#message_box').show();";
	exit;
	
}

?>