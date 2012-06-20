<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$documentType = sanitize_string($_REQUEST['documentType']);
$userSelectable = sanitize_string($_REQUEST['userSelectable']);

if (trim($documentType) == "") {exit;}

if ($userSelectable != 1) {
	
	$userSelectable = 0;
	
}

if (trim($focus) != "") {
	
	$result = mysql_query("UPDATE documentTypes SET focus = '0' WHERE focus = '1'");

	if(!$result) {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- Internal error. Please retry your request.</div>');";
		print "$('#message_box').show();";
		exit;

	}
	
}

$result = mysql_query("UPDATE documentTypes SET userSelectable = '{$userSelectable}' WHERE documentType = '{$documentType}'");

if(!$result) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Please retry your request.</div>');";
	print "$('#message_box').show();";
	exit;

}

header('Content-type: application/javascript');
print "$('#message_box').html('<div>Document Type parameters updated successfully.</div>');";
print "$('#message_box').show();";
print "cancelEditDocumentTypeOptions();";

?>