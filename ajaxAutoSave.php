<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$content = sanitize_string($_REQUEST['content']);

if (trim($_SESSION['username']) == "") {exit;}

$result = mysql_query("SELECT username FROM autoSaveContent WHERE username = '{$_SESSION['username']}'");

if (mysql_num_rows($result) > 0) {
	
	mysql_query("UPDATE autoSaveContent SET content = '{$content}' WHERE username = '{$_SESSION['username']}'");
	
} else {
	
	mysql_query("INSERT INTO autoSaveContent (username, content) VALUES ('{$_SESSION['username']}', '{$content}')");
	
}


?>