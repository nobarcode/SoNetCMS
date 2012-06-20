<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$deleteId = sanitize_string($_REQUEST['deleteId']);

if (!is_array($deleteId) || trim($_SESSION['username']) == "") {$error = 1;}

if ($error != 1) {
	
	for ($x = 0; $x < count($deleteId); $x++) {
		
		mysql_query("DELETE FROM messages WHERE id = '{$deleteId[$x]}' AND toUser = '{$_SESSION['username']}'");
		
	}
	
}

?>