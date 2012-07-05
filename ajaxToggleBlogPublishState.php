<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$showImage = sanitize_string($_REQUEST['showImage']);

if (trim($id) == "" || trim($_SESSION['username']) == "") {$error = 1;}

if ($error != 1) {
	
	$time = time();
	
	$result = mysql_query("SELECT category, publishState, title FROM blogs WHERE id = '{$id}' AND usernameCreated = '{$_SESSION['username']}' LIMIT 1");
	$row = mysql_fetch_object($result);
	$title = $row->title;
	$category = sanitize_string($row->category);
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	if ($row->publishState == 'Unpublished') { 
		
		//read config file
		$config = new ConfigReader();
		$config->loadConfigFile('assets/core/config/config.properties');
		
		mysql_query("UPDATE blogs SET datePublished = '{$time}', publishState = 'Published' WHERE id = '{$id}' AND usernameCreated = '{$_SESSION['username']}'");
		
		//create the notifications for this published blog
		$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications FROM friends INNER JOIN users ON friends.owner = '{$_SESSION['username']}' AND friends.friend = users.username AND friends.status = 'approved'");

		while ($row = mysql_fetch_object($result)) {

			include("assets/core/config/notifications/publish_blog/notification.php");
			
			mysql_query("INSERT INTO messages (dateSent, toUser, fromUser, subject, body, status, system) VALUES ($time, '{$row->username}', '{$_SESSION['username']}', '" . sanitize_string($subject) . "', '" . sanitize_string($notificationText) . "', 'unread', 1)");
			
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
		
		if ($showImage == "yes") {
			
			print "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_published.gif\" border=\"0\"> Published";
			
		} else {
			
			print "Published";
			
		}
		
	} else {
		
		mysql_query("UPDATE blogs SET publishState = 'Unpublished' WHERE id = '{$id}' AND usernameCreated = '{$_SESSION['username']}'");
		
		if ($showImage == "yes") {
			
			print "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_unpublished.gif\" border=\"0\"> Unpublished";
			
		} else {
			
			print "Unpublished";
			
		}
		
	}
	
}

?>