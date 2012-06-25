<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$oldUsername = sanitize_string($_REQUEST['oldUsername']);
$username = sanitize_string($_REQUEST['username']);
$password = sanitize_string($_REQUEST['password']);
$confirmPassword = sanitize_string($_REQUEST['confirmPassword']);
$email = sanitize_string($_REQUEST['email']);
$imageUrlEdit = sanitize_string($_REQUEST['imageUrlEdit']);
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
$hereFor = convertCheckboxes(sanitize_string($_REQUEST['hereFor']));
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

if (trim($oldUsername) == "") {exit;}

if (trim($username) == "") {$error = 1; $errorMessage .= "- Please provide a username.<br>";}
if (trim($username) !="" && !preg_match("/^[0-9a-z_:.-]+$/i", $username)) {$error = 1; $errorMessage .="- Usernames can only contain letters and numbers or the following: hyphens (\"-\"), underscores (\"_\"), colons (\":\"), or periods (\".\")<br>";}

//if a new username is supplied, check if the new username already exists
if ($username != $oldUsername) {
	
	$result = mysql_query("SELECT username FROM users WHERE username = '{$username}'"); 
	
	if (mysql_num_rows($result) > 0) {
		
		$error = 1; $errorMessage .= "- The supplied username already exists.<br>";
		
	}
	
}

if (trim($password) != "") {
	
	if (trim($password) == "") {$error = 1; $errorMessage .= "- Please provide a password.<br>";}
	if (trim($confirmPassword) == "") {$error = 1; $errorMessage .= "- Please confirm the password.<br>";}
	if ((trim($password) != "" && trim($confirmPassword) != "") && ($password != $confirmPassword)) {$error = 1; $errorMessage .= "- Password and confirm password fields do not match.<br>";}
	
}

if (trim($name) == "") {$error = 1; $errorMessage .= "- Please provide a name.<br>";}
if (trim($email) == "") {$error = 1; $errorMessage .= "- Please provide an e-mail address.<br>";}
if (trim($level) == "") {$error = 1; $errorMessage .= "- Please provide a user level.<br>";}

//check is user being edited is the master account, if it is - make sure the user performing the edit is the master account
$result = mysql_query("SELECT level FROM users WHERE username = '{$oldUsername}'"); 
$row = mysql_fetch_object($result);

if ($row->level == 1 && $_SESSION['userLevel'] != 1) {
	
	$error = 1; $errorMessage = "- You are not authorized to edit this account.<br>";
	
}

//check is user being edited is trying to be set as a master account, if it is and there is already a master account, deny the request -- unless it's the master account that attempting to set it
$result = mysql_query("SELECT username FROM users WHERE level = 1"); 
$row = mysql_fetch_object($result);

if ($level == 1 && ($oldUsername != $row->username && $_SESSION['userLevel'] != 1)) {
	
	$error = 1; $errorMessage = "- A master account already exists.<br>";
	
}

