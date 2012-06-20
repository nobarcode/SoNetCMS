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
$value = sanitize_string($_REQUEST['value']);

if (trim($category) == "" || trim($subcategory) == "" || trim($value) == "") {$error = 1; $errorMessage .= "- Please supply a subcategory.<br>";}
if (trim($value) !="" && preg_match("/,/i", $value)) {$error = 1; $errorMessage .="- Commas cannot be used in subcategory names.<br>";}
if (trim($value) !="" && preg_match('/\$_this/i', $value)) {$error = 1; $errorMessage .= "- \$_this is a reserved name. Please use a different name.<br>";}

$matchRows = mysql_result(mysql_query("SELECT COUNT(1) AS NumRows FROM subcategories WHERE category = '{$category}' AND subcategory = '{$value}'"), 0, "NumRows");

//if the subcategory already exists and the new $value is not the same as the one being edited, generate error (allows changing the case of an existing subcategory)
if ($matchRows > 0 && strtolower($subcategory) != strtolower($value)) {$error = 1; $errorMessage .= "- The supplied subcategory already exists.<br>";}

if ($error != 1) {
	
	mysql_query("UPDATE subcategories SET subcategory = '{$value}' WHERE category = '{$category}' AND subcategory = '{$subcategory}'");
	mysql_query("UPDATE subjects SET subcategory = '{$value}' WHERE category = '{$category}' AND subcategory = '{$subcategory}'");
	mysql_query("UPDATE documents SET subcategory = '{$value}' WHERE category = '{$category}' AND subcategory = '{$subcategory}'");
	mysql_query("UPDATE blogs SET subcategory = '{$value}' WHERE category = '{$category}' AND subcategory = '{$subcategory}'");
	
	//update rc_components in documents and blogs
	$result = mysql_query("SELECT id, body FROM documents");
	
	while ($row = mysql_fetch_object($result)) {

		$updatedDocument = preg_replace("/\[\[rc_component type=\"document\"(.*?)subcategory=\"" . unsanitize_string(preg_quote($subcategory, '/')) . "\"(.*?)\]\]/is", "[[rc_component type=\"document\"$1subcategory=\"" . unsanitize_string($value) . "\"$2]]", $row->body);
		$updatedDocument = preg_replace("/\[\[rc_component type=\"blog\"(.*?)subcategory=\"" . unsanitize_string(preg_quote($subcategory, '/')) . "\"(.*?)\]\]/is", "[[rc_component type=\"document\"$1subcategory=\"" . unsanitize_string($value) . "\"$2]]", $updatedDocument);
		$updatedDocument = sanitize_string($updatedDocument);
		
		mysql_query("UPDATE documents SET body = '{$updatedDocument}' WHERE id = '{$row->id}'");
		
	}
	
	header('Content-type: application/javascript');
	print "cancelEditSubcategory();";
	print "regenerateList();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>