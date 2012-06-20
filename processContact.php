<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$name = sanitize_string($_REQUEST['name']);
$email = sanitize_string($_REQUEST['email']);
$phone = sanitize_string($_REQUEST['phone']);
$subject = sanitize_string($_REQUEST['subject']);
$body = sanitize_string($_REQUEST['body']);
$code = sanitize_string($_REQUEST['code']);
$category = sanitize_string($_REQUEST['category']);
$returnUrl = sanitize_string($_REQUEST['returnUrl']);

//read config file and determine if the contact form is enabled, if not then exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/scripts/contact/contact.properties');

if ($config->readValue('enableContactForm') != 'true') {
	
	exit;
	
}

if (trim($returnUrl) != "") {
	
	$cancelAction = unsanitize_string($returnUrl);
	
} else {
	
	$cancelAction = "/";
	
}

if ($config->readValue('name') == 'true' && trim($name) == "") {$error = 1; $errorMessage .= "- Please supply your name.<br>";}
if ($config->readValue('email') == 'true' && trim($email) == "") {$error = 1; $errorMessage .= "- Please supply an e-mail address.<br>";}
if ($config->readValue('phone') == 'true' && trim($phone) == "") {$error = 1; $errorMessage .= "- Please supply a phone number.<br>";}
if ($config->readValue('subject') == 'true' && trim($subject) == "") {$error = 1; $errorMessage .= "- Please supply a subject.<br>";}
if ($config->readValue('body') == 'true' && trim($body) == "") {$error = 1; $errorMessage .= "- The body of your message is empty.<br>";}
if (md5($code) != $_SESSION['captchaKey']) {
	
	$error = 1; $errorMessage .= "- Please enter the code.<br>";
	$showCode .= "\n<tr valign=\"center\"><td colspan=\"2\"><iframe id=\"captcha\" src=\"captcha.php\" width=\"100\" height=\"28\" noresize scrolling=\"no\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\"></iframe><a href=\"javascript:reloadCaptcha();\"><img style=\"margin-left:5px;\" src=\"/assets/core/resources/images/reload_captcha.png\" border=\"0\" alt=\"Click here to get a different security code.\" title=\"Click here to get a different code.\"></a></td></tr>\n";
	$showCode .= "<tr valign=\"center\"><td colspan=\"2\"><input type=\"text\" id=\"code\" name=\"code\" style=\"width:94px\"> Enter the code.</td></tr>\n";
	
} else {
	
	$code = htmlentities(unsanitize_string($code));
	$codePass = "<input type=\"hidden\" id=\"code\" name=\"code\" value=\"$code\">";
	
}

if ($error != 1) {
	
	$time = time();
	$name = unsanitize_string($name);
	$email = unsanitize_string($email);
	
	if (trim($email) == "") {
		
		$to = $config->readValue('fromEmailAddress');
		
	} else {
		
		$to = $email;
		
	}
	
	$phone = unsanitize_string($phone);
	
	$subjectEmail = preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " communication";
	
	if (trim($subject) != "") {
		
		$subjectEmail .= ": $subject";
		
	}
	
	$body = htmlentities(unsanitize_string($body));
	$body = preg_replace("/\\n/", "<br>", $body);
	
	$to = $config->readValue('contactFormRecipients');
	
	$messageEmail = "<html>";
	$messageEmail .= "<body>";
	$messageEmail .= preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " has received the following message:<br><br><table border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td>name:</td><td>" . htmlentities($name) . "</td></tr><tr><td>phone:</td><td>" . htmlentities($phone) . "</td></tr><tr><td>e-mail</td><td>" . htmlentities($email) . "</td></tr></table><br><b>Message</b>:<br>$body";
	$messageEmail .= "</body>";
	$messageEmail .= "</html>";
	
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= "From: $email\r\n";
	$headers .= "Reply-To: email\r\n";
		
	mail($to, $subjectEmail, $messageEmail, $headers);
	
	if(trim($returnURL) != "") {
		
		unsanitize_string($returnURL);
		$clickBack = "<br><br><a style=\"color:#000000;\" href=\"$returnURL\">Click here</a> to return to the previous page.";
		
	}
	
	$showMessage = "<div id=\"message_box\" onClick=\"$('#message_box').hide();\"><b>Thank you for contacting us! Your message has been sent successfully.</b>$clickBack</div>";
	
	//clear all the form fields
	$name = "";
	$email = "";
	$phone = "";
	$subject = "";
	$body = "";
	
} else {
	
	$showMessage = "<div id=\"message_box\" onClick=\"$('#message_box').hide();\"><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>";
	
	$name = htmlentities(unsanitize_string($name));
	$email = htmlentities(unsanitize_string($email));
	$phone = htmlentities(unsanitize_string($phone));
	$subject = htmlentities(unsanitize_string($subject));
	$body = htmlentities(unsanitize_string($body));
	
} 

$showDomain = preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']);

$htmlCategory = htmlentities(unsanitize_string($category));

$showContactForm =<<< EOF
			<div id="contact_input">
				<form id="compose_message" method="post" action="processContact.php">
					<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Name:</td><td width="100%"><input type="text" id="name" name="name" size="32" value="$name"></td></tr>
					<tr valign="center"><td nowrap>E-mail:</td><td width="100%"><input type="text" id="email" name="email" size="32" value="$email"></td></tr>
					<tr valign="center"><td nowrap>Phone:</td><td width="100%"><input type="text" id="phone" name="phone" size="32" value="$phone"></td></tr>
					<tr valign="center"><td nowrap>Subject:</td><td width="100%"><input type="text" id="subject" name="subject" value="$subject" style="width:99%;"></td></tr>
					<tr valign="center"><td colspan="2"><textarea id="body" name="body" rows="16" style="width:99%">$body</textarea></td></tr>$showCode
					<tr valign="center"><td colspan="2"><input type="submit" id="submit" value="Send"> <input type="button" id="cancel" value="Cancel" onClick="window.location='$cancelAction';"></td></tr>
					<input type="hidden" id="category" name="category" value="$htmlCategory">
					</table>
				</form>
			</div>
EOF;

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/contact.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/contact.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("assets/core/layout/contact/layout_contact.php");

$site_container->showSiteContainerBottom();

?>