if ($error != 1) {
	
	$dateOfBirth = "$birthYear-$birthMonth-$birthDay 00:00";
	if (trim($showName) == "") {$showName = 0;}
	if (trim($showAge) == "") {$showAge = 0;}
	if (trim($allowEmailNotifications) == "") {$allowEmailNotifications = 0;}
	
	mysql_query("UPDATE users SET username = '{$username}', email = '{$email}', imageUrl = '{$imageUrlEdit}', name = '{$name}', company = '{$company}', profession = '{$profession}', dateOfBirth = '{$dateOfBirth}', race = '{$race}', gender = '{$gender}', heightFeet = '{$heightFeet}', heightInches = '{$heightInches}', bodyType = '{$bodyType}', orientation = '{$orientation}', religion = '{$religion}', smoke = '{$smoke}', drink = '{$drink}', hereFor = '{$hereFor}', city = '{$city}', state = '{$state}', zip = '{$zip}', country = '{$country}', profileSummary = '{$profileSummary}', interests = '{$interests}', showName = '{$showName}', showAge = '{$showAge}', allowEmailNotifications = '{$allowEmailNotifications}', level = '{$level}' WHERE username = '{$oldUsername}'");
	
	//update all tables that cotain references to usernames if the user's username has changed
	if ($oldUsername != $username) {
		
		mysql_query("UPDATE announcements SET usernameCreated = '{$username}' WHERE usernameCreated = '{$oldUsername}'");
		mysql_query("UPDATE announcements SET usernameUpdated = '{$username}' WHERE usernameUpdated = '{$oldUsername}'");
		mysql_query("UPDATE blogs SET username = '{$username}' WHERE username = '{$oldUsername}'");
		mysql_query("UPDATE commentsDocuments SET username = '{$username}' WHERE username = '{$oldUsername}'");
		mysql_query("UPDATE commentsImages SET username = '{$username}' WHERE username = '{$oldUsername}'");
		mysql_query("UPDATE commentsImages SET parentId = '{$username}' WHERE parentId = '{$oldUsername}'");
		mysql_query("UPDATE commentsUserProfiles SET username = '{$username}' WHERE username = '{$oldUsername}'");
		mysql_query("UPDATE commentsUserProfiles SET parentId = '{$username}' WHERE parentId = '{$oldUsername}'");
		mysql_query("UPDATE documents SET usernameCreated = '{$username}' WHERE usernameCreated = '{$oldUsername}'");
		mysql_query("UPDATE documents SET usernameUpdated = '{$username}' WHERE usernameUpdated = '{$oldUsername}'");
		mysql_query("UPDATE documentVersioning SET usernameCreated = '{$username}' WHERE usernameCreated = '{$oldUsername}'");
		mysql_query("UPDATE events SET usernameCreated = '{$username}' WHERE usernameCreated = '{$oldUsername}'");
		mysql_query("UPDATE events SET usernameUpdated = '{$username}' WHERE usernameUpdated = '{$oldUsername}'");
		mysql_query("UPDATE friends SET owner = '{$username}' WHERE owner = '{$oldUsername}'");
		mysql_query("UPDATE friends SET friend = '{$username}' WHERE friend = '{$oldUsername}'");
		mysql_query("UPDATE groupsMembers SET username = '{$username}' WHERE username = '{$oldUsername}'");
		mysql_query("UPDATE imagesUsers SET username = '{$username}' WHERE username = '{$oldUsername}'");
		mysql_query("UPDATE messages SET toUser = '{$username}' WHERE toUser = '{$oldUsername}'");
		mysql_query("UPDATE messages SET fromUser = '{$username}' WHERE fromUser = '{$oldUsername}'");
		mysql_query("UPDATE fileManager SET owner = '{$username}' WHERE owner = '{$oldUsername}'");
		
		//rename the user's image directory and update their image gallery and blogs with the new path
		$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
		rename("$script_directory/cms_users/$oldUsername", "$script_directory/cms_users/$username");
		
		//update filemanager database links
		$oldFsPath = "$script_directory/cms_users/$oldUsername/";
		
		$result = mysql_query("SELECT wwwPath, fsPath FROM fileManager WHERE fsPath LIKE BINARY '{$oldFsPath}%'");
		
		while ($row = mysql_fetch_object($result)) {
			
			$updatedWwwPath = str_replace("/cms_users/$oldUsername/", "/cms_users/$username/", $row->wwwPath);
			$updatedFsPath = str_replace("/cms_users/$oldUsername/", "/cms_users/$username/", $row->fsPath);
			
			mysql_query("UPDATE fileManager SET wwwPath = '{$updatedWwwPath}', fsPath = '{$updatedFsPath}' WHERE fsPath = '{$row->fsPath}'");
			
		}
		
		//update user's profile photo url
		$result = mysql_query("SELECT imageUrl FROM users WHERE username = '{$username}'");
		$row = mysql_fetch_object($result);
		$updatedPath = str_replace("/cms_users/$oldUsername/", "/cms_users/$username/", $row->imageUrl);
		mysql_query("UPDATE users SET imageUrl = '{$updatedPath}' WHERE username = '{$username}'");
				
		//update user's image body text with the new path
		$result = mysql_query("SELECT id, body, imageUrl FROM imagesUsers WHERE username = '{$username}'");
		
		while($row = mysql_fetch_object($result)) {
			
			$newImageURL = str_replace("/cms_users/$oldUsername/", "/cms_users/$username/", $row->imageUrl);
			$newBody = str_replace("/cms_users/$oldUsername/", "/cms_users/$username/", $row->body);
			
			mysql_query("UPDATE imagesUsers SET body = '{$newBody}', imageUrl = '{$newImageURL}' WHERE id = '{$row->id}'");
			
		}
		
		//update user's blog body text with the new path
		$result = mysql_query("SELECT id, body FROM blogs WHERE author = '{$username}'");
		
		while($row = mysql_fetch_object($result)) {
			
			$newBody = str_replace("/cms_users/$oldUsername/", "/cms_users/$username/", $row->body);
			mysql_query("UPDATE blogs SET body = '{$newBody}' WHERE id = '{$row->id}'");
			
		}
		
		
	}
	
	if (trim($password) != "") {
		
		$newPassword = hash('sha256', $password);
		mysql_query("UPDATE users SET password = '{$newPassword}' WHERE username = '{$oldUsername}'");
		
	}
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

header('Content-type: application/javascript');
print <<< EOF
$('#message_box').html('<div>User information updated successfully.</div>');
$('#message_box').show();
EOF;

function convertCheckboxes($data) {
	
	for ($x = 0; $x < count($data); $x++) {
		
		$return .= "<" . $data[$x] . ">";
		
	}
	
	return($return);
	
}

?>