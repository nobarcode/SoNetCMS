<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$parentId = sanitize_string($_REQUEST['parentId']);

if (trim($parentId) == "") {exit;}

//load group information
$result = mysql_query("SELECT conversations.groupId, conversations.title, conversations.restricted, conversations.locked FROM conversations INNER JOIN groups ON groups.id = conversations.groupId WHERE conversations.id = '{$parentId}' LIMIT 1");

//catch ivalid conversation ids
if (mysql_num_rows($result) > 0) {
	
	$row = mysql_fetch_object($result);
	$groupId = $row->groupId;
	
	//validate group and requesting user access rights
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
		//if the user is not an admin, validate that the user is allowed to access the requested group
		$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");
	
		if (mysql_num_rows($result) == 0) {
	
			exit;
	
		}
	
	}
	
	$escapeTitle = preg_replace('/\\\/', '\\\\\\', $row->title);
	$escapeTitle = preg_replace('/\'/', '\\\'', $escapeTitle);
	
	//check if this topic is restricted
	if ($row->restricted == 1) {
		
		$showRestrictedChecked = "true";
		
	} else {
		
		$showRestrictedChecked = "false";
		
	}
	
	//check if this topic is locked
	if ($row->locked == 1) {
		
		$showLockedChecked = "true";
		
	} else {
		
		$showLockedChecked = "false";
		
	}
	
	header('Content-type: application/javascript');
	
	//update the title
	print "$('#title').val('$escapeTitle');";
	
	//update restricted
	print "$('#restricted').attr('checked',$showRestrictedChecked);";
	
	//update restricted
	print "$('#locked').attr('checked', $showLockedChecked);";
	
}

?>