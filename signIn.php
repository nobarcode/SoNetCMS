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
$loginSubmit = sanitize_string($_REQUEST['loginSubmit']);
$jb = sanitize_string($_REQUEST['jb']);
$return_url = sanitize_string($_REQUEST['return_url']);
$sr = sanitize_string($_REQUEST['sr']);

$testUsername = strtoupper($username);

//check if any users are in the database, if not create a user using this (the submitted) login information.
$result = mysql_query("SELECT * FROM users");
$count = mysql_num_rows($result);

$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));

//if there are no users in the user table yet, add this one as an admin
if ($count == 0 && (trim($username) != "" && trim($password) != "")) {
	
	$newPassword = hash('sha256', $password);
	$time = date("Y-m-d H:i:s", time());
	$lastIpAddress = $_SERVER['REMOTE_ADDR'];
	
	mysql_query("INSERT INTO users (username, password, dateCreated, level, status, lastLogin, lastIpAddress) VALUES ('{$username}', '{$newPassword}', '{$time}', 1, 'approved', '{$time}', '{$lastIpAddress}')");
	
	$_SESSION['username'] = $username;
	$_SESSION['userLevel'] = '1';
		
	//create this user's file directory
	mkdir("$script_directory/cms_users/$username") or die("SYSTEM ERROR: unable to create personal directory!");
	
	//user is admin now, so allow filemanager access
	$_SESSION['isLoggedIn'] = true;
	$_SESSION['sysRootPath'] = "$script_directory/assets";
	$_SESSION['wwwRootPath'] = "/assets";
	$_SESSION['maxDiskSpace'] = false; // no max diskspace
	
	header("location: controlPanel.php");
	
	exit;
	
}

if (trim($loginSubmit) != "") {
	
	//load the master config file
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/config.properties');
	
	//test the username and password combination
	$testPassword = hash('sha256', $password);
	$result = mysql_query("SELECT username, level, status FROM users WHERE UPPER(username) = '{$testUsername}' AND password = '{$testPassword}'");
	$row = mysql_fetch_object($result);
	$matchRows = mysql_num_rows($result);
	
	if ($matchRows < 1) {$error = 1; $errorMessage .= "<br>- Please verify your username and password.";}
	if ($row->status == 'pending' && $config->readValue('requireSignUpApproval') == 'true') {$error = 1; $errorMessage .= "<br>- Your account is pending approval. Please check back later, or contact us for more information.";}
	
	if ($error != 1) {
		
		//record time and IP then log the user in, then redirect
		$time = date("Y-m-d H:i:s", time());
		$lastIpAddress = $_SERVER['REMOTE_ADDR'];
		
		mysql_query("UPDATE users SET lastLogin = '{$time}', lastIpAddress = '{$lastIpAddress}' WHERE username = '{$username}'");
		
		//set the user's session variables
		$_SESSION['username'] = $row->username;
		$_SESSION['userLevel'] = $row->level;
		
		if ($row->level == 1 || $row->level == 2 || $row->level == 3 || $row->level == 4) {
			
			$_SESSION['isLoggedIn'] = true;
			$_SESSION['sysRootPath'] = "$script_directory/assets";
			$_SESSION['wwwRootPath'] = "/assets";
			$_SESSION['maxDiskSpace'] = false; // no max diskspace
			
		} else {
			
			$_SESSION['isLoggedIn'] = true;
			$_SESSION['sysRootPath'] = "$script_directory/cms_users/$row->username";
			$_SESSION['wwwRootPath'] = "/cms_users/$row->username";
			$_SESSION['maxDiskSpace'] = 100; // 100 MB max diskspace
			
		}
		
		//jumpback to wherever they came from ($jb is defined in part_session.php)
		if (trim($jb) != "") {
			
			header("location: $jb");
			exit;
		
		//check if image manager/file manager passed its return url variable	
		} elseif (trim($return_url) != "") {
			
			header("location: $return_url");
			exit;
		
		//if there's no return variables available, just go to the home page
		} else {
			
			header("location: ./");
			exit;
			
		}

	}
	
}

if ($error == 1) {
	
	$message = "<div id=\"message_box\" onClick=\"$('#message_box').hide();\"><b>There was an error processing your request, please check the following:</b>$errorMessage</div>";
	$username = unsanitize_string($username);
	$username = htmlentities($username);
	
}

$jb = unsanitize_string($jb);
$jbHtml = htmlentities($jb);
$jb = urlencode($jb);
$return_url = htmlentities($return_url);

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/signIn.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/signIn.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("assets/core/layout/signin/layout_signin.php");

$site_container->showSiteContainerBottom();

?>