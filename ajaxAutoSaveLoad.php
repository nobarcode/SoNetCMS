<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

if (trim($_SESSION['username']) == "") {exit;}

$result = mysql_query("SELECT content FROM autoSaveContent WHERE username = '{$_SESSION['username']}'");
$row = mysql_fetch_object($result);

if (trim($row->content) != "") {
	
	$escapeBody = preg_replace('/\\\/', '\\\\\\', $row->content);
	$escapeBody = preg_replace("/\\n/", "\\\\n", $escapeBody);
	$escapeBody = preg_replace("/\\r/", "\\\\r", $escapeBody);
	$escapeBody = preg_replace('/\'/', '\\\'', $escapeBody);
	
	header('Content-type: application/javascript');
	print "CKEDITOR.currentInstance.setData('$escapeBody');";
	
}

?>