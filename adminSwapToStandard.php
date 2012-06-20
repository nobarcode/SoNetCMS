<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));

//define user session variables
$_SESSION['userLevel'] = 0;
$_SESSION['isLoggedIn'] = true;
$_SESSION['sysRootPath'] = "$script_directory/cms_users/" . $_SESSION['username'];
$_SESSION['wwwRootPath'] = "/cms_users/" . $_SESSION['username'];
$_SESSION['maxDiskSpace'] = 100; // 100 MB max diskspace

//go to the last page the user was on
header("location: " . $_REQUEST['jb']);

?>