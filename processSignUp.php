<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$username = sanitize_string($_REQUEST['username']);
$password = sanitize_string($_REQUEST['password']);
$confirmPassword = sanitize_string($_REQUEST['confirmPassword']);
$name = sanitize_string($_REQUEST['name']);
$email = sanitize_string($_REQUEST['email']);
$confirmEmail = sanitize_string($_REQUEST['confirmEmail']);
$code = sanitize_string($_REQUEST['code']);
$terms = sanitize_string($_REQUEST['terms']);
$jb = sanitize_string($_REQUEST['jb']);
$return_url = sanitize_string($_REQUEST['return_url']);

//read config file
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if (trim($name) == "") {$error = 1; $errorMessage .= "- Please provide a name.<br>";}
if (trim($email) == "") {$error = 1; $errorMessage .= "- Please provide an e-mail address.<br>";}
if (trim($confirmEmail) == "") {$error = 1; $errorMessage .= "- Please confirm the e-mail address.<br>";}
if ((trim($email) != "" && trim($confirmEmail) != "") && ($email != $confirmEmail)) {$error = 1; $errorMessage .= "- E-mail and confirm e-mail fields do not match.<br>";}
if (trim($username) == "") {$error = 1; $errorMessage .= "- Please provide a username.<br>";}
if (trim($username) !="" && !preg_match("/^[0-9a-z_:.-]+$/i", $username)) {$error = 1; $errorMessage .="- Usernames can only contain letters and numbers or the following: hyphens (\"-\"), underscores (\"_\"), colons (\":\"), or periods (\".\")<br>";}
if (trim($password) == "") {$error = 1; $errorMessage .= "- Please provide a password.<br>";}
if (trim($confirmPassword) == "") {$error = 1; $errorMessage .= "- Please confirm the password.<br>";}
if ((trim($password) != "" && trim($confirmPassword) != "") && ($password != $confirmPassword)) {$error = 1; $errorMessage .= "- Password and confirm password fields did not match.<br>";}
if (md5($code) != $_SESSION['captchaKey']) {
	
	$error = 1; $errorMessage .= "- Please enter the code.<br>";
	$showCode .= "\n<br/><table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">\n";
	$showCode .= "<tr valign=\"center\"><td colspan=\"2\"><iframe id=\"captcha\" src=\"captcha.php\" width=\"100\" height=\"28\" noresize scrolling=\"no\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\"></iframe><a href=\"javascript:reloadCaptcha();\"><img style=\"margin-left:5px;\" src=\"/assets/core/resources/images/reload_captcha.png\" border=\"0\" alt=\"Click here to get a different security code.\" title=\"Click here to get a different code.\"></a></td></tr>\n";
	$showCode .= "<tr valign=\"center\"><td colspan=\"2\"><input type=\"text\" id=\"code\" name=\"code\" style=\"width:94px\"> Enter the code.</td></tr>\n";
	$showCode .= "</table>\n";
	
} else {
	
	$code = htmlentities(unsanitize_string($code));
	$codePass = "<input type=\"hidden\" id=\"code\" name=\"code\" value=\"$code\">";
	
}

if ($config->readValue('displaySignUpAgreementLink') == 'true') {
	
	if (trim($terms) == "") {$error = 1; $errorMessage .= "- You must agree to " . $config->readValue('signUpAgreementLinkLabel') . ".<br>";}
	
}

//check if the selected username already exists
$testUsername = strtoupper($username);
$result = mysql_query("SELECT username FROM users WHERE UPPER(username) = '{$testUsername}'");
$row = mysql_fetch_object($result);

if (mysql_num_rows($result) > 0) {$error = 1; $errorMessage .= "- The username you supplied is not available.<br>";}

//check if the selected e-mail address already exists
$testUsername = strtoupper($email);
$result = mysql_query("SELECT email FROM users WHERE UPPER(email) = '{$email}'");
$row = mysql_fetch_object($result);

if (mysql_num_rows($result) > 0) {$error = 1; $errorMessage .= "- The e-mail address you supplied is not available.<br>";}

