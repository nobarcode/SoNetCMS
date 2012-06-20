<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

$result = mysql_query("SELECT * FROM messages WHERE id = '{$id}' AND toUser = '{$_SESSION['username']}' LIMIT 1");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	$row = mysql_fetch_object($result);
	$subject = htmlentities($row->subject);
	$dateSent = date("F j, Y g:i A", $row->dateSent);
	
	if ($row->system != 1) {
		
		$body = htmlentities($row->body);
		$body = preg_replace("/\\n/", "<br>", $body);
		
	} else {
		
		$body = $row->body;
		
	}
	
	$body = "<div class=\"message_info\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>From:</td><td class=\"message_info_data\"><a href=\"/showProfile.php?username=$row->fromUser\">$row->fromUser</a></td></tr><tr><td>Sent:</td><td class=\"message_info_data\">$dateSent</td></tr><tr><td>Subject:</td><td class=\"message_info_data\">$subject</td></tr></table></div>" . $body;
	
	print "<div class=\"message_body\">\n";
	print "<div class=\"message_body_content\">\n";
	print "$body\n";
	print "</div>";
	print "<div class=\"reply_button\"><a class=\"button\" href=\"composeMessage.php?reply=$id&returnURL=showMyMessages.php\"><span>Reply</span></a></div>";
	print "</div>";
	
	//mark message as read
	mysql_query("UPDATE messages SET status = 'read' WHERE id = '{$id}' AND toUser = '{$_SESSION['username']}'");
	
}

?>