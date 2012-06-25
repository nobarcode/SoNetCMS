<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$multipleId = sanitize_string($_REQUEST['multipleId']);

if (!is_array($multipleId) || trim($groupId) == "" || trim($_SESSION['username']) == "") {exit;}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");
	
	if (mysql_num_rows($result) == 0) {
		
		exit;
		
	} else {
		
		$memberLevelCheck = " AND memberLevel != '1'";
		 
	}
	
}

//grab the group name
$result = mysql_query("SELECT groups.name FROM groups WHERE groups.id = '{$groupId}'");

if (mysql_num_rows($result) == 0) {
	
	exit;
	
}

$row = mysql_fetch_object($result);
$groupName = $row->name;

//read config file
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

foreach($multipleId as $id) {
	
	$time = date("Y-m-d H:i:s", time());
	
	mysql_query("UPDATE groupsMembers SET dateJoined = '{$time}', status = 'approved' WHERE parentId = '{$groupId}' AND username = '{$id}'$memberLevelCheck AND status = 'pending'");
	
	if (mysql_affected_rows() > 0) {
		
		$time = time();
		
		$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications FROM groupsMembers INNER JOIN users ON users.username = groupsMembers.username WHERE groupsMembers.parentId = '{$groupId}' AND groupsMembers.username = '{$id}'");
		
		while ($row = mysql_fetch_object($result)) {

			$subject = "Your " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " $groupName membership request has been approved!";
			$message = "Hello " . htmlentities($row->name) . ",<br><br>Your <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/groups/id/$groupId\">" . htmlentities($groupName) . "</a> membership request has been approved!";
			
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
		
	}
	
}

?>