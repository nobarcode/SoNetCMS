<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$parentId = sanitize_string($_REQUEST['parentId']);
$id = sanitize_string($_REQUEST['id']);
$type = sanitize_string($_REQUEST['type']);
$body = sanitize_string($_REQUEST['body']);

if (trim($parentId) == "" || trim($type) == "" || trim($_SESSION['username']) == "") {
	
	exit;
	
}

switch ($type) {
	
	case "documentComment":
		
		$outputTo = "#message_box";
		break;
	
	case "documentImageComment":
		
		$outputTo = "#message_box";
		break;
	
	case "userProfileComment":
		
		$outputTo = "#comment_message_box";
		break;
		
	case "userImageComment":
					
		$outputTo = "#message_box";
		break;

	case "blogComment":
		
		$outputTo = "#message_box";
		break;

	case "eventComment":
		
		$outputTo = "#message_box";
		break;
		
	case "groupImageComment":
		
		$outputTo = "#message_box";
		break;
		
}

if (trim($body) == "") {$error = 1; $errorMessage .= "- Please enter a comment.<br>";}

if ($error != 1) {
	
	//get the current date and time
	$time = time();
	
	switch ($type) {
		
		case "documentComment":
			
			//gather document information to display in notifications
			$result = mysql_query("SELECT documents.shortcut, documents.title FROM documents WHERE documents.id = '{$parentId}'");
			$row = mysql_fetch_object($result);
			$messageURL = "http://" . $_SERVER['HTTP_HOST'] . "/documents/open/$row->shortcut";
			include("assets/core/config/notifications/add_comment/document/notification.php");
			
			//add comment to database
			$result = mysql_query("INSERT INTO commentsDocuments (parentId, type, username, dateCreated, body) VALUES ('{$parentId}', '{$type}', '{$_SESSION['username']}', {$time}, '{$body}')");
			
			//grab the id for this comment
			$commentId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
			
			break;
		
		case "documentImageComment":
			
			$messageURL = "http://" . $_SERVER['HTTP_HOST'] . "/galleries/id/$parentId";
			include("assets/core/config/notifications/add_comment/document_image/notification.php");
			
			//add comment to database
			$result = mysql_query("INSERT INTO commentsImages (parentId, imageId, type, username, dateCreated, body) VALUES ('{$parentId}', '{$id}', '{$type}', '{$_SESSION['username']}', {$time}, '{$body}')");
			
			//grab the id for this comment
			$commentId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
			
			break;
		
		case "userProfileComment":
			
			//check if commentsFromFriendsOnly is enabled for the user being commented on
			$result = mysql_query("SELECT commentsFromFriendsOnly FROM users WHERE username = '{$parentId}'");
			$row = mysql_fetch_object($result);
			
			//if commentsFromFriendsOnly is enabled and commentor is not a friend, exit
			if ($row->commentsFromFriendsOnly == '1') {
				
				$result = mysql_query("SELECT owner FROM friends WHERE owner = '{$parentId}' AND friend = '{$_SESSION['username']}' AND status = 'approved'");
				
				if(mysql_num_rows($result) == 0) {
					
					exit;
					
				}
				
			}
			
			//gather document information to display in notifications
			$messageURL = "http://" . $_SERVER['HTTP_HOST'] . "/showProfile.php?username=" . urlencode(unsanitize_string($parentId));
			include("assets/core/config/notifications/add_comment/profile/notification.php");
			
			//add comment to database
			$result = mysql_query("INSERT INTO commentsUserProfiles (parentId, username, dateCreated, body) VALUES ('{$parentId}', '{$_SESSION['username']}', {$time}, '{$body}')");
			
			//grab the id for this comment
			$commentId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
			
			break;
			
		case "userImageComment":
						
			//gather document information to display in notifications
			$messageURL = "http://" . $_SERVER['HTTP_HOST'] . "/usergalleries/username/$parentId";
			include("assets/core/config/notifications/add_comment/profile_image/notification.php");
			
			//add comment to database
			$result = mysql_query("INSERT INTO commentsImages (parentId, imageId, type, username, dateCreated, body) VALUES ('{$parentId}', '{$id}', '{$type}', '{$_SESSION['username']}', {$time}, '{$body}')");
			
			//grab the id for this comment
			$commentId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
			
			break;
			
		case "blogComment":
			
			//gather document information to display in notifications
			$result = mysql_query("SELECT title, usernameCreated FROM blogs where id = '{$parentId}'");
			$row = mysql_fetch_object($result);
			$messageURL = "http://" . $_SERVER['HTTP_HOST'] . "/blogs/id/$parentId";
			include("assets/core/config/notifications/add_comment/blog/notification.php");
			
			//add comment to database
			$result = mysql_query("INSERT INTO commentsDocuments (parentId, type, username, dateCreated, body) VALUES ('{$parentId}', '{$type}', '{$_SESSION['username']}', {$time}, '{$body}')");
			
			//grab the id for this comment
			$commentId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
			
			break;

		case "eventComment":
			
			//gather document information to display in notifications
			$result = mysql_query("SELECT events.category, events.title FROM events WHERE events.id = '{$parentId}'");
			$row = mysql_fetch_object($result);
			$messageURL = "http://" . $_SERVER['HTTP_HOST'] . "/events/id/$parentId";
			include("assets/core/config/notifications/add_comment/event/notification.php");
			
			if(accessPrivateGroupEvent($parentId)) {
				
				//add comment to database
				$result = mysql_query("INSERT INTO commentsDocuments (parentId, type, username, dateCreated, body) VALUES ('{$parentId}', '{$type}', '{$_SESSION['username']}', {$time}, '{$body}')");
				
				//grab the id for this comment
				$commentId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
				
			} else {
				
				exit;
				
			}
			
			break;
			
		case "groupImageComment":
			
			//gather document information to display in notifications
			$result = mysql_query("SELECT name FROM groups WHERE id = '{$parentId}' LIMIT 1");
			$row = mysql_fetch_object($result);
			$messageURL = "http://" . $_SERVER['HTTP_HOST'] . "/groupgalleries/id/$parentId";
			include("assets/core/config/notifications/add_comment/group_image/notification.php");
			
			//add comment to database
			$result = mysql_query("INSERT INTO commentsImages (parentId, imageId, type, username, dateCreated, body) VALUES ('{$parentId}', '{$id}', '{$type}', '{$_SESSION['username']}', {$time}, '{$body}')");
			
			//grab the id for this comment
			$commentId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
			
			break;
			
	}
	
	if ($result) {
		
		//read config file
		$config = new ConfigReader();
		$config->loadConfigFile('assets/core/config/config.properties');
		
		$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications FROM friends INNER JOIN users ON friends.owner = '{$_SESSION['username']}' AND friends.friend = users.username AND friends.status = 'approved'");
		
		$time = time();
		
		while ($row = mysql_fetch_object($result)) {
			
			$subject = $_SESSION['username'] . " has posted a new comment on " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . "!";
			$message = "Hello " . htmlentities($row->name) . ",<br><br>Your friend " . $_SESSION['username'] . $messageBody;
			
			mysql_query("INSERT INTO messages (dateSent, toUser, fromUser, subject, body, status, system) VALUES ($time, '{$row->username}', '{$_SESSION['username']}', '" . sanitize_string($subject) . "', '" . sanitize_string($message) . "', 'unread', 1)");
			
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
		print "$('$outputTo').html('Your comment has been added.');";
		print "$('$outputTo').show();";
		exit;
		
	} else {
		
		header('Content-type: application/javascript');
		print "$('$outputTo').html('Unknown error! Please try your request again.');";
		print "$('$outputTo').show();";
		exit;
		
	}
	
} else {
	
	$showMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('$outputTo').html('$showMessage');";
	print "$('$outputTo').show();";
	exit;
	
}

function accessPrivateGroupEvent($parentId) {
	
	$result = mysql_query("SELECT groupId, private FROM events WHERE id = '{$parentId}'");
	$row = mysql_fetch_object($result);
	$groupId = $row->groupId;
	$private = $row->private;
	
	if (trim($groupId) != "" && $private == 1) {
		
		if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {

			//if the user is not an admin, validate that the user is allowed to delete the requested group
			$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND status = 'approved'");

			if (mysql_num_rows($result) == 0) {

				return false;

			}

		}
		
	}
	
	return true;
	
}

?>