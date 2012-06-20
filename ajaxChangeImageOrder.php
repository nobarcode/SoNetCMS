<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$s = sanitize_string($_REQUEST['s']);
$image = sanitize_string($_REQUEST['image']);

if (trim($id) == "" || trim($s) == "" || trim($image[0]) == "") {$error = 1;}

$result = mysql_query("SELECT category FROM documents WHERE id = '{$id}' LIMIT 1");
$row = mysql_fetch_object($result);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowEditing()) {exit;}

parse_str($image);

//grab the weight of the first image in the list
$result = mysql_query("SELECT * FROM imagesDocuments WHERE parentId = '{$id}' ORDER BY weight LIMIT $s, 1");
$row = mysql_fetch_object($result);
$y = $row->weight;

if ($error != 1) {
	
	for ($x = 0; $x < count($image); $x++) {
		
		mysql_query("UPDATE imagesDocuments SET weight = '{$y}' WHERE id = '{$image[$x]}' AND parentId = '{$id}'");
		
		$y++;
		
	}
	
}

?>