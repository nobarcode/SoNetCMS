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

//check if user is already a member
$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND status = 'approved'");

if (mysql_num_rows($result) > 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('You are already a member of this group.');";
	print "$('#message_box').show();";
	exit;
	
}

//check if user already has a pending request
$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND status = 'pending'");

if (mysql_num_rows($result) > 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('A request to join this group is already pending.');";
	print "$('#message_box').show();";
	exit;
	
}

//check if user is part of an exclusive group
$result = mysql_query("SELECT username FROM groupsMembers INNER JOIN groups ON groups.id = groupsMembers.parentId WHERE username = '{$_SESSION['username']}' AND groups.exclusiveRequired = '1'");

if (mysql_num_rows($result) > 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('You are currently a member of an exclusive group. You cannot join another group while you\'re a member of an exclusive group.');";
	print "$('#message_box').show();";
	exit;
	
}

//get the properties for this group
$result = mysql_query("SELECT approvalRequired, exclusiveRequired FROM groups WHERE id = '{$groupId}'");
$row = mysql_fetch_object($result);

if ($row->approvalRequired == '1') {
	
	$approvalRequired = 1;
	$memberStatus = "pending";
	
} else {
	
	$time = date("Y-m-d H:i:s", time());
	$memberStatus = "approved";
	
}

if ($row->exclusiveRequired == '1') {
	
	$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId != '{$groupId}' AND username = '{$_SESSION['username']}'");
	
	if (mysql_num_rows($result) > 0) {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('This groups requires that you not be a member of any other group.');";
		print "$('#message_box').show();";
		exit;
		
	}
	
}

$result = mysql_query("INSERT INTO groupsMembers (parentId, username, memberLevel, dateJoined, status) VALUES ($groupId, '{$_SESSION['username']}', '3', '{$time}', '{$memberStatus}')");

if ($result) {
	
	//read config file
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/config.properties');
	
	$time = time();
	
	//get profile info for owners/admins
	$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications, groups.name AS groupName FROM groupsMembers INNER JOIN users ON users.username = groupsMembers.username INNER JOIN groups ON groups.id = '{$groupId}' WHERE groupsMembers.parentId = '{$groupId}' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2')");
	
	while($row = mysql_fetch_object($result)) {
		
		//send a message to the owner/admin of the group
		include("assets/core/config/notifications/join_group/notification.php");
		
		mysql_query("INSERT INTO messages (dateSent, toUser, fromUser, subject, body, status, system) VALUES ($time, '{$row->username}', '{$_SESSION['username']}', '" . sanitize_string(htmlentities($subject)) . "', '" . sanitize_string($notificationText) . "', 'unread', 1)");

		if ($row->allowEmailNotifications == 1) {

			$to = $row->email;
			
			$notificationEmail = "<html>";
			$notificationEmail .= "<body>";
			$notificationEmail .= $notificationText;
			$notificationEmail .= "</body>";
			$notificationEmail .= "</html>";
			
			$headers = "MIME-Version: 1.0\r\n"; 
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
			$headers .= "From: " . $config->readValue('siteEmailAddress') . "\r\n";
			$headers .= "Reply-To: " . $config->readValue('siteEmailAddress') . "\r\n";
			
			mail($to, $subject, $notificationEmail, $headers);
			
		}
		
	}
	
	if ($approvalRequired == 1) {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('Your request has been sent.');";
		print "$('#message_box').show();";
		exit;
		
	} else {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('You have successfully joined this group.');";
		print "$('#message_box').show();";
		print "regenerateMemberList(last_s, '');";
		print "regenerateMemberCount();";
		print "regenerateConversationList();";
		print "regenerateEventList('', '');";
		print "regenerateMemberBlogs();";
		exit;
		
	}
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Unknown error! Please try your request again.');";
	print "$('#message_box').show();";
	exit;
	
}

?>