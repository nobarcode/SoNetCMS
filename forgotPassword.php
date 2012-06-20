<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$username = sanitize_string($_REQUEST['username']);
$email = sanitize_string($_REQUEST['email']);
$jb = sanitize_string($_REQUEST['jb']);

if (trim($username) != "" && trim($email) != "") {
	
	//test the username and password combination
	$result = mysql_query("SELECT username, level FROM users WHERE username = '{$username}' AND email = '{$email}'");
	$matchRows = mysql_num_rows($result);

	if (trim($username) == "") {$errorUsername = " <span class=\"error_text\">Required!</span>";}
	if (trim($email) == "") {$errorEmail = " <span class=\"error_text\">Required!</span>";}

	if ($matchRows < 1) {

		$message = "<div id=\"message_box\" class=\"message_box\" onClick=\"$('#message_box').hide();\"><b>There was an error processing your request, please check the following:</b><br>- Please verify your username and email address.</div>";
		
	} else {
		
		//read config file
		$config = new ConfigReader();
		$config->loadConfigFile('assets/core/config/config.properties');
		
		//create a new password for the user
		$password = random_string(8);
		$newPassword = hash('sha256', $password);
		
		//update the password
		mysql_query("UPDATE users SET password = '{$newPassword}' WHERE username = '{$username}' AND email = '{$email}'");
		
		include("assets/core/config/notifications/forgot_password/notification.php");
		
		$to = $email;
		$headers = "From: " . $config->readValue('siteEmailAddress') . "\r\nReply-To: " . $config->readValue('siteEmailAddress') . "\r\n";
		
		mail($to, $subject, $messageEmail, $headers);
		
		//set the message
		$message = "<div id=\"message_box\" class=\"message_box\" onClick=\"$('#message_box').hide();\"><b>Your password has been reset and sent to the e-mail address associated with your account.</b></div>";
		
	}
	
}

$jb = unsanitize_string($jb);
$jbHtml = htmlentities($jb);
$jb = urlencode($jb);
$return_url = htmlentities($return_url);

$username = htmlentities(unsanitize_string($username));
$email = htmlentities(unsanitize_string($email));

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/signIn.css");
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, '');

$site_container->showSiteContainerTop();

print <<< EOF
			$message
			<div class="subheader_title">Reset Password</div>
			<div id="form_container">
				<form id="resetPassword" name="resetPassword" action="forgotPassword.php" method="post" enctype="multipart/form-data">
				<table border="0" cellspacing="0" cellpadding="2">
					<tr valign="center">
						<td width="60">Username:</td><td><input type="text" id="username" name="username" value="$username">$errorUsername</td>
					</tr>
					<tr valign="center">
						<td width="60">E-mail:</td><td><input type="text" id="email" name="email" value="$email">$errorEmail<br></td>
					</tr>
				</table>
				<div class="form_buttons"><input type="submit" value="Reset"> <input type="button" value="Cancel" onClick="window.location='signIn.php?jb=$jb';"></div>
				<input type="hidden" name="jb" value="$jbHtml">
				</form>
			</div>
EOF;

$site_container->showSiteContainerBottom();

function random_string($showCharacters) {
	
	$time = date("YmdHis", time());
	$time = base_convert($time, 10, 16);
	
	$charList = '23456789abcdefghijkmnpqrstuvwxyz';
	$max = strlen($charList)-1;
	
	for ($i = 0; $i < $showCharacters; $i++) {
		
		$string .= $charList{rand(0, $max)};
		
	}
	
	return $string;

}

?>