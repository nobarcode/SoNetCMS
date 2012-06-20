<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_config_reader.php");

$documentType = sanitize_string($_REQUEST['documentType']);
$id = sanitize_string($_REQUEST['id']);

if (trim($documentType) == "" || trim($id) == "") {exit;}

//clear document editing tracker
mysql_query("DELETE FROM documentEditTracking WHERE documentType = '{$documentType}' AND id = '{$id}' AND username = '{$_SESSION['username']}'");

?>