<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$document_type = sanitize_string($_REQUEST['document_type']);

if (trim($document_type[0]) == "") {$error = 1;}

if ($error != 1) {
	
	for ($x = 0; $x < count($document_type); $x++) {
		
		$y = $x + 1;
		
		mysql_query("UPDATE documentTypes SET weight = '{$y}' WHERE id = '$document_type[$x]'");
		
	}
	
}

?>