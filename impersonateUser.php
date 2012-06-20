<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$username = sanitize_string($_REQUEST['username']);

//make sure the user being impersonated is not the master account
$result = mysql_query("SELECT level FROM users WHERE username = '{$username}' LIMIT 1");
$row = mysql_fetch_object($result);

//do not impersonate the master account
if ($row->level == 1) {
	
	exit;
	
}

$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));

//define user session variables
$_SESSION['username'] = $_REQUEST['username'];
$_SESSION['userLevel'] = $row->level;

$_SESSION['isLoggedIn'] = true;
$_SESSION['sysRootPath'] = "$script_directory/cms_users/" . $_REQUEST['username'];
$_SESSION['wwwRootPath'] = "/cms_users/" . $_REQUEST['username'];
$_SESSION['maxDiskSpace'] = 100; // 100 MB max diskspace

//go to the home page
header("location: /");

?>