<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$toggleStatusId = sanitize_string($_REQUEST['multipleId']);

if (!is_array($toggleStatusId) || trim($_SESSION['username']) == "") {exit;}

//get the current date and time
$time = date("Y-m-d H:i:s", time());

for ($x = 0; $x < count($toggleStatusId); $x++) {
	
	//update the publishState based on its current state
	mysql_query("UPDATE documents SET datePublished = '{$time}', publishState = IF(publishState = 'Unpublished', 'Published', IF(publishState = 'Published', 'Unpublished', publishState)) WHERE id = '{$toggleStatusId[$x]}'");
	
}

?>