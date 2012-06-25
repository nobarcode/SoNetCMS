<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$multipleId = sanitize_string($_REQUEST['multipleId']);

if (!is_array($multipleId) || trim($_SESSION['username']) == "") {exit;}

foreach($multipleId as $id) {
	
	//update the publishState based on its current state
	mysql_query("UPDATE blogs SET publishState = IF(publishState = 'Unpublished', 'Published', IF(publishState = 'Published', 'Unpublished', publishState)) WHERE id = '{$id}' AND blogs.usernameCreated = '{$_SESSION['username']}'");
	
}

?>