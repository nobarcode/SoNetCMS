<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$imageId = sanitize_string($_REQUEST['imageId']);

if (trim($_SESSION['username']) == "" || trim($imageId) == "") {$error = 1;}

$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM imagesUsers WHERE username = '{$_SESSION['username']}'"), 0, "totalRows");
$result = mysql_query("SELECT weight FROM imagesUsers WHERE parentId = '{$_SESSION['username']}' AND id = '{$imageId}' ORDER BY weight ASC");

if (mysql_num_rows($result) > 0) {
	
	$row = mysql_fetch_object($result);
	$weight = $row->weight;
	
	mysql_query("DELETE FROM imagesUsers WHERE parentId = '{$_SESSION['username']}' AND id = '{$imageId}'");
	
	for ($x = $weight + 1; $x <= $totalRows; $x++) {
		
		mysql_query("UPDATE imagesUsers SET weight = (weight-1) WHERE parentId = '{$_SESSION['username']}' AND weight = '{$x}'");
		
	}
	
}

//delete the document's image gallery comments and any votes associated to each image gallery comment
mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'userImageComment' WHERE commentsImages.imageId = '{$imageId}' AND commentsImages.type = 'userImageComment'");

?>