<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$username = sanitize_string($_REQUEST['username']);

//if either session or username are empty, exit
if (trim($username) == "" || trim($_SESSION['username']) == "") {
	
	exit;
	
}

//don't allow friends with self
if ($_SESSION['username'] == $username) {

	exit;
	
}

//check if user is already in the list
$result = mysql_query("SELECT friend FROM friends WHERE ((owner = '{$_SESSION['username']}' AND friend = '{$username}') OR (friend = '{$_SESSION['username']}' AND owner = '{$usermame}')) AND status != 'pending'");

if (mysql_num_rows($result) > 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('This member is already in your list.');";
	print "$('#message_box').show();";
	exit;
	
}

//check if user already has a pending request
$result = mysql_query("SELECT friend FROM friends WHERE ((owner = '{$_SESSION['username']}' AND friend = '{$username}') OR (friend = '{$_SESSION['username']}' AND owner = '{$username}')) AND status = 'pending'");

if (mysql_num_rows($result) > 0) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('A request is already pending.');";
	print "$('#message_box').show();";
	exit;
	
}

$time = time();
$result = mysql_query("INSERT INTO friends (owner, friend, dateAdded, status) VALUES ('{$_SESSION['username']}', '{$username}', $time, 'pending')");

if ($result) {
	
	//read config file
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/config.properties');
	
	//get profile info for user being added as friend
	$result = mysql_query("SELECT name, email, allowEmailNotifications FROM users WHERE username = '{$username}'");
	$row = mysql_fetch_object($result);
	
	//create a message for the user being added
	include("assets/core/config/notifications/add_friend/notification.php");
	
	mysql_query("INSERT INTO messages (dateSent, toUser, fromUser, subject, body, status, system) VALUES ($time, '{$username}', '{$_SESSION['username']}', '" . sanitize_string($subject) . "', '" . sanitize_string($message) . "', 'unread', 1)");
	
	if ($row->allowEmailNotifications == 1) {
		
		//send an e-mail to the user being added
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
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Your request has been sent.');";
	print "$('#message_box').show();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Unknown error! Please try your request again.');";
	print "$('#message_box').show();";
	exit;
	
}

?>