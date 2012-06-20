<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_group_membership_validator.php");
include("class_config_reader.php");
include("class_process_bbcode.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$parentId = sanitize_string($_REQUEST['parentId']);
$id = sanitize_string($_REQUEST['id']);

if (trim($parentId) == "" || trim($_SESSION['username']) == "") {
	
	exit;
	
}

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	exit;
	
}

//load main group information
$result = mysql_query("SELECT conversations.groupId, conversations.title, conversations.restricted, conversations.locked, groups.name, groups.allowNonMemberPosting FROM conversations INNER JOIN groups ON groups.id = conversations.groupId WHERE conversations.id = '{$parentId}' LIMIT 1");
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

//non-member posting
if ($row->allowNonMemberPosting == 1) {
	
	$nonMemberPosting = true;
	
} else {
	
	$nonMemberPosting = false;
	
}

if ($row->locked == 1) {
	
	$locked = true;
	
} else {
	
	$locked = false;
	
}

//check if this conversation has been restricted, if so, make sure the user is a member of this conversation's group
if ($row->restricted == 1 && (!$groupValidator->isGroupMember && $_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2)) {
	
	exit;
	
}
	
$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i %p') AS newDateCreated FROM conversationsPosts WHERE id = '{$id}'");

if (mysql_num_rows($result) == 0) {
	
	print "<div class=\"post_body\">\n";
	print "There are no posts associated with this conversation.";
	print "</div>\n";
	
} else {
	
	$row = mysql_fetch_object($result);
	
	$bbcode = new ProcessBbcode();
				
	$body = $bbcode->convert($row->body);
	
	print "<div class=\"header\"><div class=\"date\">$row->newDateCreated</div><div class=\"profile\"><a href=\"/showProfile.php?username=$row->author\">$row->author</a></div>";
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['username'] == $row->author || $groupValidator->isGroupAdmin) {
		
		print "<div class=\"options\"><div class=\"edit\"><a href=\"javascript:initEditConversation('$row->id')\">Edit</a></div>";
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->author || $groupValidator->isGroupAdmin) {
	
			print "<div class=\"delete\"><a href=\"javascript:deleteConversation('$row->id')\"  onClick=\"return confirm('Are you sure you want to delete this post?');\">Delete</a></div>";
	
		}
		
		print "</div>";
		
	}
	
	print "</div>";
	print "<div id=\"body_$row->id\" class=\"body_container\">\n";
	print "<div class=\"body\">$body</div>";
	
	if (trim($_SESSION['username']) != "" && ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $groupValidator->isGroupAdmin || ($groupValidator->isGroupMember && !$locked) || (!$groupValidator->isGroupMember && $nonMemberPosting && !$locked))) {
		
		print "<div class=\"reply_button\"><a class=\"button\" href=\"javascript:reply('$row->id');\"><span>Reply</span></a></div>";
		
	}
			
	print "</div>\n";
	
}

?>