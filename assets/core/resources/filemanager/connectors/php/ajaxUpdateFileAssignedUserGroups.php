<?php

include("../../../../config/part_set_timezone.php");
include("../../../../../../connectDatabase.inc");
include("../../../../../../part_session.php");
include("../../../../../../part_admin_check.php");
include("../../../../../../requestVariableSanitizer.inc");
include("../../../../../../class_file_user_group_validator.php");
include("../../../../../../class_config_reader.php");;
include("filemanager.config.php");

$assigned = sanitize_string($_REQUEST['assigned']);
$path = sanitize_string($_REQUEST['path']);
$fsPath = sanitize_string($config['sys_root'] . $_REQUEST['path']);

if (!is_array($assigned) || trim($path) == "") {$error = 1;}

if ($error != 1) {
	
	//check group security
	$userGroup = new FileUserGroupValidator();
	$userGroup->loadFileUserGroups($fsPath);
	if (!$userGroup->allowEditing()) {
		
		exit;
		
	}
	
	for ($x = 0; $x < count($assigned); $x++) {
		
		mysql_query("DELETE FROM fileManager WHERE groupId = '{$assigned[$x]}' AND fsPath = '{$fsPath}'");
		
	}
	
	//get assigned
	$result = mysql_query("SELECT userGroups.id, userGroups.name FROM fileManager INNER JOIN userGroups ON userGroups.id = fileManager.groupId WHERE fileManager.fsPath = '{$fsPath}' ORDER BY userGroups.name ASC");
	
	if (mysql_num_rows($result) > 0) {
		
		while($row = mysql_fetch_object($result)) {
			
			$groupName = htmlentities($row->name);
			$groupName = preg_replace('/\\\/', '\\\\\\', $groupName);
			$groupName = preg_replace('/\'/', '\\\'', $groupName);
			$assignedGroups .= "<option value=\"$row->id\">$groupName</option>";
			$assignedGroupsPreview .= $groupName . "<br>";
			
		}
		
	} else {
		
		$assignedGroupsPreview = "No Groups Assigned";
		
	}
	
	//get available
	$result = mysql_query("SELECT userGroups.id, userGroups.name FROM userGroups LEFT OUTER JOIN fileManager ON fileManager.groupId = userGroups.id AND fileManager.fsPath = '{$fsPath}' WHERE fileManager.groupId IS NULL ORDER BY name ASC");
	
	while($row = mysql_fetch_object($result)) {
		
		$groupName = htmlentities($row->name);
		$groupName = preg_replace('/\\\/', '\\\\\\', $groupName);
		$groupName = preg_replace('/\'/', '\\\'', $groupName);
		$availableGroups .= "<option value=\"$row->id\">$groupName</option>";
		
	}
	
	//update lists
	header('Content-type: application/javascript');
	print "$('#assigned').html('$assignedGroups');";
	print "$('#available').html('$availableGroups');";
	print "$('#file_group_assignment').html('$assignedGroupsPreview');";
	
}

?>