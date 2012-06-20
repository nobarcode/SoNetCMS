<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$friend = sanitize_string($_REQUEST['friend']);

if (trim($friend) == "" || trim($_SESSION['username']) == "") {
	
	exit;
	
}

//delete friend from $_SESSION['username']'s list and reorder their list for them
$weight = mysql_result(mysql_query("SELECT weight FROM friends WHERE owner = '{$_SESSION['username']}' AND friend = '{$friend}'"), 0, "weight");
$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM friends WHERE owner = '{$_SESSION['username']}'"), 0, "totalRows");

if ($totalRows == 0) {$error = 1;}

if ($error != 1) {
	
	$result = mysql_query("DELETE FROM friends WHERE owner = '{$_SESSION['username']}' AND friend = '{$friend}' AND status = 'approved'");
	
	for ($x = $weight + 1; $x <= $totalRows; $x++) {
		
		mysql_query("UPDATE friends SET weight = (weight-1) WHERE owner = '{$_SESSION['username']}' AND weight = '{$x}'");
		
	}
	
}

//delete $_SESSION['username'] from friend's list and reorder their list for them
$weight = mysql_result(mysql_query("SELECT weight FROM friends WHERE owner = '{$friend}' AND friend = '{$_SESSION['username']}'"), 0, "weight");
$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM friends WHERE owner = '{$friend}'"), 0, "totalRows");

if ($totalRows == 0) {$error = 1;}

if ($error != 1) {
	
	$result = mysql_query("DELETE FROM friends WHERE owner = '{$friend}' AND friend = '{$_SESSION['username']}' AND status = 'approved'");
	
	for ($x = $weight + 1; $x <= $totalRows; $x++) {
		
		mysql_query("UPDATE friends SET weight = (weight-1) WHERE owner = '{$friend}' AND weight = '{$x}'");
		
	}
	
}

if ($result) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Friend deleted.');";
	print "$('#message_box').show();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Unknown error! Please try your request again.');";
	print "$('#message_box').show();";
	exit;
	
}

?>