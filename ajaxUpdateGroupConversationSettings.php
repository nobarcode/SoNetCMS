<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$parentId = sanitize_string($_REQUEST['parentId']);
$title = sanitize_string($_REQUEST['title']);
$restricted = sanitize_string($_REQUEST['restricted']);
$locked = sanitize_string($_REQUEST['locked']);

if (trim($parentId) == "") {exit;}

//load group information
$result = mysql_query("SELECT conversations.groupId FROM conversations INNER JOIN groups ON groups.id = conversations.groupId WHERE conversations.id = '{$parentId}' LIMIT 1");

//catch ivalid conversation ids
if (mysql_num_rows($result) > 0) {
	
	$row = mysql_fetch_object($result);
	$groupId = $row->groupId;
	
	//validate group and requesting user access rights
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3) {
	
		//if the user is not an admin, validate that the user is allowed to access the requested group
		$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");
	
		if (mysql_num_rows($result) == 0) {
	
			exit;
	
		}
	
	}
	
	//update conversation
	mysql_query("UPDATE conversations SET title = '{$title}', restricted = '{$restricted}', locked = '{$locked}' WHERE conversations.id = '{$parentId}'");
	
	$title = htmlentities(unsanitize_string($title));
	
	header('Content-type: application/javascript');
	
	//update the title
	print "$('#conversation_title').html('$title');";
	
	if ($locked == 1) {
		
		print "if(!$('#conversation_title').hasClass('locked')) {";
		print "$('#conversation_title').addClass('locked');";
		print "$('#conversation_title').attr('title', 'Topic is Locked');";
		print "}";
		
	} else {
		
		print "if($('#conversation_title').hasClass('locked')) {";
		print "$('#conversation_title').removeClass('locked');";
		print "$('#conversation_title').attr(title, '');";
		print "}";
		
	}
	
}

?>