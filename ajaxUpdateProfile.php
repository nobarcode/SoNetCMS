<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$password = sanitize_string($_REQUEST['password']);
$confirmPassword = sanitize_string($_REQUEST['confirmPassword']);
$name = sanitize_string($_REQUEST['name']);
$email = sanitize_string($_REQUEST['email']);
$imageUrl = sanitize_string($_REQUEST['imageUrl']);
$company = sanitize_string($_REQUEST['company']);
$profession = sanitize_string($_REQUEST['profession']);
$city = sanitize_string($_REQUEST['city']);
$state = sanitize_string($_REQUEST['state']);
$zip = sanitize_string($_REQUEST['zip']);
$country = sanitize_string($_REQUEST['country']);
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
$hereFor = convertCheckboxes(sanitize_string($_REQUEST['hereFor']));
$profileSummary = sanitize_string($_REQUEST['profileSummary']);
$interests = sanitize_string($_REQUEST['interests']);
$showName = sanitize_string($_REQUEST['showName']);
$showAge = sanitize_string($_REQUEST['showAge']);
$allowEmailNotifications = sanitize_string($_REQUEST['allowEmailNotifications']);
$commentsFromFriendsOnly = sanitize_string($_REQUEST['commentsFromFriendsOnly']);

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

if (trim($password) != "") {
	
	if (trim($confirmPassword) == "") {$error = 1; $errorMessage .= "- Please confirm the password.<br>";}
	if (trim($confirmPassword) != "" && $password != $confirmPassword) {$error = 1; $errorMessage .= "- Password and confirm password fields do not match.<br>";}
	
}

if (trim($name) == "") {$error = 1; $errorMessage .= "- Please provide a name.<br>";}
if (trim($email) == "") {$error = 1; $errorMessage .= "- Please provide an e-mail address.<br>";}

if ($error != 1) {
	
	$dateOfBirth = "$birthYear-$birthMonth-$birthDay";
	if (trim($showName) == "") {$showName = 0;}
	if (trim($showAge) == "") {$showAge = 0;}
	if (trim($allowEmailNotifications) == "") {$allowEmailNotifications = 0;}
	
	mysql_query("UPDATE users SET email = '{$email}', imageUrl = '{$imageUrl}', name = '{$name}', company = '{$company}', profession = '{$profession}', dateOfBirth = '{$dateOfBirth}', race = '{$race}', gender = '{$gender}', heightFeet = '{$heightFeet}', heightInches = '{$heightInches}', bodyType = '{$bodyType}', orientation = '{$orientation}', religion = '{$religion}', smoke = '{$smoke}', drink = '{$drink}', hereFor = '{$hereFor}', city = '{$city}', state = '{$state}', zip = '{$zip}', country = '{$country}', profileSummary = '{$profileSummary}', interests = '{$interests}', showName = '{$showName}', showAge = '{$showAge}', allowEmailNotifications = '{$allowEmailNotifications}', commentsFromFriendsOnly = '{$commentsFromFriendsOnly}' WHERE username = '{$_SESSION['username']}'");
	
	if (trim($password) != "") {
		
		$newPassword = hash('sha256', $password);
		mysql_query("UPDATE users SET password = '{$newPassword}' WHERE username = '{$_SESSION['username']}'");
		
	}
	
	$showMessage = "Profile information updated successfully.";
	
	if (trim($imageUrl) != "") {
		
		$imageUrl = urlencode(unsanitize_string($imageUrl));
		$updateProfileImage = "<a href=\"showUserGallery.php?username=" . $_SESSION['username'] . "\"><img src=\"/file.php?load=$imageUrl&w=270\" border=\"0\"></a>";
			
	} else {
		
		$updateProfileImage = "<div class=\"profile_image_note\">To add an image to your profile, click the <i>Account</i> tab and upload an image (or choose from an existing image, if you\'ve already uploaded images) by clicking <i>Browse</i> at the end of the <i>Image</i> field.</div>";
		
	}
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('$showMessage');";
	print "$('#message_box').show();";
	print "$('#profile_image').html('$updateProfileImage');";
	
} else {
	
	$showMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('$showMessage');";
	print "$('#message_box').show();";
	exit;
	
}

function convertCheckboxes($data) {
	
	for ($x = 0; $x < count($data); $x++) {
		
		$return .= "<" . $data[$x] . ">";
		
	}
	
	return($return);
	
}

?>