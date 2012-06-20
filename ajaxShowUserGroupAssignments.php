<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$username = sanitize_string($_REQUEST['username']);
$type = sanitize_string($_REQUEST['type']);

$assignedGroups = loadGroups($username, 'assigned');
$availableGroups = loadGroups($username, 'available');

print <<< EOF
<div class="editor_user_container">
	<div class="assigned_groups">
		<div class="groups_assignement_header"><b>Groups Assigned To:</b> $username</div>
		<form id="update_assigned">
		<select multiple="multiple" id="assigned" name="assigned[]" size="20" style="width:445px;">
		$assignedGroups
		</select>
		<div class="remove_group"><input type="button" id="to_available" value="&gt;&gt;&gt;"></div>
		<input type="hidden" id="username" name="username" value="$username">
		</form>
	</div>
	<div class="available_groups">
		<div class="groups_assignement_header"><b>Available Groups</b></div>
		<form id="update_available">
		<select multiple="multiple" id="available" name="available[]" size="20" style="width:445px;">
		$availableGroups
		</select>
		<div class="add_group"><input type="button" id="to_assigned" value="&lt;&lt;&lt;"></div>
		<input type="hidden" id="username" name="username" value="$username">
		</form>
	</div>
	<div class="cancel_button"><input type="button" id="editor_cancel" value="Done"></div>
</div>
EOF;

function loadGroups($username, $type) {
	
	if ($type == 'assigned') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM userGroupsMembers INNER JOIN userGroups ON userGroups.id = userGroupsMembers.groupId WHERE userGroupsMembers.username = '{$username}' ORDER BY userGroups.name ASC";
		
	} elseif ($type == 'available') {
		
		$loadType = "SELECT userGroups.id, userGroups.name FROM userGroups LEFT OUTER JOIN userGroupsMembers ON userGroupsMembers.groupId = userGroups.id AND userGroupsMembers.username =  '{$username}' WHERE userGroupsMembers.groupId IS NULL ORDER BY name ASC";
		
	}
	
	$result = mysql_query($loadType);
	
	while($row = mysql_fetch_object($result)) {
		
		$groupName = htmlentities($row->name);
		$return .= "<option value=\"$row->id\">$groupName</option>";
		
	}
	
	return($return);
}
	
?>