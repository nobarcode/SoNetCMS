<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$deleteId = sanitize_string($_REQUEST['deleteId']);

if (!is_array($deleteId)) {exit;}

foreach($deleteId as $id) {
	
	mysql_query("DELETE FROM userGroups WHERE id = '{$id}'");
	mysql_query("DELETE FROM userGroupsMembers WHERE groupId = '{$id}'");
	mysql_query("DELETE FROM categoriesUserGroups WHERE groupId = '{$id}'");
	mysql_query("UPDATE fileManager SET groupId = '' WHERE groupId = '{$id}'");
	
}

?>