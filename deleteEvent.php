<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

if (trim($id) != "") {
	
	$result = mysql_query("SELECT category FROM events WHERE id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);

	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup = new CategoryUserGroupValidator();
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	if (!$userGroup->allowEditing()) {exit;}
	
	//delete the document's comments and any votes associated to each comment
	mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment' WHERE commentsDocuments.parentId = '{$id}' AND commentsDocuments.type = 'eventComment'");
	
	//delete the document's image gallery comments and any votes associated to each image gallery comment
	mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'eventImageComment' WHERE commentsImages.parentId = '{$id}' AND commentsImages.type = 'eventImageComment'");
	
	//delete the document's image gallery
	mysql_query("DELETE imagesEvents FROM imagesEvents WHERE parentId = '{$id}'");
	
	//delete the document and its associated votes if there are any
	mysql_query("DELETE FROM events WHERE id = '{$id}'");
	
	//delete document versioning information
	mysql_query("DELETE FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'event'");
	
	//delete document edit tracking data
	mysql_query("DELETE FROM documentEditTracking WHERE documentType = 'event' AND id = '{$id}'");
	
	header("location: eventEditorList.php");
	
}

?>