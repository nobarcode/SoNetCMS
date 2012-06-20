<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$value = sanitize_string($_REQUEST['value']);

if (trim($id) == "" || trim($value) == "") {
	
	exit;
	
}

$time = date("Y-m-d H:i:s", time());

//load the parentId (conversation id) and author for this conversation post
$result = mysql_query("SELECT parentId, author FROM conversationsPosts WHERE id = '{$id}'");
if (mysql_num_rows($result) == 0) {

	exit;

}

$row = mysql_fetch_object($result);
$parentId = $row->parentId;
$author = $row->author;

//load the groupId for this conversation
$result = mysql_query("SELECT conversations.groupId, conversations.locked FROM conversations INNER JOIN groups ON groups.id = conversations.groupId WHERE conversations.id = '{$parentId}' LIMIT 1");
if (mysql_num_rows($result) == 0) {

	exit;

}

$row = mysql_fetch_object($result);
$groupId = $row->groupId;
$locked = $row->locked;

//check if the user is a group admin
$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");
if (mysql_num_rows($result) > 0) {

	$isGroupAdmin = 1;

}

//if this topic is locked and the user is not a group admin, exit
if ($locked == 1 && $isGroupAdmin != 1) {
	
	exit;
	
}

//update conversation post
if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['username'] == $author || $isGroupAdmin == 1) {
	
	//strip invalid tags
	$value = strip_tags($value, '<p><br /><span><strong><em><u><a><img>');
	
	$result = mysql_query("UPDATE conversationsPosts SET body = '{$value}', dateUpdated = '{$time}' WHERE id = '{$id}'");
	
}

$body = htmlentities(unsanitize_string($value));
$body = preg_replace("/\n|\r\n/", "<br>", $body);
$body = preg_replace('/\'/', '\\\'', $body);

header('Content-type: application/javascript');
print "$('#body_$id').html('$body');";

?>