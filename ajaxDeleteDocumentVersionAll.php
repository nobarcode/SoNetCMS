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

if (trim($id) == "" || trim($documentType) == "") {exit;}

//delete all but the last version
mysql_query("DELETE FROM documentVersioning WHERE parentId = '{$id}' AND documentType = '{$documentType}' AND version NOT IN (SELECT version FROM (SELECT version FROM documentVersioning WHERE parentId = '{$id}' AND documentType = '{$documentType}' ORDER BY version DESC LIMIT 1) saveList)");

//set the last version to version 1
mysql_query("UPDATE documentVersioning SET version = '1' WHERE parentId = '{$id}' AND documentType = '{$documentType}'");

//grab the "new" latest version's info
$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i:%s %p') AS newDateCreated FROM documentVersioning WHERE parentId = '{$id}' AND  documentType = '{$documentType}' AND version = '1'");
$row = mysql_fetch_object($result);

header('Content-type: application/javascript');
print "regenerateVersionList('$id', '', '');";
print "$('#selected_version').html('$row->version > $row->newDateCreated > $row->usernameCreated');";
print "currentVersion = '1';";
	
?>