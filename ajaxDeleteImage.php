<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$imageId = sanitize_string($_REQUEST['imageId']);

if (trim($id) == "" || trim($imageId) == "") {exit;}

$result = mysql_query("SELECT category FROM documents WHERE id = '{$id}' LIMIT 1");
$row = mysql_fetch_object($result);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowEditing()) {exit;}

$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM imagesDocuments WHERE parentId = '{$id}'"), 0, "totalRows");
$result = mysql_query("SELECT weight FROM imagesDocuments WHERE parentId = '{$id}' AND id = '{$imageId}' ORDER BY weight ASC");

if (mysql_num_rows($result) > 0) {
	
	$row = mysql_fetch_object($result);
	$weight = $row->weight;
	
	mysql_query("DELETE FROM imagesDocuments WHERE parentId = '{$id}' AND id = '{$imageId}'");
	
	for ($x = $weight + 1; $x <= $totalRows; $x++) {
		
		mysql_query("UPDATE imagesDocuments SET weight = (weight-1) WHERE parentId = '{$id}' AND weight = '{$x}'");
		
	}
	
}

//delete the document's image gallery comments and any votes associated to each image gallery comment
mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'documentImageComment' WHERE commentsImages.imageId = '{$imageId}' AND commentsImages.type = 'documentImageComment'");

//delete versioning information
mysql_query("DELETE FROM documentVersioning WHERE parentId = '{$imageId}' AND documentType = 'documentImage'");

?>