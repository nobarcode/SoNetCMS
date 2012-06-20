<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$assigned = sanitize_string($_REQUEST['assigned']);
$username = sanitize_string($_REQUEST['username']);
$type = sanitize_string($_REQUEST['type']);

if (!is_array($assigned) || trim($username) == "") {$error = 1;}

if ($error != 1) {
	
	for ($x = 0; $x < count($assigned); $x++) {
		
		mysql_query("DELETE FROM userGroupsMembers WHERE groupId = '{$assigned[$x]}' AND username = '{$username}'");
		
	}
	
	$assignedGroups = loadGroups($username, 'assigned');
	$availableGroups = loadGroups($username, 'available');
	
	header('Content-type: application/javascript');
	print "$('#assigned').html('$assignedGroups');";
	print "$('#available').html('$availableGroups');";
	
}

function loadGroups($username, $type) {
	
	if ($type == 'assigned') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM userGroupsMembers INNER JOIN userGroups ON userGroups.id = userGroupsMembers.groupId WHERE userGroupsMembers.username = '{$username}' ORDER BY userGroups.name ASC";
		
	} elseif ($type == 'available') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM userGroups LEFT OUTER JOIN userGroupsMembers ON userGroupsMembers.groupId = userGroups.id AND userGroupsMembers.username =  '{$username}' WHERE userGroupsMembers.groupId IS NULL ORDER BY name ASC";
		
	}
	
	$result = mysql_query($loadType);
	
	while($row = mysql_fetch_object($result)) {
		
		$groupName = htmlentities($row->name);
		$groupName = preg_replace('/\\\/', '\\\\\\', $groupName);
		$groupName = preg_replace('/\'/', '\\\'', $groupName);
		
		$return .= "<option value=\"$row->id\">$groupName</option>";
		
	}
	
	return($return);
}

?>