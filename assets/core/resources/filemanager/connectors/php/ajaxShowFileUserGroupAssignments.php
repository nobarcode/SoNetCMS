<?php

include("../../../../config/part_set_timezone.php");
include("../../../../../../connectDatabase.inc");
include("../../../../../../part_session.php");
include("../../../../../../part_admin_check.php");
include("../../../../../../requestVariableSanitizer.inc");
include("../../../../../../class_category_user_group_validator.php");
include("../../../../../../class_config_reader.php");;
include("filemanager.config.php");

$path = sanitize_string($_REQUEST['path']);
$fsPath = sanitize_string($config['sys_root'] . $_REQUEST['path']);
$type = sanitize_string($_REQUEST['type']);
$htmlPath = htmlentities(unsanitize_string($path));

//$result = mysql_query("SELECT category FROM categories WHERE id = '{$category}' LIMIT 1");
//$row = mysql_fetch_object($result);
//$category = sanitize_string($row->category);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
//$userGroup = new CategoryUserGroupValidator();
//$userGroup->loadCategoryUserGroups($category);
//if (!$userGroup->allowEditing()) {exit;}

$assignedGroups = loadGroups($fsPath, 'assigned');
$availableGroups = loadGroups($fsPath, 'available');

print <<< EOF
<div class="editor_category_container">
	<div class="assigned_groups">
		<div class="groups_assignement_header">Assigned Groups</div>
		<form id="update_assigned">
		<select multiple="multiple" id="assigned" name="assigned[]" size="10" style="width:220px;">
		$assignedGroups
		</select>
		<div class="remove_group"><input type="button" id="to_available" value="&gt;&gt;&gt;"></div>
		<input type="hidden" id="path" name="path" value="$htmlPath">
		</form>
	</div>
	<div class="available_groups">
		<div class="groups_assignement_header">Available Groups</div>
		<form id="update_available">
		<select multiple="multiple" id="available" name="available[]" size="10" style="width:220px;">
		$availableGroups
		</select>
		<div class="add_group"><input type="button" id="to_assigned" value="&lt;&lt;&lt;"></div>
		<input type="hidden" id="path" name="path" value="$htmlPath">
		</form>
	</div>
	<div class="cancel_button"><input type="button" id="editor_cancel" value="Done"></div>
</div>
EOF;

function loadGroups($fsPath, $type) {
	
	if ($type == 'assigned') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM fileManager INNER JOIN userGroups ON userGroups.id = fileManager.groupId WHERE fileManager.fsPath = '{$fsPath}' ORDER BY userGroups.name ASC";
		
	} elseif ($type == 'available') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM userGroups LEFT OUTER JOIN fileManager ON fileManager.groupId = userGroups.id AND fileManager.fsPath = '{$fsPath}' WHERE fileManager.groupId IS NULL ORDER BY name ASC";
		
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