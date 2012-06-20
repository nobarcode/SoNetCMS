<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);

if ($_SESSION['username'] != "") {
	
	print "									<option value=\"\">All</option>\n";
	
	if (trim($category) != "") {
		
		$userGroup = new CategoryUserGroupValidator();
		$userGroup->loadCategoryUserGroups($category);
		
		if (trim($subcategory) != "" && $userGroup->allowRead()) {
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 ||$_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
				
				$result = mysql_query("SELECT * FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}' ORDER BY weight");
				
			} else {
				
				$result = mysql_query("SELECT * FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}' AND userSelectable = '1' ORDER BY weight");
				
			}
			
			while ($row = mysql_fetch_object($result)) {
		
				$subject = htmlentities($row->subject);
		
				print "									<option value=\"$subject\">$subject</option>\n";
		
			}
			
		}
		
	}
	
}

?>