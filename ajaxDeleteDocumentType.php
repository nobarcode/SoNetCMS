<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$documentType = sanitize_string($_REQUEST['documentType']);

if (trim($documentType) == "") {$error = 1;}

if ($error != 1) {
	
	$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM documentTypes"), 0, "totalRows");
	$result = mysql_query("SELECT weight FROM documentTypes WHERE documentType = '{$documentType}'");
	
	if (mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_object($result);
		$weight = $row->weight;
		
		mysql_query("DELETE FROM documentTypes WHERE documentType = '{$documentType}'");
		
		for ($x = $weight + 1; $x <= $totalRows; $x++) {
			
			mysql_query("UPDATE documentTypes SET weight = (weight-1) WHERE weight = '{$x}'");
			
		}
		
	}
	
}

?>