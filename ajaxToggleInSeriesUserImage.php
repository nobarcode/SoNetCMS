<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$imageId = sanitize_string($_REQUEST['imageId']);

if (trim($_SESSION['username']) == "" || trim($imageId) == "") {$error = 1;}

if ($error != 1) {
	
	$result = mysql_query("SELECT inSeriesImage FROM imagesUsers WHERE parentId = '{$_SESSION['username']}' AND id = '{$imageId}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	if ($row->inSeriesImage == 0) { 
		
		mysql_query("UPDATE imagesUsers SET inSeriesImage = 1 WHERE parentId = '{$_SESSION['username']}' AND id = '{$imageId}'");
		
		print "1";
		
	} else {
		
		mysql_query("UPDATE imagesUsers SET inSeriesImage = 0 WHERE parentId = '{$_SESSION['username']}' AND id = '{$imageId}'");
		
		print "0";
		
	}
	
}

?>