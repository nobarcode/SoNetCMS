<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

$result = mysql_query("SELECT id FROM messages WHERE toUser = '{$_SESSION['username']}' AND status = 'unread'");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	if ($count < 2) {
		
		$messageText = "message";
		
	} else {
		
		$messageText = "messages";
		
	}
	
	print "<span style=\"font-weight:bold;\">$count unread $messageText</span>";
	
} else {
	
	print "<span>" . $count . " unread messages</span>";
	
}

?>