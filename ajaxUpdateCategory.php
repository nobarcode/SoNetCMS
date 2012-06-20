<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);
$value = sanitize_string($_REQUEST['value']);

if (trim($category) == "" || trim($value) == "") {$error = 1; $errorMessage .= "- Please supply a category.<br>";}
if (trim($value) !="" && preg_match("/,/i", $value)) {$error = 1; $errorMessage .="- Commas cannot be used in category names.<br>";}
if (trim($value) !="" && preg_match('/\$_this/i', $value)) {$error = 1; $errorMessage .= "- \$_this is a reserved name. Please use a different name.<br>";}

$matchRows = mysql_result(mysql_query("SELECT COUNT(1) AS NumRows FROM categories WHERE category = '{$value}'"), 0, "NumRows");

//if the category already exists and the new $value is not the same as the one being edited, generate error (allows changing the case of an existing category)
if ($matchRows > 0 && strtolower($category) != strtolower($value)) {$error = 1; $errorMessage .= "- The supplied category already exists.<br>";}

if ($error != 1) {
	
	mysql_query("UPDATE categories SET category = '{$value}' WHERE category = '{$category}'");
	mysql_query("UPDATE categoriesUserGroups SET category = '{$value}' WHERE category = '{$category}'");
	mysql_query("UPDATE subcategories SET category = '{$value}' WHERE category = '{$category}'");
	mysql_query("UPDATE subjects SET category = '{$value}' WHERE category = '{$category}'");
	mysql_query("UPDATE documents SET category = '{$value}' WHERE category = '{$category}'");
	mysql_query("UPDATE blogs SET category = '{$value}' WHERE category = '{$category}'");
	mysql_query("UPDATE events SET category = '{$value}' WHERE category = '{$category}'");
	
	//update rc_components in documents, blogs, and events
	$result = mysql_query("SELECT id, body FROM documents");
	
	while ($row = mysql_fetch_object($result)) {

		$updatedDocument = preg_replace("/\[\[rc_component type=\"document\"(.*?)category=\"" . unsanitize_string(preg_quote($category, '/')) . "\"(.*?)\]\]/is", "[[rc_component type=\"document\"$1category=\"" . unsanitize_string($value) . "\"$2]]", $row->body);
		$updatedDocument = preg_replace("/\[\[rc_component type=\"blog\"(.*?)category=\"" . unsanitize_string(preg_quote($category, '/')) . "\"(.*?)\]\]/is", "[[rc_component type=\"document\"$1category=\"" . unsanitize_string($value) . "\"$2]]", $updatedDocument);
		$updatedDocument = preg_replace("/\[\[rc_component type=\"event\"(.*?)category=\"" . unsanitize_string(preg_quote($category, '/')) . "\"(.*?)\]\]/is", "[[rc_component type=\"document\"$1category=\"" . unsanitize_string($value) . "\"$2]]", $updatedDocument);
		$updatedDocument = sanitize_string($updatedDocument);
		
		mysql_query("UPDATE documents SET body = '{$updatedDocument}' WHERE id = '{$row->id}'");
		
	}
	
	header('Content-type: application/javascript');
	print "cancelEditCategory();";
	print "regenerateList();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>