<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_group_membership_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$title = sanitize_string($_REQUEST['title']);
$restricted = sanitize_string($_REQUEST['restricted']);
$documentBody = sanitize_string($_REQUEST['documentBody']);

//load site group validator
$groupValidator = new GroupValidator();
$groupValidator->isGroupMember($groupId, $_SESSION['username']);
$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);

if ($groupValidator->isGroupAdmin) {
	
	$locked = sanitize_string($_REQUEST['locked']);
	
}

if (trim($groupId) == "" || trim($_SESSION['username']) == "") {
	
	exit;
	
}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//load main group information
	$result = mysql_query("SELECT name, allowNonMemberPosting FROM groups WHERE id = '{$groupId}' LIMIT 1");
	
	//if the group doesn't exist, exit
	if (mysql_num_rows($result) == 0) {
		
		exit;
		
	}
	
	$row = mysql_fetch_object($result);
	
	//if non-group members are not allowed to post and the user is not a member of the group or a site admin, exit
	if ($row->allowNonMemberPosting != 1 && (!$groupValidator->isGroupMember && $_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2)) {
		
		exit;
		
	}
	
}

if (trim($title) == "") {$error = 1; $errorMessage .= "- Please enter a title.<br>";}
if (trim($documentBody) == "") {$error = 1; $errorMessage .= "- Please enter text in the body of your conversation.<br>";}

if ($error != 1) {
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	$result = mysql_query("INSERT INTO conversations (groupId, title, dateCreated, author, restricted, locked) VALUES ('{$groupId}', '{$title}', '{$time}', '{$_SESSION['username']}', '{$restricted}', '{$locked}')");
	
	//grab the id for this new conversation thread
	$conversationId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
	
	//if the thread was created successfully, add the post
	if ($result) {
		
		$result = mysql_query("INSERT INTO conversationsPosts (parentId, body, dateCreated, author) VALUES ('{$conversationId}', '{$documentBody}', '{$time}', '{$_SESSION['username']}')");
		
		//grab the id for this new post
		$conversationPostId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
		
		//if the post was added successfully, send out notifications to friends and output success message to user
		if($result) {
			
			//read config file
			$config = new ConfigReader();
			$config->loadConfigFile('assets/core/config/config.properties');
			
			$time = time();
			
			$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications FROM friends INNER JOIN users ON friends.owner = '{$_SESSION['username']}' AND friends.friend = users.username AND friends.status = 'approved'");
			
			while ($row = mysql_fetch_object($result)) {
				
				include("assets/core/config/notifications/add_group_conversation/notification.php");
				
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
			print "$('#message_box').html('Your conversation has been added.');";
			print "$('#message_box').show();";
			print "regenerateList('', '', 'desc', 'desc', 'desc', 'desc', 'date', '');";
			print "$('#add_conversation')[0].reset();";
			
			//clear the editor window; clear undo history and disable buttons; set editor to not dirty
			print "CKEDITOR.instances.documentBody.setData('', function() {CKEDITOR.instances.documentBody.resetDirty();CKEDITOR.instances.documentBody.resetUndo();});";
			
			exit;
			
		} else {
			
			//rollback; delete the conversation since the post was not successful
			$result = mysql_query("DELETE conversations WHERE id = '{$conversationId}'");
			
			header('Content-type: application/javascript');
			print "$('#message_box').html('Unable to create conversation! Please try your request again.');";
			print "$('#message_box').show();";
			exit;
			
		}
		
	} else {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('Unable to create conversation! Please try your request again.');";
		print "$('#message_box').show();";
		exit;
		
	}
	
} else {
	
	$showMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('$showMessage');";
	print "$('#message_box').show();";
	
}

?>