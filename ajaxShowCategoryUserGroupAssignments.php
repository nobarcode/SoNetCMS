<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);
$type = sanitize_string($_REQUEST['type']);

$result = mysql_query("SELECT category FROM categories WHERE id = '{$category}' LIMIT 1");
$row = mysql_fetch_object($result);
$category = sanitize_string($row->category);
$showCategory = htmlentities($row->category);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);
if (!$userGroup->allowEditing()) {exit;}

$assignedGroups = loadGroups($category, 'assigned');
$availableGroups = loadGroups($category, 'available');

print <<< EOF
<div class="editor_category_container">
	<div class="assigned_groups">
		<div class="groups_assignement_header"><b>Groups Assigned To:</b> $showCategory</div>
		<form id="update_assigned">
		<select multiple="multiple" id="assigned" name="assigned[]" size="20" style="width:440px;">
		$assignedGroups
		</select>
		<div class="remove_group"><input type="button" id="to_available" value="&gt;&gt;&gt;"></div>
		<input type="hidden" id="category" name="category" value="$showCategory">
		</form>
	</div>
	<div class="available_groups">
		<div class="groups_assignement_header"><b>Available Groups</b></div>
		<form id="update_available">
		<select multiple="multiple" id="available" name="available[]" size="20" style="width:440px;">
		$availableGroups
		</select>
		<div class="add_group"><input type="button" id="to_assigned" value="&lt;&lt;&lt;"></div>
		<input type="hidden" id="category" name="category" value="$showCategory">
		</form>
	</div>
	<div class="cancel_button"><input type="button" id="editor_cancel" value="Done"></div>
</div>
EOF;

function loadGroups($category, $type) {
	
	if ($type == 'assigned') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM categoriesUserGroups INNER JOIN userGroups ON userGroups.id = categoriesUserGroups.groupId WHERE categoriesUserGroups.category = '{$category}' ORDER BY userGroups.name ASC";
		
	} elseif ($type == 'available') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM userGroups LEFT OUTER JOIN categoriesUserGroups ON categoriesUserGroups.groupId = userGroups.id AND categoriesUserGroups.category = '{$category}' WHERE categoriesUserGroups.groupId IS NULL ORDER BY name ASC";
		
	}
	
	$result = mysql_query($loadType);
	
	while($row = mysql_fetch_object($result)) {
		
		$groupName = htmlentities($row->name);
		$return .= "<option value=\"$row->id\">$groupName</option>";
		
	}
	
	return($return);
}
	
?>