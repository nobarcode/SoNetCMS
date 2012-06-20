<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$newCategory = sanitize_string($_REQUEST['newCategory']);
$newCategoryDefaultUrl = sanitize_string($_REQUEST['newCategoryDefaultUrl']);
$userSelectable = sanitize_string($_REQUEST['userSelectable']);
$weight = sanitize_string($_REQUEST['weight']);

if (trim($newCategory) == "") {$error = 1; $errorMessage .= "- Please supply a category.<br>";}
if (trim($newCategory) !="" && preg_match("/,/i", $newCategory)) {$error = 1; $errorMessage .="- Commas cannot be used in category names.<br>";}
if (trim($newCategory) !="" && preg_match('/\$_this/i', $newCategory)) {$error = 1; $errorMessage .= "- \$_this is a reserved name. Please use a different name.<br>";}

$matchRows = mysql_result(mysql_query("SELECT COUNT(1) AS NumRows FROM categories WHERE category = '{$newCategory}'"), 0, "NumRows");

if ($matchRows > 0) {$error = 1; $errorMessage .= "- The supplied category already exists.<br>";}

if ($error != 1) {
	
	$result = mysql_query("SELECT * FROM categories");
	$weight = mysql_num_rows($result) + 1;
	$result = mysql_query("INSERT INTO categories (category, userSelectable, hidden, defaultUrl, weight) VALUES ('{$newCategory}', '{$userSelectable}', '1', '#', '{$weight}')");
	
	if($result) {
		
		header('Content-type: application/javascript');
		print "$('#newCategory').val('');\n";
		print "regenerateList();\n";
		exit;
		
	} else {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<div>Unknown error! Please try your request again.</div>');";
		print "$('#message_box').show();";
		exit;
		
	}
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>