if ($error != 1) {
	
	//check if config.properties file requires approvals on new sign ups
	if ($config->readValue('requireSignUpApproval') == 'false') {
		
		$status = "approved";
		
	} else {
		
		$status = "pending";
		
	}
	
	$_SESSION['captchaKey'] = "";
	
	$newPassword = hash('sha256', $password);
	
	//record time and IP then log the user in, then redirect
	$time = date("Y-m-d H:i:s", time());
	$lastIpAddress = $_SERVER['REMOTE_ADDR'];
		
	$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	
	//create this user's file directory
	mkdir("$script_directory/cms_users/$username") or die("SYSTEM ERROR: unable to create personal directory!");
	
	$result = mysql_query("INSERT INTO users (username, password, name, email, allowEmailNotifications, dateCreated, level, status, lastLogin, lastIpAddress) VALUES ('{$username}', '{$newPassword}', '{$name}', '{$email}', 1, '{$time}', 0, '{$status}', '{$time}', '{$lastIpAddress}')");
	
	if ($result && $config->readValue('requireSignUpApproval') == 'false') {
		
		//set the user's session variables
		$_SESSION['username'] = $username;
		$_SESSION['userLevel'] = 0;

		//set this new user's file manager session variables	
		$_SESSION['isLoggedIn'] = true;
		$_SESSION['sysRootPath'] = "$script_directory/cms_users/$username";
		$_SESSION['wwwRootPath'] = "/cms_users/$username";
		$_SESSION['maxDiskSpace'] = 100; // 100 MB max diskspace
	
	} else {
		
		//if approval is required after sign up, send e-mail notifications to admins and then forward the user to the "approval pending page"
		$result = mysql_query("SELECT username, name, email, allowEmailNotifications FROM users WHERE level = '1' OR level = '2'");
		
		$time = time();
		
		while ($row = mysql_fetch_object($result)) {
			
			include("assets/core/config/notifications/process_sign_up/notification.php");
			
			mysql_query("INSERT INTO messages (dateSent, toUser, fromUser, subject, body, status, system) VALUES ($time, '{$row->username}', '{$username}', '" . sanitize_string($subject) . "', '" . sanitize_string($message) . "', 'unread', 1)");
			
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
		
		$_SESSION['sign_up_pending'] = true;
		header("Location: memberApprovalPending.php");
		exit;
		
	}
		
	if (trim($jb) == "") {
		
		$jb = "./";
		
	}
	
	header("Location: $jb");
	exit;
	
}

$name = unsanitize_string($name);
$email = unsanitize_string($email);
$confirmEmail = unsanitize_string($confirmEmail);
$username = unsanitize_string($username);

$name = htmlentities($name);
$email = htmlentities($email);
$confirmEmail = htmlentities($confirmEmail);
$username = htmlentities($username);

$showMessage = "<div id=\"message_box\" onClick=\"$('#message_box').hide();\"><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>";

$jb = unsanitize_string($jb);
$jbHtml = htmlentities($jb);
$jb = urlencode($jb);
$return_url = htmlentities($return_url);

if ($config->readValue('displaySignUpAgreementLink') == 'true') {
	
	$signUpAgreementLink = "<a href=\"" . $config->readValue('signUpAgreementLinkUrl') . "\" onclick=\"window.open(this.href, 'ControlPanelOverview', 'resizable=yes,status=no,location=no,toolbar=yes,menubar=no,fullscreen=no,scrollbars=yes,dependent=no,width=900,height=767'); return false;\">" . $config->readValue('signUpAgreementLinkLabel') . "</a>";
	
	if($terms == "1") {
		
		$termsChecked = " checked";
		
	}
	
	$agreementCheckbox = "\n<br/>\n<input type=\"checkbox\" id=\"terms\" name=\"terms\" value=\"1\"$termsChecked> I agree to $signUpAgreementLink.\n<br/>";
	
}

$showSignUpForm =<<< EOF
	<div id="sign_up_form">
		<form id="signUpForm" name="signUpForm" action="/processSignUp.php" method="post" enctype="multipart/form-data">
		Please provide your name:
		<br/><input type="text" id="name" name="name" style="width:230px" value="$name">
		<br/>
		<br/>Enter your e-mail addresss:
		<br/><input type="text" id="email" name="email" style="width:230px" value="$email">
		<br/>
		<br/>Confirm your e-mail address:
		<br/><input type="text" id="confirmEmail" name="confirmEmail" style="width:230px" value="$confirmEmail">
		<br/>
		<br/>Choose a Username:
		<br/><input type="text" id="username" name="username" style="width:230px" value="$username">
		<br/>
		<br/>Enter a password:
		<br/><input type="password" id="password" name="password" style="width:230px">
		<br/>
		<br/>Confirm your password:
		<br/><input type="password" id="confirmPassword" name="confirmPassword" style="width:230px">
		<br/>$showCode$agreementCheckbox
		<br/><input type="submit" value="Sign me up!">
		<input type="hidden" id="jb" name="jb" value="$jbHtml">
		$codePass
		</form>
	</div>
EOF;

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/signUp.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/signUp.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("assets/core/layout/signup/layout_signup.php");

$site_container->showSiteContainerBottom();

?>