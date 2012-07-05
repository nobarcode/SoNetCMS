<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$multipleId = sanitize_string($_REQUEST['multipleId']);

//read config file
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if (!is_array($multipleId)) {exit;}

foreach($multipleId as $id) {
	
	mysql_query("UPDATE users SET status = 'approved' WHERE username = '{$id}' AND status != 'approved'");
	
	if (mysql_affected_rows() > 0) {
		
		$result = mysql_query("SELECT name, email, allowEmailNotifications FROM users WHERE username = '{$id}' LIMIT 1");
		$row = mysql_fetch_object($result);
		
		if ($row->allowEmailNotifications == 1) {
			
			include("assets/core/config/notifications/approve_user/notification.php");
			
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
	
}

?>