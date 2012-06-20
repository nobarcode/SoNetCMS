<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$imageId = sanitize_string($_REQUEST['imageId']);

if (trim($id) == "" || trim($imageId) == "") {$error = 1;}

$result = mysql_query("SELECT category FROM documents WHERE id = '{$id}' LIMIT 1");
$row = mysql_fetch_object($result);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowEditing()) {exit;}

if ($error != 1) {
	
	$result = mysql_query("SELECT inSeriesImage FROM imagesDocuments WHERE parentId = '{$id}' AND id = '{$imageId}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	if ($row->inSeriesImage == 0) { 
		
		mysql_query("UPDATE imagesDocuments SET inSeriesImage = 1 WHERE parentId = '{$id}' AND id = '{$imageId}'");
		
		print "1";
		
	} else {
		
		mysql_query("UPDATE imagesDocuments SET inSeriesImage = 0 WHERE parentId = '{$id}' AND id = '{$imageId}'");
		
		print "0";
		
	}
	
}

?>