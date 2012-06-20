<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$imageId = sanitize_string($_REQUEST['imageId']);

if (trim($groupId) == "" || trim($imageId) == "") {exit;}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM imagesGroups WHERE parentId = '{$groupId}'"), 0, "totalRows");
$result = mysql_query("SELECT weight FROM imagesGroups WHERE parentId = '{$groupId}' AND id = '{$imageId}' ORDER BY weight ASC");

if (mysql_num_rows($result) > 0) {
	
	$row = mysql_fetch_object($result);
	$weight = $row->weight;
	
	mysql_query("DELETE FROM imagesGroups WHERE parentId = '{$groupId}' AND id = '{$imageId}'");
	
	for ($x = $weight + 1; $x <= $totalRows; $x++) {
		
		mysql_query("UPDATE imagesGroups SET weight = (weight-1) WHERE parentId = '{$groupId}' AND weight = '{$x}'");
		
	}
	
}

//delete the document's image gallery comments and any votes associated to each image gallery comment
mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'documentImageComment' WHERE commentsImages.imageId = '{$imageId}' AND commentsImages.type = 'documentImageComment'");

?>