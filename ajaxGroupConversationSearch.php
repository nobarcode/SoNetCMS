<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_word_limiter.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_group_membership_validator.php");
include("class_config_reader.php");
include("class_process_bbcode.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$search = sanitize_string($_REQUEST['search']);
$groupId = sanitize_string($_REQUEST['groupId']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$orderDirection = sanitize_string($_REQUEST['orderDirection']);

changeDirection($s, $d, $search, $groupId, $orderBy, $orderDirection);

function changeDirection($s, $d, $search, $groupId, $orderBy, $orderDirection) {
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	//order type
	if ($orderBy == "relevance") {
		
		$queryOrder = " ORDER BY relevance";
	
	} elseif ($orderBy == "author") {
		
		$queryOrder = " ORDER BY author";
		
	} elseif ($orderBy == "date") {
		
		$queryOrder = " ORDER BY dateCreated";
		
	} else {
		
		$queryOrder = " ORDER BY relevance";
		
	}

	//order direction
	if ($orderDirection == "desc") {
		
		$queryOrder .= " DESC";
		
	} elseif ($orderDirection == "asc") {
		
		$queryOrder .= " ASC";
		
	} else {
		
		$queryOrder .= " DESC";
		
	}			
	
	//load site group validator
	$groupValidator = new GroupValidator();
	$groupValidator->isGroupMember($groupId, $_SESSION['username']);
	$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);
	
	//check if user is a member of the group this conversation is assigned to
	
	$result = mysql_query("SELECT conversationsPosts.id, conversations.restricted FROM conversationsPosts INNER JOIN conversations ON conversations.id = conversationsPosts.parentId AND conversations.groupId = '{$groupId}' WHERE MATCH (conversationsPosts.body) AGAINST ('{$search}')");
	
	$totalRows = 0;
	
	//count the total number of conversation posts that are not in a restricted conversation
	while ($row = mysql_fetch_object($result)) {
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $row->restricted != 1 || ($row->restricted == 1 && $groupValidator->isGroupMember)) {
			
			$totalRows++;
			
		}
		
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

	$result = mysql_query("SELECT conversations.title, conversations.restricted, conversationsPosts.parentId, conversationsPosts.id, conversationsPosts.dateCreated, conversationsPosts.author, DATE_FORMAT(conversationsPosts.dateCreated, '%M %d, %Y %h:%i %p') AS showDate, body, MATCH (conversationsPosts.body) AGAINST ('{$search}') AS relevance FROM conversationsPosts INNER JOIN conversations ON conversations.id = conversationsPosts.parentId AND conversations.groupId = '{$groupId}' WHERE MATCH (conversationsPosts.body) AGAINST ('{$search}')$queryOrder LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {

		changeDirection($s, 'b', $search, $groupId, $orderBy, $orderDirection);

	} else {
		
		if ($count > 0) {
			
			$x = 0;
			
			$bbcode = new ProcessBbcode();
			
			while ($row = mysql_fetch_object($result)) {
					
				$x++;
				
				if ($x < $count) {
					
					$style = " post_row_separator";
					
				} else {
					
					$style = "";
					
				}
				
				$title = htmlentities($row->title);
				$showAuthor = "$row->author";
				$showDate = "$row->showDate";
				
				$body = $bbcode->convert($row->body);
				
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $row->restricted != 1 || ($row->restricted == 1 && $groupValidator->isGroupMember)) {
					
					print "<div class=\"post_container$style\">";
					print "	<div class=\"post_info_container\">";
					print "		<div class=\"post_title\"><a href=\"showGroupConversation.php?parentId=$row->parentId&findId=$row->id\">$title</a></div><div class=\"post_date\">$showDate</div><div class=\"post_author\">$showAuthor</div>";
					print "	</div>";
					print "	<div class=\"post_body\">$body</div>";
					print "</div>";
					
				}
				
			}
			
		} else {
			
			print "<div class=\"post_container\">";
			print "	No posts found. Please refine your search and try again.";
			print "</div>";
			
		}
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Posts Found</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
	}
	
}

?>