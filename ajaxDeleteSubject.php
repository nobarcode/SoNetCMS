<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);

if (trim($category) == "" || trim($subcategory) == "" || trim($subject) == "") {$error = 1;}

if ($error != 1) {
	
	$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}'"), 0, "totalRows");
	$result = mysql_query("SELECT weight FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}' AND subject = '{$subject}' ORDER BY weight ASC");
	
	if (mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_object($result);
		$weight = $row->weight;
		
		mysql_query("DELETE FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}' AND subject = '{$subject}'");
		
		for ($x = $weight + 1; $x <= $totalRows; $x++) {
			
			mysql_query("UPDATE subjects SET weight = (weight-1) WHERE category = '{$category}' AND subcategory = '{$subcategory}' AND weight = '{$x}'");
			
		}
		
	}
	
}
?>