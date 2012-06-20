<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

if (trim($id) == "") {$error = 1;}

$result = mysql_query("SELECT category FROM documents WHERE id = '{$id}' LIMIT 1");
$row = mysql_fetch_object($result);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowEditing()) {exit;}

$matchRows = mysql_result(mysql_query("SELECT COUNT(1) AS NumRows FROM focusedDocuments WHERE id = '{$id}'"), 0, "NumRows");

if ($matchRows > 0) {
	
	$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM focusedDocuments"), 0, "totalRows");
	$result = mysql_query("SELECT weight FROM focusedDocuments WHERE id = '{$id}'");
	
	if (mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_object($result);
		$weight = $row->weight;
		
		mysql_query("DELETE FROM focusedDocuments WHERE id = '{$id}'");
		
		for ($x = $weight + 1; $x <= $totalRows; $x++) {
			
			mysql_query("UPDATE focusedDocuments SET weight = (weight-1) WHERE weight = '{$x}'");
			
		}
		
		print "Set Focus";
		
	}
	
} else {
	
	$result = mysql_query("SELECT * FROM focusedDocuments");
	$weight = mysql_num_rows($result) + 1;
	$result = mysql_query("INSERT INTO focusedDocuments (id, activeState, weight) VALUES ('{$id}', 'Inactive', '{$weight}')");
	
	if($result) {
		
		print "Remove Focus";
		
	}
	
}

?>