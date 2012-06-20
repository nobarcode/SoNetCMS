<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);

if (trim($category) == "" || trim($subcategory[0]) == "") {$error = 1;}

parse_str($subcategory);

if ($error != 1) {
	
	for ($x = 0; $x < count($subcategory); $x++) {
		
		$y = $x + 1;
		
		mysql_query("UPDATE subcategories SET weight = '{$y}' WHERE id = '{$subcategory[$x]}' AND category = '{$category}'");
		
	}
	
}

?>