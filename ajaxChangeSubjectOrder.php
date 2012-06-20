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
$subject = sanitize_string($_REQUEST['subject']);

if (trim($category) == "" || trim($subcategory) == "" || trim($subject[0]) == "") {$error = 1;}

parse_str($subject);

if ($error != 1) {
	
	for ($x = 0; $x < count($subject); $x++) {
		
		$y = $x + 1;
		
		mysql_query("UPDATE subjects SET weight = '{$y}' WHERE id = '{$subject[$x]}' AND category = '{$category}' AND subcategory = '{$subcategory}'");
		
	}
	
}

?>