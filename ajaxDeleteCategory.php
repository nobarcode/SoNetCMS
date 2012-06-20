<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);

if (trim($category) == "") {$error = 1;}

if ($error != 1) {
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup = new CategoryUserGroupValidator();
	$userGroup->loadCategoryUserGroups($category);
	if (!$userGroup->allowEditing()) {exit;}
	
	$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM categories"), 0, "totalRows");
	$result = mysql_query("SELECT weight FROM categories WHERE category = '{$category}' ORDER BY weight ASC");
	
	if (mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_object($result);
		$weight = $row->weight;
		
		mysql_query("DELETE FROM categories WHERE category = '{$category}'");
		
		for ($x = $weight + 1; $x <= $totalRows; $x++) {
			
			mysql_query("UPDATE categories SET weight = (weight-1) WHERE weight = '{$x}'");
			
		}
		
	}
	
}

?>