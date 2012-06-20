<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$deleteId = sanitize_string($_REQUEST['deleteId']);

if (!is_array($deleteId)) {$error = 1;}

if ($error != 1) {
	
	for ($x = 0; $x < count($deleteId); $x++) {
		
		mysql_query("DELETE FROM userGroups WHERE id = '{$deleteId[$x]}'");
		mysql_query("DELETE FROM userGroupsMembers WHERE groupId = '{$deleteId[$x]}'");
		mysql_query("DELETE FROM categoriesUserGroups WHERE groupId = '{$deleteId[$x]}'");
		mysql_query("UPDATE fileManager SET groupId = '' WHERE groupId = '{$deleteId[$x]}'");
		
	}
	
}

?>