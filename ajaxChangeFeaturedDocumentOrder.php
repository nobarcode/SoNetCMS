<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$featured_document = sanitize_string($_REQUEST['featured_document']);

if (trim($featured_document[0]) == "") {$error = 1;}

parse_str($featured_document);

if ($error != 1) {
	
	for ($x = 0; $x < count($featured_document); $x++) {
		
		$y = $x + 1;
		
		mysql_query("UPDATE featuredDocuments SET weight = '{$y}' WHERE id = '{$featured_document[$x]}'");
		
	}
	
}

?>