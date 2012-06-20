<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);

//if either session or id are empty, exit
if (trim($groupId) == "" || trim($_SESSION['username']) == "") {
	
	exit;
	
}

//check if user is a member
$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}'");

if (mysql_num_rows($result) == 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('You are not a member of this group.');";
	print "$('#message_box').show();";
	exit;
	
}

//check if user is the owner
$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND memberLevel = '1' AND status = 'approved'");

if (mysql_num_rows($result) > 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('You are the owner of this group. <a href=\"javascript:transferOwnership();\">Click here to transfer ownership.</a>');";
	print "$('#message_box').show();";
	exit;
	
}

$result = mysql_query("DELETE FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}'");

if ($result) {
	
	//read config file
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/config.properties');
	
	$time = time();
	
	//get profile info for owners/admins
	$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications, groups.name AS groupName FROM groupsMembers INNER JOIN users ON users.username = groupsMembers.username INNER JOIN groups ON groups.id = '{$groupId}' WHERE groupsMembers.parentId = '{$groupId}' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2')");
	
	while($row = mysql_fetch_object($result)) {
		
		//send a message to the owner/admin of the group
		include("assets/core/config/notifications/leave_group/notification.php");
		
		mysql_query("INSERT INTO messages (dateSent, toUser, fromUser, subject, body, status, system) VALUES ($time, '{$row->username}', '{$_SESSION['username']}', '" . sanitize_string(htmlentities($subject)) . "', '" . sanitize_string($message) . "', 'unread', 1)");
		
		if ($row->allowEmailNotifications == 1) {

			$to = $row->email;
			
			$messageEmail = "<html>";
			$messageEmail .= "<body>";
			$messageEmail .= $message;
			$messageEmail .= "</body>";
			$messageEmail .= "</html>";
			
			$headers = "MIME-Version: 1.0\r\n"; 
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
			$headers .= "From: " . $config->readValue('siteEmailAddress') . "\r\n";
			$headers .= "Reply-To: " . $config->readValue('siteEmailAddress') . "\r\n";
			
			mail($to, $subject, $messageEmail, $headers);
			
		}
		
	}
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('You have successfully left the group.');";
	print "$('#message_box').show();";
	print "regenerateMemberList(last_s, '');";
	print "regenerateMemberCount();";
	print "regenerateConversationList();";
	print "regenerateEventList('', '');";
	print "regenerateMemberBlogs();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Unknown error! Please try your request again.');";
	print "$('#message_box').show();";
	exit;
	
}

?>