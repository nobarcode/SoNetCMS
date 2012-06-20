<?php

include("../../../../config/part_set_timezone.php");
include("../../../../../../connectDatabase.inc");
include("../../../../../../part_session.php");
include("../../../../../../part_admin_check.php");
include("../../../../../../requestVariableSanitizer.inc");
include("../../../../../../class_file_user_group_validator.php");
include("../../../../../../class_config_reader.php");;
include("filemanager.config.php");

$available = sanitize_string($_REQUEST['available']);
$path = sanitize_string($_REQUEST['path']);
$fsPath = sanitize_string($config['sys_root'] . $_REQUEST['path']);

if (!is_array($available) || trim($path) == "") {$error = 1;}

if ($error != 1) {
	
	//check group security
	$userGroup = new FileUserGroupValidator();
	$userGroup->loadFileUserGroups($fsPath);
	if (!$userGroup->allowEditing()) {
		
		exit;
		
	}
	
	//get a copy of the file's properties
	$result = mysql_query("SELECT wwwPath, fsPath, owner, security FROM fileManager WHERE fsPath = '{$fsPath}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	for ($x = 0; $x < count($available); $x++) {
		
		mysql_query("INSERT INTO fileManager (wwwPath, fsPath, owner, security, groupId) VALUES ('{$row->wwwPath}', '{$row->fsPath}', '{$row->owner}', '{$row->security}', '{$available[$x]}')");
		
	}
	
	//get assigned
	$result = mysql_query("SELECT userGroups.id, userGroups.name FROM fileManager INNER JOIN userGroups ON userGroups.id = fileManager.groupId WHERE fileManager.fsPath = '{$fsPath}' ORDER BY userGroups.name ASC");
	
	while($row = mysql_fetch_object($result)) {
		
		$groupName = htmlentities($row->name);
		$groupName = preg_replace('/\\\/', '\\\\\\', $groupName);
		$groupName = preg_replace('/\'/', '\\\'', $groupName);
		$assignedGroups .= "<option value=\"$row->id\">$groupName</option>";
		$assignedGroupsPreview .= $groupName . "<br>";
		
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