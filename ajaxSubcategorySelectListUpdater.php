<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);

$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);

if ($_SESSION['username'] != "" && trim($category) != "" && $userGroup->allowRead()) {
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
		
		$result = mysql_query("SELECT * FROM subcategories WHERE category = '{$category}' ORDER BY weight");
		
	} else {
		
		$result = mysql_query("SELECT * FROM subcategories WHERE category = '{$category}' AND userSelectable = '1' ORDER BY weight");
		
	}

	while ($row = mysql_fetch_object($result)) {

		$subcategory = htmlentities($row->subcategory);

		print "									<option value=\"$subcategory\">$subcategory</option>\n";

	}
	
}

?>