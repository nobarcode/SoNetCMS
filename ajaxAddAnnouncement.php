<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$title = sanitize_string($_REQUEST['title']);
$body = sanitize_string($_REQUEST['body']);
$monthAdd = sanitize_string($_REQUEST['monthAdd']);
$dayAdd = sanitize_string($_REQUEST['dayAdd']);
$yearAdd = sanitize_string($_REQUEST['yearAdd']);
$linkUrl = sanitize_string($_REQUEST['linkUrl']);
$linkText = sanitize_string($_REQUEST['linkText']);

if (trim($title) == "") {$error = 1; $errorMessage .= "- Please provide a title.<br>";}
if (trim($body) == "") {$error = 1; $errorMessage .= "- Please enter text in the body of your announcement.<br>";}

if ($error != 1) {
	
	$time = getdate();
	$createDate = $time['year'] . "-" . $time['mon'] . "-" . $time['mday'] . " " . $time['hours'] . ":" . $time['minutes'] . ":" . $time['seconds'];
	$expireDate = "$yearAdd-$monthAdd-$dayAdd 00:00";
		
	$result = mysql_query("INSERT INTO announcements (usernameCreated, dateCreated, dateExpires, title, body, linkUrl, linkText, publishState) VALUES ('{$_SESSION['username']}', '{$createDate}', '{$expireDate}', '{$title}', '{$body}', '{$linkUrl}', '{$linkText}', 'Unpublished')");
	
	if($result) {
		
		//read config file and determine content creation notification level
		$config = new ConfigReader();
		$config->loadConfigFile('assets/core/config/config.properties');
		
		if ($config->readValue('contentCreationNotifications') == 'true' && $_SESSION['userLevel'] > 3) {
			
			$notificationQuery .= " AND (";
			
			if ($config->readValue('masterNotification') == 'true') {
				
				$notificationQuery .= "level = 1";
				
			}
			
			if ($config->readValue('adminNotification') == 'true') {
				
				if ($config->readValue('masterNotification') == 'true') {

					$notificationQuery .= " OR ";

				}
				
				$notificationQuery .= "level = 2";
				
			}
			
			if ($config->readValue('editorNotification') == 'true') {
				
				if ($config->readValue('masterNotification') == 'true' || $config->readValue('adminNotification') == 'true') {

					$notificationQuery .= " OR ";

				}
				
				$notificationQuery .= "level = 3";
				
			}
			
			$notificationQuery .= ")";
			
			$result = mysql_query("SELECT name, email FROM users WHERE allowEmailNotifications = '1'$notificationQuery");

			while ($row = mysql_fetch_object($result)) {

				include("assets/core/config/notifications/add_announcement/notification.php");
				
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
		print "$('#add_announcement')[0].reset();\n";
		print "regenerateList('', '', 'desc', 'desc', 'desc', 'desc', 'dateCreated', '');";
		exit;
		
	} else {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<div>Unknown error! Please try your request again.</div>');";
		print "$('#message_box').show();";
		exit;
		
	}
	
} else {
	
	$showMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div>$showMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
	
}

?>