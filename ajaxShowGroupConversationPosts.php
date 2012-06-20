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
$findId = sanitize_string($_REQUEST['findId']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);

if (trim($parentId) == "") {
	
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
$conversationTitle = htmlentities($row->title);
$groupName = htmlentities($row->name);

//load site group validator
$groupValidator = new GroupValidator();
$groupValidator->isGroupMember($groupId, $_SESSION['username']);
$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);

//check if this conversation has been restricted, if so, make sure the user is a memeber of this conversation's group
if ($row->restricted == 1 && (!$groupValidator->isGroupMember && $_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3)) {
	
	exit;
	
}

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

changeDirection($groupId, $parentId, $findId, $s, $d, $nonMemberPosting, $locked, $groupValidator);

function changeDirection($groupId, $parentId, $findId, $s, $d, $nonMemberPosting, $locked, $groupValidator) {
	
	$max_per_page = 25;
	
	$result = mysql_query("SELECT id FROM conversationsPosts WHERE parentId = '{$parentId}'");
	$totalRows = mysql_num_rows($result);
	
	if (trim($findId) != "" && trim($s) == "") {
		
		$findRow = 0;
		
		while ($row = mysql_fetch_object($result)) {
			
			if ($row->id == $findId) {
				
				if ($findRow > $max_per_page) {
					
					$s = floor($findRow / $max_per_page) * $max_per_page;
					
				} else {
					
					$s = 0;
					
				}
				
				break;
				
			} else {
				
				$findRow++;
				
			}
			
		}
		
	}
	
	if (is_string($s) && ($s == 'last' || $s == "last_post")) {
		
		if ($totalRows > $max_per_page) {
			
			$s = floor($totalRows / $max_per_page) * $max_per_page;
			
		} else {
			
			$s = 0;
			
		}
		
		$d = '';
		
	} elseif (trim($s) == "" || $s == 'first') {

		$s = 0;

	}
	
	$showTotalPages = ceil($totalRows / $max_per_page);

	if ($d == "b") {

		$s -= $max_per_page;

		if ($s < 0) {

			$s = 0;

		}

	}

	if ($d == "n") {

		if ($s + $max_per_page < $totalRows) {

			$s += $max_per_page;

		}

	}

	if ($totalRows > 0) {

		$showCurrentPage = floor($s / $max_per_page) + 1;

	} else {

		$showCurrentPage = 0;

	}

	$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i %p') AS newDateCreated FROM conversationsPosts WHERE parentId = '{$parentId}' ORDER BY dateCreated ASC LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($groupId, $parentId, $findId, $s, '', $nonMemberPosting, $locked, $groupValidator);
		
	} else {
		
		$bbcode = new ProcessBbcode();
		
		while ($row = mysql_fetch_object($result)) {
			
			$body = $bbcode->convert($row->body);
						
			print "<div id=\"post_container_$row->id\" class=\"post_container\">";
			print "<div class=\"header\"><div class=\"date\">$row->newDateCreated</div><div class=\"profile\"><a href=\"/showProfile.php?username=$row->author\">$row->author</a></div>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $groupValidator->isGroupAdmin || ($_SESSION['username'] == $row->author && !$locked)) {
				
				print "<div class=\"options\"><div class=\"edit\"><a href=\"javascript:initEditConversation('$row->id')\">Edit</a></div>";
				
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $groupValidator->isGroupAdmin || ($_SESSION['username'] == $row->author && !$locked)) {

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
			print "</div>";
			
		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<div class=\"post_container\">";
			print "<div class=\"post_body\">\n";
			print "There are no posts associated with this conversation.";
			print "</div>\n";
			print "</div>";
			
		}
		
		print "<div id=\"post_navigation\">";
		print "	<div class=\"totals\">$totalRows Posts</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"first\"><a href=\"javascript:regenerateList('first', '');\" title=\"First Results\">First</a></div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n');\" title=\"Next Results\">Next</a></div><div class=\"last\"><a href=\"javascript:regenerateList('last', '');\" title=\"Last Results\">Last</a></div></div>";
		print "</div>";
		
		//assign last_s the current value of page start value
		print "<script>last_s = '$s';</script>";
		
	}
	
}

?>