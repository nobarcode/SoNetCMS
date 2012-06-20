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
$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND status = 'approved'");

if (mysql_num_rows($result) == 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('You are not a member of this group.');";
	print "$('#message_box').show();";
	exit;
	
}

//check if user is the owner
$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND memberLevel = '1' AND status = 'approved'");

if (mysql_num_rows($result) == 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('You are not the owner of this group.');";
	print "$('#message_box').show();";
	exit;
	
}

//grab the next member in line (the oldest admin in the group)
$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND memberLevel = '2' ORDER BY dateJoined ASC LIMIT 1");

if (mysql_num_rows($result) == 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('There are no administrators assigned to this group. Please assign an administrator and then retry your request.');";
	print "$('#message_box').show();";
	exit;
	
} else {
	
	$row = mysql_fetch_object($result);
	$username = $row->username;
	
}

$result = mysql_query("UPDATE groupsMembers SET memberLevel = '2' WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND memberLevel = '1' AND status = 'approved'");

if ($result) {
	
	$result = mysql_query("UPDATE groupsMembers SET memberLevel = '1' WHERE parentId = '{$groupId}' AND username = '{$username}' AND memberLevel = '2'");
	
	if ($result) {
		
		//read config file
		$config = new ConfigReader();
		$config->loadConfigFile('assets/core/config/config.properties');
		
		$time = time();
		
		$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications, groups.name AS groupName FROM groupsMembers INNER JOIN users ON users.username = groupsMembers.username INNER JOIN groups ON groups.id = '{$groupId}' WHERE groupsMembers.parentId = '{$groupId}' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2')");

		while($row = mysql_fetch_object($result)) {

			//send a message to the owner/admin of the group
			$subject = "Tranfer of Group Ownership: $row->groupName";
			$subject = sanitize_string($subject);
			$messageSystem = "Hello $row->name,<br><br>" . $_SESSION['username'] . " has transferred ownership of $row->groupName to $username<br><br>To view the group's current members, <a href=\"manageGroupMembers.php?id=$groupId\">click here</a>.";
			$messageSystem = sanitize_string($messageSystem);

			mysql_query("INSERT INTO messages (dateSent, toUser, fromUser, subject, body, status, system) VALUES ($time, '{$row->username}', '{$_SESSION['username']}', '{$subject}', '{$messageSystem}', 'unread', 1)");

			if ($row->allowEmailNotifications == 1) {

				//send an e-mail to the owner/admin of the group
				$to = $row->email;
				$subject = "Tranfer of Group Ownership: $row->groupName";
				$message = "Hello $row->name,\n\n" . $_SESSION['username'] . " has transferred ownership of $row->groupName to $username\n\nTo view the group's current members follow the link below:\n\nhttp://" . $_SERVER['HTTP_HOST'] . "/manageGroupMembers.php?id=$groupId";
				$headers = "From: " . $config->readValue('siteEmailAddress') . "\r\nReply-To: " . $config->readValue('siteEmailAddress') . "\r\n";

				mail($to, $subject, $message, $headers);

			}
			
		}
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('You have successfully transferred ownership.');";
		print "$('#message_box').show();";
		exit;

	} else {

		header('Content-type: application/javascript');
		print "$('#message_box').html('Unknown error! Please try your request again.');";
		print "$('#message_box').show();";
		exit;

	}
	
} else {

	header('Content-type: application/javascript');
	print "$('#message_box').html('Unknown error! Please try your request again.');";
	print "$('#message_box').show();";
	exit;
	
}

?>