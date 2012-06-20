<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);
$newSubcategory = sanitize_string($_REQUEST['newSubcategory']);
$userSelectable = sanitize_string($_REQUEST['userSelectable']);
$weight = sanitize_string($_REQUEST['weight']);

if (trim($category) == "" || trim($newSubcategory) == "") {$error = 1; $errorMessage .= "- Please supply a subcategory.<br>";}
if (trim($newSubcategory) !="" && preg_match("/,/i", $newSubcategory)) {$error = 1; $errorMessage .="- Commas cannot be used in subcategory names.<br>";}
if (trim($newSubcategory) !="" && preg_match('/\$_this/i', $newSubcategory)) {$error = 1; $errorMessage .= "- \$_this is a reserved name. Please use a different name.<br>";}

$matchRows = mysql_result(mysql_query("SELECT COUNT(1) AS NumRows FROM subcategories WHERE category = '{$category}' AND subcategory = '{$newSubcategory}'"), 0, "NumRows");

if ($matchRows > 0) {$error = 1; $errorMessage .= "- The supplied subcategory already exists.<br>";}

if ($error != 1) {
	
	$result = mysql_query("SELECT * FROM subcategories WHERE category = '{$category}'");
	$weight = mysql_num_rows($result) + 1;
	$result = mysql_query("INSERT INTO subcategories (category, subcategory, userSelectable, weight) VALUES ('{$category}', '{$newSubcategory}', '{$userSelectable}', '{$weight}')");
	
	if($result) {
		
		header('Content-type: application/javascript');
		print "$('#newSubcategory').val('');\n";
		print "regenerateList();";
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