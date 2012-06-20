<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$image = sanitize_string($_REQUEST['image']);

if (trim($_SESSION['username']) == "" || trim($s) == "" || trim($image[0]) == "") {$error = 1;}

//parse the list of images
parse_str($image);

//grab the weight of the first image in the list
$result = mysql_query("SELECT * FROM imagesUsers WHERE parentId = '{$_SESSION['username']}' ORDER BY weight LIMIT $s, 1");
$row = mysql_fetch_object($result);
$y = $row->weight;

if ($error != 1) {
	
	for ($x = 0; $x < count($image); $x++) {
		
		mysql_query("UPDATE imagesUsers SET weight = '{$y}' WHERE id = '{$image[$x]}' AND parentId = '{$_SESSION['username']}'");
		
		$y++;
		
	}
	
}

?>