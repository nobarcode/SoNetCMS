<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$multipleId = sanitize_string($_REQUEST['multipleId']);

if (!is_array($multipleId)) {exit;}

foreach($multipleId as $id) {
	
	mysql_query("DELETE FROM announcements WHERE id = '{$id}'");
	
}

?>