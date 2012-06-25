<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$deleteId = sanitize_string($_REQUEST['deleteId']);

if (!is_array($deleteId) || trim($_SESSION['username']) == "") {exit;}

foreach($deleteId as $id) {
	
	mysql_query("DELETE FROM messages WHERE id = '{$id}' AND toUser = '{$_SESSION['username']}'");
	
}

?>