<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$userSelectable = sanitize_string($_REQUEST['userSelectable']);
$hidden = sanitize_string($_REQUEST['hidden']);
$useAlternateClass = sanitize_string($_REQUEST['useAlternateClass']);
$defaultUrl = sanitize_string($_REQUEST['defaultUrl']);
$flyoutContent = sanitize_string($_REQUEST['flyoutContent']);
$title = sanitize_string($_REQUEST['title']);
$description = sanitize_string($_REQUEST['description']);
$keywords = sanitize_string($_REQUEST['keywords']);

if (trim($id) == "") {exit;}

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowEditing()) {exit;}

$result = mysql_query("SELECT category FROM categories WHERE id = '{$id}' LIMIT 1");
$row = mysql_fetch_object($result);

if ($error != 1) {
	
	$result = mysql_query("UPDATE categories SET userSelectable = '{$userSelectable}', hidden = '{$hidden}', useAlternateClass = '{$useAlternateClass}', defaultUrl = '{$defaultUrl}', flyoutContent = '{$flyoutContent}' WHERE id = '{$id}'");
	
	if(!$result) {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Please retry your request.</div>');";
		print "$('#message_box').show();";
		exit;
	
	}
	
	//reset the category variable to prevent the tab selection during menu regeneration
	$category = "";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div>Category parameters updated successfully.</div>');";
	print "$('#message_box').show();";
	print "cancelEditCategoryOptions();";
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>