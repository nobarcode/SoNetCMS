<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);

//read config file and determine if view group authentication is true, if it is and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	exit;
	
}

$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND memberLevel = '1' AND status = 'approved'");
$row = mysql_fetch_object($result);

print $row->username;

?>