<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$toggleStatusId = sanitize_string($_REQUEST['multipleId']);

if (!is_array($toggleStatusId) || trim($_SESSION['username']) == "") {exit;}

for ($x = 0; $x < count($toggleStatusId); $x++) {
	
	//update the publishState based on its current state
	mysql_query("UPDATE blogs SET publishState = IF(publishState = 'Unpublished', 'Published', IF(publishState = 'Published', 'Unpublished', publishState)) WHERE id = '{$toggleStatusId[$x]}' AND blogs.usernameCreated = '{$_SESSION['username']}'");
	
}

?>