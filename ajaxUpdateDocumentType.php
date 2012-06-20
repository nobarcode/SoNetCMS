<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$documentType = sanitize_string($_REQUEST['documentType']);
$value = sanitize_string($_REQUEST['value']);

if (trim($documentType) == "" || trim($value) == "") {$error = 1; $errorMessage .= "- Please supply a document type.<br>";}
if (trim($value) !="" && preg_match("/,/i", $value)) {$error = 1; $errorMessage .="- Commas cannot be used in document type names.<br>";}
if (trim($value) !="" && preg_match('/\$_this/i', $value)) {$error = 1; $errorMessage .= "- \$_this is a reserved name. Please use a different name.<br>";}

$matchRows = mysql_result(mysql_query("SELECT COUNT(1) AS NumRows FROM documentTypes WHERE documentType = '{$value}'"), 0, "NumRows");

//if the document type already exists and the new $value is not the same as the one being edited, generate error (allows changing the case of an existing document type)
if ($matchRows > 0 && strtolower($documentType) != strtolower($value)) {$error = 1; $errorMessage .= "- The supplied document type already exists.<br>";}

if ($error != 1) {
	
	mysql_query("UPDATE documentTypes SET documentType = '{$value}' WHERE documentType = '{$documentType}'");
	mysql_query("UPDATE blogs SET documentType = '{$value}' WHERE documentType = '{$documentType}'");
	mysql_query("UPDATE documents SET documentType = '{$value}' WHERE documentType = '{$documentType}'");
	
	//update rc_components in documents and blogs
	$result = mysql_query("SELECT id, body FROM documents");
	
	while ($row = mysql_fetch_object($result)) {

		$updatedDocument = preg_replace("/\[\[rc_component type=\"document\"(.*?)documentType=\"" . unsanitize_string($documentType) . "\"(.*?)\]\]/is", "[[rc_component type=\"document\"$1documentType=\"" . unsanitize_string($value) . "\"$2]]", $row->body);
		$updatedDocument = preg_replace("/\[\[rc_component type=\"blog\"(.*?)documentType=\"" . unsanitize_string($documentType) . "\"(.*?)\]\]/is", "[[rc_component type=\"document\"$1documentType=\"" . unsanitize_string($value) . "\"$2]]", $updatedDocument);
		$updatedDocument = sanitize_string($updatedDocument);
		
		mysql_query("UPDATE documents SET body = '{$updatedDocument}' WHERE id = '{$row->id}'");
		
	}
	
	header('Content-type: application/javascript');
	print "cancelEditDocumentType();";
	print "regenerateList();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>