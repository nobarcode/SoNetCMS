<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$focused_document = sanitize_string($_REQUEST['focused_document']);

if (trim($focused_document[0]) == "") {$error = 1;}

parse_str($focused_document);

if ($error != 1) {
	
	for ($x = 0; $x < count($focused_document); $x++) {
		
		$y = $x + 1;
		
		mysql_query("UPDATE focusedDocuments SET weight = '{$y}' WHERE id = '{$focused_document[$x]}'");
		
	}
	
}

?>