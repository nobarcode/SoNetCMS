<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

if (trim($id) == "") {$error = 1;}

if ($error != 1) {
	
	//load the parentId (conversation id) and author for this conversation post
	$result = mysql_query("SELECT parentId, author FROM conversationsPosts WHERE id = '{$id}'");
	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
	$row = mysql_fetch_object($result);
	$parentId = $row->parentId;
	$author = $row->author;
	
	//load the groupId for this conversation
	$result = mysql_query("SELECT conversations.groupId FROM conversations INNER JOIN groups ON groups.id = conversations.groupId WHERE conversations.id = '{$parentId}' LIMIT 1");
	if (mysql_num_rows($result) == 0) {

		exit;

	}

	$row = mysql_fetch_object($result);
	$groupId = $row->groupId;
	
	//check if the user is a group admin
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");
	if (mysql_num_rows($result) > 0) {

		$isGroupAdmin = 1;

	}
	
	//delete conversation post
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $author || $isGroupAdmin == 1) {
		
		mysql_query("DELETE FROM conversationsPosts WHERE id = '{$id}'");
		
	}
	
}

?>