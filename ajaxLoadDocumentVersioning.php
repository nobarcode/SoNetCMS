<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$documentType = sanitize_string($_REQUEST['documentType']);
$version = sanitize_string($_REQUEST['version']);

if (trim($id) == "" || trim($documentType) == "") {$error = 1;}

$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i:%s %p') AS newDateCreated FROM documentVersioning WHERE parentId = '{$id}' AND  documentType = '{$documentType}' AND version = '{$version}'");
$totalRows = mysql_num_rows($result);

if ($totalRows == 0) {$error = 1;}

if ($error != 1) {
	
	$row = mysql_fetch_object($result);
	
	$escapeTitle = preg_replace('/\\\/', '\\\\\\', $row->title);
	$escapeTitle = preg_replace('/\'/', '\\\'', $escapeTitle);
	
	$escapeBody = preg_replace('/\\\/', '\\\\\\', $row->body);
	$escapeBody = preg_replace("/\\n/", "\\\\n", $escapeBody);
	$escapeBody = preg_replace("/\\r/", "\\\\r", $escapeBody);
	$escapeBody = preg_replace('/\'/', '\\\'', $escapeBody);
	
	//output javascript
	header('Content-type: application/javascript');
	
	//update the title
	print "$('#title').val('$escapeTitle');";
	
	//update the editor window with the escaped summary information
	print "CKEDITOR.instances.documentBody.setData('$escapeBody', function(){CKEDITOR.instances.documentBody.updateElement();CKEDITOR.instances.documentBody.resetDirty();});";
	
	//update version display
	print "$('#selected_version').html('$row->version > $row->newDateCreated > $row->usernameCreated');";
	print "currentVersion = $row->version;";
	
}

?>