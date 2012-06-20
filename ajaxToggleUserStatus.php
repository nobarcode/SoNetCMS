<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$username = sanitize_string($_REQUEST['username']);

$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

//don't do anything if the username variable is missing or if sign up approvals are not enabled
if (trim($username) == "" || $config->readValue('requireSignUpApproval') == 'false') {$error = 1;}

if ($error != 1) {
	
	$result = mysql_query("SELECT level, status FROM users WHERE username = '{$username}' LIMIT 1");
	$row = mysql_fetch_object($result);

	if ($row->status == 'pending') { 
		
		mysql_query("UPDATE users SET status = 'approved' WHERE username = '{$username}'");
		
		$time = time();
		
		$result = mysql_query("SELECT name, email, allowEmailNotifications FROM users WHERE username = '{$username}' LIMIT 1");
		$row = mysql_fetch_object($result);
		
		if ($row->allowEmailNotifications == 1) {
			
			//read config file
			$config = new ConfigReader();
			$config->loadConfigFile('assets/core/config/config.properties');
			
			include("assets/core/config/notifications/approve_user/notification.php");
			
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
		
		print "Approved";
			
	} elseif ($row->status == 'approved' && $row->level != 1) {
		
		mysql_query("UPDATE users SET status = 'pending' WHERE username = '{$username}'");
		
		print "Pending";
		
	} else {
		
		print "Approved";
		
	}
	
}

?>