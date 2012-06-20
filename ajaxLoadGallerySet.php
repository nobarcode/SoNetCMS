<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$parentId = sanitize_string($_REQUEST['parentId']);
$requestedPage = sanitize_string($_REQUEST['requestedPage']);
$type = sanitize_string($_REQUEST['type']);

if (trim($parentId) == "" || trim($requestedPage) == "" || trim($type) == "") {
	
	exit;
	
}

//define start page based on $reqestedPage value
$s = $requestedPage * 3;

switch ($type) {
	
	case "document":
		
		$query = "SELECT id, imageUrl FROM imagesDocuments WHERE parentId = '{$parentId}' AND inSeriesImage = 1 ORDER BY weight ASC LIMIT $s, 3";
		break;
	
	case "user":
		
		$query = "SELECT id, imageUrl FROM imagesUsers WHERE parentId = '{$parentId}' AND inSeriesImage = 1 ORDER BY weight ASC LIMIT $s, 3";
		break;
		
	case "group":
		
		$query = "SELECT id, imageUrl FROM imagesGroups WHERE parentId = '{$parentId}' AND inSeriesImage = 1 ORDER BY weight ASC LIMIT $s, 3";
		break;
		
}

//load the thumbnails
$result = mysql_query($query);

if (mysql_num_rows($result) > 0) {
	
	while ($row = mysql_fetch_object($result)) {
		
		$showThumbs .= "<div id=\"image_$row->id\" class=\"thumb_image_container\" onclick=\"showImage('$row->id');\"><div class=\"image\"><img src=\"/file.php?load=$row->imageUrl&thumbs=true\" border=\"0\"></div></div>";
		
	}
	
}

//return thumbnails to ajax function
print $showThumbs;

?>