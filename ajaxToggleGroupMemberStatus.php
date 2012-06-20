<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$username = sanitize_string($_REQUEST['username']);

if (trim($groupId) == "" || trim($username) == "" || trim($_SESSION['username']) == "") {$error = 1;}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

//validate that the user being edited is in the requested group and NOT the owner
$result = mysql_query("SELECT groups.name FROM groupsMembers INNER JOIN groups ON groups.id = groupsMembers.parentId WHERE groupsMembers.parentId = '{$groupId}' AND groupsMembers.username = '{$username}' AND groupsMembers.memberLevel != '1'");

if (mysql_num_rows($result) == 0) {
	
	$error = 1;
	
}

$row = mysql_fetch_object($result);
$groupName = $row->name;

if ($error != 1) {
	
	$result = mysql_query("SELECT status FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$username}' LIMIT 1");
	$row = mysql_fetch_object($result);

	if ($row->status == 'pending') { 
		
		//read config file
		$config = new ConfigReader();
		$config->loadConfigFile('assets/core/config/config.properties');
		
		$time = date("Y-m-d H:i:s", time());
		
		mysql_query("UPDATE groupsMembers SET dateJoined = '{$time}', status = 'approved' WHERE parentId = '{$groupId}' AND username = '{$username}'");
		
		$time = time();
		
		$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications FROM groupsMembers INNER JOIN users ON users.username = groupsMembers.username WHERE groupsMembers.parentId = '{$groupId}' AND groupsMembers.username = '{$username}'");
		
		while ($row = mysql_fetch_object($result)) {

			include("assets/core/config/notifications/approve_group_member/notification.php");
			
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
		
	} else {
		
		mysql_query("UPDATE groupsMembers SET status = 'pending' WHERE parentId = '{$groupId}' AND username = '{$username}'");
		
	}
	
}

?>