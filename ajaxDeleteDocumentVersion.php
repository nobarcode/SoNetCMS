<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$documentType = sanitize_string($_REQUEST['documentType']);
$version = sanitize_string($_REQUEST['version']);
$currentVersion = sanitize_string($_REQUEST['currentVersion']);

if (trim($id) == "" || trim($documentType) == "" || trim($version) == "" || trim($currentVersion) == "") {exit;}

//prevent deletion of current version
$result = mysql_query("SELECT version FROM documentVersioning WHERE parentId = '{$id}' AND documentType = '{$documentType}' ORDER BY version DESC LIMIT 1");
$row = mysql_fetch_object($result);
if ($row->version == $version || $currentVersion == $version) {exit;}

$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM documentVersioning WHERE parentId = '{$id}' AND documentType = '{$documentType}'"), 0, "totalRows");
$result = mysql_query("SELECT version FROM documentVersioning WHERE parentId = '{$id}' AND documentType = '{$documentType}' ORDER BY version ASC");

if (mysql_num_rows($result) > 0) {
	
	$row = mysql_fetch_object($result);
	$versionCycle = $row->version;
	
	mysql_query("DELETE FROM documentVersioning WHERE parentId = '{$id}' AND documentType = '{$documentType}' AND version = '{$version}'");
	
	for ($x = $versionCycle + 1; $x <= $totalRows; $x++) {
		
		mysql_query("UPDATE documentVersioning SET version = (version-1) WHERE parentId = '{$id}' AND documentType = '{$documentType}' AND version = '{$x}'");
		
	}
	
	//get the new current version number
	if ($currentVersion > 1 && $currentVersion > $version) {
		
		$currentVersion -=1;
		
	}
	
	$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i:%s %p') AS newDateCreated FROM documentVersioning WHERE parentId = '{$id}' AND  documentType = '{$documentType}' AND version = '{$currentVersion}'");
	$row = mysql_fetch_object($result);
	
	header('Content-type: application/javascript');
	print "regenerateVersionList('$id', '', '');";
	print "$('#selected_version').html('$row->version > $row->newDateCreated > $row->usernameCreated');";
	print "currentVersion = $currentVersion;";
	
}

?>