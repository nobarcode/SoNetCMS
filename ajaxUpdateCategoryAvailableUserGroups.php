<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$available = sanitize_string($_REQUEST['available']);
$category = sanitize_string($_REQUEST['category']);

if (!is_array($available) || trim($category) == "") {$error = 1;}

if ($error != 1) {
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup = new CategoryUserGroupValidator();
	$userGroup->loadCategoryUserGroups($category);
	if (!$userGroup->allowEditing()) {exit;}
	
	for ($x = 0; $x < count($available); $x++) {
		
		mysql_query("INSERT INTO categoriesUserGroups (groupId, category) VALUES ('{$available[$x]}', '{$category}')");
		
	}
	
	$assignedGroups = loadGroups($category, 'assigned');
	$availableGroups = loadGroups($category, 'available');
	
	header('Content-type: application/javascript');
	print "$('#assigned').html('$assignedGroups');";
	print "$('#available').html('$availableGroups');";
	
}

function loadGroups($category, $type) {
	
	if ($type == 'assigned') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM categoriesUserGroups INNER JOIN userGroups ON userGroups.id = categoriesUserGroups.groupId WHERE categoriesUserGroups.category = '{$category}' ORDER BY userGroups.name ASC";
		
	} elseif ($type == 'available') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM userGroups LEFT OUTER JOIN categoriesUserGroups ON categoriesUserGroups.groupId = userGroups.id AND categoriesUserGroups.category =  '{$category}' WHERE categoriesUserGroups.groupId IS NULL ORDER BY name ASC";
		
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