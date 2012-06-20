<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$username = sanitize_string($_REQUEST['username']);
$password = sanitize_string($_REQUEST['password']);
$confirmPassword = sanitize_string($_REQUEST['confirmPassword']);
$email = sanitize_string($_REQUEST['email']);
$imageUrlNew = sanitize_string($_REQUEST['imageUrlNew']);
$name = sanitize_string($_REQUEST['name']);
$company = sanitize_string($_REQUEST['company']);
$profession = sanitize_string($_REQUEST['profession']);
$birthMonth = sanitize_string($_REQUEST['birthMonth']);
$birthDay = sanitize_string($_REQUEST['birthDay']);
$birthYear = sanitize_string($_REQUEST['birthYear']);
$race = sanitize_string($_REQUEST['race']);
$gender = sanitize_string($_REQUEST['gender']);
$heightFeet = sanitize_string($_REQUEST['heightFeet']);
$heightInches = sanitize_string($_REQUEST['heightInches']);
$bodyType = sanitize_string($_REQUEST['bodyType']);
$orientation = sanitize_string($_REQUEST['orientation']);
$religion = sanitize_string($_REQUEST['religion']);
$smoke = sanitize_string($_REQUEST['smoke']);
$drink = sanitize_string($_REQUEST['drink']);
$hereFor = sanitize_string($_REQUEST['hereFor']);
$city = sanitize_string($_REQUEST['city']);
$state = sanitize_string($_REQUEST['state']);
$zip = sanitize_string($_REQUEST['zip']);
$country = sanitize_string($_REQUEST['country']);
$profileSummary = sanitize_string($_REQUEST['profileSummary']);
$interests = sanitize_string($_REQUEST['interests']);
$showName = sanitize_string($_REQUEST['showName']);
$showAge = sanitize_string($_REQUEST['showAge']);
$allowEmailNotifications = sanitize_string($_REQUEST['allowEmailNotifications']);
$level = sanitize_string($_REQUEST['level']);

if (trim($username) == "") {$error = 1; $errorMessage .= "- Please provide a username.<br>";}
if (trim($username) !="" && !preg_match("/^[0-9a-z_:.-]+$/i", $username)) {$error = 1; $errorMessage .="- Usernames can only contain letters and numbers or the following: hyphens (\"-\"), underscores (\"_\"), colons (\":\"), or periods (\".\")<br>";}
if (trim($password) == "") {$error = 1; $errorMessage .= "- Please provide a password.<br>";}
if (trim($confirmPassword) == "") {$error = 1; $errorMessage .= "- Please confirm the password.<br>";}
if ((trim($password) != "" && trim($confirmPassword) != "") && ($password != $confirmPassword)) {$error = 1; $errorMessage .= "- Password and confirm password fields do not match.<br>";}
if (trim($name) == "") {$error = 1; $errorMessage .= "- Please provide a name.<br>";}
if (trim($email) == "") {$error = 1; $errorMessage .= "- Please provide an e-mail address.<br>";}
if (trim($level) == "") {$error = 1; $errorMessage .= "- Please provide a user level.<br>";}

$result = mysql_query("SELECT username FROM users WHERE username = '{$username}'");
$row = mysql_fetch_object($result);
$matchRows = mysql_num_rows($result);

if ($matchRows > 0) {$error = 1; $errorMessage .= "- The username provided already exists.<br>";}

if ($error != 1) {
	
	$username = strtolower($username);
	$newPassword = hash('sha256', $password);
	$dateOfBirth = "$birthYear-$birthMonth-$birthDay 00:00";
	$time = date("Y-m-d H:i:s", time());
	
	$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	
	//create this user's file directory
	if(!mkdir("$script_directory/cms_users/$username")) {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<div>SYSTEM ERROR: unable to create personal directory!</div>');";
		print "$('#message_box').show();";
		exit;
		
	}
	
	$result = mysql_query("INSERT INTO users (username, password, imageUrl, name, email, company, profession, city, state, zip, country, dateOfBirth, race, gender, heightFeet, heightInches, bodyType, orientation, religion, smoke, drink, hereFor, profileSummary, interests, showName, showAge, allowEmailNotifications, dateCreated, level, status) VALUES ('{$username}', '{$newPassword}', '{$imageUrlNew}', '{$name}', '{$email}', '{$company}', '{$profession}', '{$city}', '{$state}', '{$zip}', '{$country}', '{$dateOfBirth}', '{$race}', '{$gender}', '{$heightFeet}', '{$heightInches}', '{$bodyType}', '{$orientation}', '{$religion}', '{$smoke}', '{$drink}', '{$hereFor}', '{$profileSummary}', '{$interests}', '{$showName}', '{$showAge}', '{$allowEmailNotifications}', '{$time}','{$level}', 'approved')");
	
	if($result) {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<div>User added successfully.</div>');";
		print "$('#message_box').show();";
		print "$('#add_user')[0].reset();";
		print "regenerateList(0, '', '$username');";
		exit;
		
	} else {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<div>Unknown error! Please try your request again.</div>');";
		print "$('#message_box').show();";
		exit;
		
	}
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
	
}

?>