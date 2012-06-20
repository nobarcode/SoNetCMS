<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$id = sanitize_string($_REQUEST['id']);

if (trim($groupId) == "") {exit;}

//validate group
$result = mysql_query("SELECT id FROM groups WHERE id = '{$groupId}'");

if (mysql_num_rows($result) == 0) {

	exit;

}

//validate group and requesting user access rights
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3) {

	//if the user is not an admin, validate that the user is allowed to access the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}

}

if (trim($groupId) != "") {
	
	//delete the document's comments and any votes associated to each comment
	mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment' WHERE commentsDocuments.parentId = '{$groupId}' AND commentsDocuments.type = 'eventComment'");
	
	//delete the document's image gallery comments and any votes associated to each image gallery comment
	mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'eventImageComment' WHERE commentsImages.parentId = '{$groupId}' AND commentsImages.type = 'eventImageComment'");
	
	//delete the document's image gallery
	mysql_query("DELETE imagesEvents FROM imagesEvents WHERE parentId = '{$groupId}'");
	
	//delete the document and its associated votes if there are any
	mysql_query("DELETE FROM events WHERE id = '{$id}' AND groupId = '{$groupId}'");
	
	header("location: groupEventEditorList.php?groupId=$groupId");
	
}

?>