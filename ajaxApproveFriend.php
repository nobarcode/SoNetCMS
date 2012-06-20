<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$owner = sanitize_string($_REQUEST['owner']);
$weight = sanitize_string($_REQUEST['weight']);

if (trim($_SESSION['username']) == "" || trim($owner) == "") {
	
	exit;
	
}

$time = time();

//check if a request from $owner exists for current user
$result = mysql_query("SELECT friend FROM friends WHERE owner = '{$owner}' AND friend = '{$_SESSION['username']}' AND status = 'pending'");

if(mysql_num_rows($result) > 0) {
	
	//update the owner's data
	$result = mysql_query("SELECT weight FROM friends WHERE owner = '{$owner}' AND status = 'approved'");
	$weight = mysql_num_rows($result) + 1;
	$result = mysql_query("UPDATE friends SET weight = '{$weight}', status = 'approved' WHERE owner = '{$owner}' AND friend = '{$_SESSION['username']}' AND status = 'pending'");	
	
	if ($result) {
		
		//delete a pending request from current user if there happens to be one
		mysql_query("DELETE FROM friends WHERE owner = '{$_SESSION['username']}' AND friend = '{$owner}' AND status = 'pending'");
		
		//update the current user's friends data
		$result = mysql_query("SELECT weight FROM friends WHERE owner = '{$_SESSION['username']}' AND status = 'approved'");
		$weight = mysql_num_rows($result) + 1;
		$result = mysql_query("INSERT INTO friends (owner, friend, dateAdded, status, weight) VALUES ('{$_SESSION['username']}', '{$owner}', {$time}, 'approved', {$weight})");
		
		if ($result) {
			
			header('Content-type: application/javascript');
			print "$('#message_box').html('Friend request approved!');";
			print "$('#message_box').show();";
			exit;
			
		}
		
	}
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Unknown error! Please try your request again.');";
	print "$('#message_box').show();";
	exit;
	
}

?>