<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$subject = sanitize_string($_REQUEST['subject']);
$userSelectable = sanitize_string($_REQUEST['userSelectable']);

if (trim($subject) == "") {exit;}

$result = mysql_query("SELECT category FROM subjects WHERE id = '{$subject}' LIMIT 1");
$row = mysql_fetch_object($result);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowEditing()) {exit;}

if ($userSelectable != 1) {
	
	$userSelectable = 0;
	
}

$result = mysql_query("UPDATE subjects SET userSelectable = '{$userSelectable}' WHERE id = '{$subject}'");

if(!$result) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Please retry your request.</div>');";
	print "$('#message_box').show();";
	exit;

}

header('Content-type: application/javascript');
print "$('#message_box').html('<div>Subject parameters updated successfully.</div>');";
print "$('#message_box').show();";
print "cancelEditSubjectOptions();";

?>