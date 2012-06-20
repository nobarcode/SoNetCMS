<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$s = sanitize_string($_REQUEST['s']);
$image = sanitize_string($_REQUEST['image']);

if (trim($groupId) == "" || trim($s) == "" || trim($image[0]) == "") {$error = 1;}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

parse_str($image);

//grab the weight of the first image in the list
$result = mysql_query("SELECT * FROM imagesGroups WHERE parentId = '{$groupId}' ORDER BY weight LIMIT $s, 1");
$row = mysql_fetch_object($result);
$y = $row->weight;

if ($error != 1) {
	
	for ($x = 0; $x < count($image); $x++) {
		
		mysql_query("UPDATE imagesGroups SET weight = '{$y}' WHERE id = '{$image[$x]}' AND parentId = '{$groupId}'");
		
		$y++;
		
	}
	
}

?>