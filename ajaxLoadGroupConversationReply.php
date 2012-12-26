<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_group_membership_validator.php");
include("class_config_reader.php");
include("class_process_bbcode.php");

$id = sanitize_string($_REQUEST['id']);
$groupId = sanitize_string($_REQUEST['groupId']);

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	exit;
	
}

//load main group information
$result = mysql_query("SELECT conversations.groupId, conversations.title, conversations.restricted, conversations.locked, groups.name, groups.allowNonMemberPosting FROM conversationsPosts INNER JOIN conversations ON conversations.id = conversationsPosts.parentId INNER JOIN groups ON groups.id = conversations.groupId WHERE conversationsPosts.id = '{$id}' LIMIT 1");
if (mysql_num_rows($result) == 0) {
	
	exit;
	
}

$row = mysql_fetch_object($result);
$groupId = $row->groupId;
$groupName = htmlentities($row->name);

//load site group validator
$groupValidator = new GroupValidator();
$groupValidator->isGroupMember($groupId, $_SESSION['username']);
$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);

//check if this conversation has been restricted, if so, make sure the user is a member of this conversation's group
if (($row->restricted == 1 && (!$groupValidator->isGroupMember && $_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3)) || ($row->allowNonMemberPosting == 0 && !$groupValidator->isGroupMember) || ($row->locked && $_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2  && !$groupValidator->isGroupAdmin)) {
	
	exit;
	
}

if (trim($id) != "") {
	
	$result = mysql_query("SELECT author, body FROM conversationsPosts WHERE id = '{$id}' LIMIT 1");
		
		//catch ivalid ids
		if (mysql_num_rows($result) > 0) {
			
			$row = mysql_fetch_object($result);
			
			$escapeBody = preg_replace('/\\\/', '\\\\\\', $row->body);
			$escapeBody = preg_replace("/\\n/", "\\\\n", $escapeBody);
			$escapeBody = preg_replace("/\\r/", "\\\\r", $escapeBody);
			$escapeBody = preg_replace('/\'/', '\\\'', $escapeBody);
			
			header('Content-type: application/javascript');
			print "CKEDITOR.instances.documentBody.setData('\[[quote=\"$row->author\"\]]$escapeBody\[[/quote\]]\\n\\n ');";
			
		}
	
}

?>