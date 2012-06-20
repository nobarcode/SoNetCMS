<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_group_membership_validator.php");
include("class_config_reader.php");

$parentId = sanitize_string($_REQUEST['parentId']);
$documentBody = sanitize_string($_REQUEST['documentBody']);

if (trim($parentId) == "" || trim($_SESSION['username']) == "") {
	
	exit;
	
}

//load main group information
$result = mysql_query("SELECT conversations.groupId, conversations.title, conversations.restricted, conversations.locked FROM conversations WHERE conversations.id = '{$parentId}' LIMIT 1");
if (mysql_num_rows($result) == 0) {
	
	exit;
	
}

$row = mysql_fetch_object($result);
$groupId = $row->groupId;
$conversationTitle = $row->title;
$locked = $row->locked;
$groupName = $row->name;

//load site group validator
$groupValidator = new GroupValidator();
$groupValidator->isGroupMember($groupId, $_SESSION['username']);
$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);

if ($row->locked == 1) {
	
	$locked = true;
	
} else {
	
	$locked = false;
	
}

//conversation lock check
if ((($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && !$groupValidator->isGroupAdmin) && $locked) || (($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && !$groupValidator->isGroupMember) && $row->restricted == 1)) {
	
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
	if ($row->allowNonMemberPosting != 1 && (!$groupValidator->isGroupMember && $_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3)) {
		
		exit;
		
	} 
	
}

if (trim($documentBody) == "") {$error = 1; $errorMessage .= "- Please enter text in the body of your reply.<br>";}

if ($error != 1) {
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	//strip invalid tags
	$body = strip_tags($body, '<p><br /><span><strong><em><u><a><img>');
	
	$result = mysql_query("INSERT INTO conversationsPosts (parentId, body, dateCreated, author) VALUES ('{$parentId}', '{$documentBody}', '{$time}', '{$_SESSION['username']}')");
	
	//grab the id for this new conversation
	$id = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
	
	if ($result) {
		
		//read config file
		$config = new ConfigReader();
		$config->loadConfigFile('assets/core/config/config.properties');
		
		$time = time();
		
		$messageURL = "showEventCalendar-id-$parentId.php";
		
		$result = mysql_query("SELECT users.username, users.name, users.email, users.allowEmailNotifications FROM friends INNER JOIN users ON friends.owner = '{$_SESSION['username']}' AND friends.friend = users.username AND friends.status = 'approved'");
		
		while ($row = mysql_fetch_object($result)) {
			
			include("assets/core/config/notifications/add_group_conversation_post/notification.php");
			
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
		print "regenerateList('last_post', '');";
		print "$('#message_box').html('Your reply has been added.');";
		print "$('#message_box').show();";
		print "CKEDITOR.instances.documentBody.setData('', function() {CKEDITOR.instances.documentBody.resetDirty();CKEDITOR.instances.documentBody.resetUndo();});";
		print "$('#reply_to_conversation').reset();";
		exit;
		
		
	} else {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('Unknown error! Please try your request again.');";
		print "$('#message_box').show();";
		exit;
		
	}
	
} else {
	
	$showMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('$showMessage');";
	print "$('#message_box').show();";
	exit;
	
}

?>