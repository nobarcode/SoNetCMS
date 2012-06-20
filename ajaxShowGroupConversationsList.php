<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_group_membership_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$dateOrder = sanitize_string($_REQUEST['dateOrder']);
$titleOrder = sanitize_string($_REQUEST['titleOrder']);
$authorOrder = sanitize_string($_REQUEST['authorOrder']);
$postsOrder = sanitize_string($_REQUEST['postsOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

if (trim($groupId) == "") {
	
	exit;
	
}

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	exit;
	
}

changeDirection($groupId, $s, $d, $dateOrder, $titleOrder, $authorOrder, $postsOrder, $orderBy, $change);

function changeDirection($groupId, $s, $d, $dateOrder, $titleOrder, $authorOrder, $postsOrder, $orderBy, $change) {
	
	//load site group validator
	$groupValidator = new GroupValidator();
	$groupValidator->isGroupMember($groupId, $_SESSION['username']);
	$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);
	
	$max_per_page = 25;
	
	if (trim($s) == "") {
		
		$s = 0;
		
	}
	
	if ($orderBy == $change) {
		
		if ($change == "date") {if ($dateOrder == "desc") {$dateOrder = "asc";} else {$dateOrder = "desc";} $changeDate = "date";}
		if ($change == "title") {if ($titleOrder == "desc") {$titleOrder = "asc";} else {$titleOrder = "desc";} $changeTitle = "title";}
		if ($change == "author") {if ($authorOrder == "desc") {$authorOrder = "asc";} else {$authorOrder = "desc";} $changeAuthor = "status";}
		if ($change == "posts") {if ($postsOrder == "desc") {$postsOrder = "asc";} else {$postsOrder = "desc";} $changePosts = "posts";}
		
	}
	
	if ($orderBy == "date") {$orderBySQL = "dateCreated"; $directionSQL = strtoupper($dateOrder); $changeDate = "date";}
	if ($orderBy == "title") {$orderBySQL = "title"; $directionSQL = strtoupper($titleOrder); $changeTitle = "title";}
	if ($orderBy == "author") {$orderBySQL = "author"; $directionSQL = strtoupper($authorOrder); $changeAuthor = "author";}
	if ($orderBy == "posts") {$orderBySQL = "totalPosts"; $directionSQL = strtoupper($postsOrder); $changePosts = "posts";}
	
	$result = mysql_query("SELECT id, restricted, locked FROM conversations WHERE groupId = '{$groupId}'");
	
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

	$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i %p') AS newDateCreated, (SELECT COUNT(conversationsPosts.id) AS postCount FROM conversationsPosts WHERE conversationsPosts.parentId = conversations.id) AS totalPosts, (SELECT DATE_FORMAT(dateCreated, '%m/%d/%Y') AS newDateCreated FROM conversationsPosts WHERE conversationsPosts.parentId = conversations.id ORDER BY dateCreated DESC LIMIT 1) AS latestPost FROM conversations WHERE groupId = '{$groupId}' ORDER BY sticky DESC, $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($groupId, $s, '', $dateOrder, $titleOrder, $authorOrder, $postsOrder, $orderBy, $change);
		
	} else {
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $groupValidator->isGroupAdmin) {print "<form id=\"deleteMultipleConversations\" method=\"post\" enctype=\"multipart/form-data\" action=\"ajaxDeleteMultipleConversations.php\">";}
		print "<div class=\"conversation_main_header_options\">";
		print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		print "<tr valign=\"center\">";
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $groupValidator->isGroupAdmin) {print "<td><div class=\"conversation_header_checkbox\"></div>";}
		print "<td><div class=\"conversation_header_date\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', 'date', '$changeDate');\">Date</a></div></td>";
		print "<td><div class=\"conversation_header_icon\"></div></td>";
		print "<td><div class=\"conversation_header_title\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', 'title', '$changeTitle');\">Title</a></div></td>";
		print "<td><div class=\"conversation_header_author\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', 'author', '$changeAuthor');\">Author</a></div></td>";
		print "<td><div class=\"conversation_header_posts\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', 'posts', '$changePosts');\">Posts</a></div></td>";
		print "</tr>";
		print "</table>";
		print "</div>";
		
		while ($row = mysql_fetch_object($result)) {
			
			$title = htmlentities($row->title);
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $groupValidator->isGroupAdmin) {
				
				if ($row->sticky != 1) {

					if (date('m/d/Y') == $row->latestPost) {

						$showIcon = "<a href=\"javascript:toggleStickyState('$row->id', '$s', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', '$orderBy');\"><img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/group_conversation_icon_new.gif\" border=\"0\" title=\"New Posts Today\"></a>";

					} else {

						$showIcon = "<a href=\"javascript:toggleStickyState('$row->id', '$s', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', '$orderBy');\"><img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/group_conversation_icon.gif\" border=\"0\" title=\"No Posts Today\"></a>";

					}

				} else {

					$showIcon = "<a href=\"javascript:toggleStickyState('$row->id', '$s', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', '$orderBy');\"><img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/group_conversation_icon_sticky.gif\" border=\"0\" title=\"Sticky Post\"></a>";

				}
				
			} else {
				
				if ($row->sticky != 1) {

					if (date('m/d/Y') == $row->latestPost) {

						$showIcon = "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/group_conversation_icon_new.gif\" border=\"0\" title=\"New Posts Today\">";

					} else {

						$showIcon = "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/group_conversation_icon.gif\" border=\"0\" title=\"No Posts Today\">";

					}

				} else {

					$showIcon = "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/group_conversation_icon_sticky.gif\" border=\"0\" title=\"Sticky Post\">";

				}
				
			}
			
			if ($row->locked == 1) {
				
				$showTitle = "<div class=\"locked\" title=\"Topic is Locked\"><a href=\"showGroupConversation.php?parentId=$row->id\">$title</a></div>";
				
			} else {
				
				$showTitle = "<a href=\"showGroupConversation.php?parentId=$row->id\">$title</a>";
				
			}
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $row->restricted != 1 || ($row->restricted == 1 && $groupValidator->isGroupMember)) {
				
				print "<div id=\"conversation_$row->id\" class=\"conversation_container\">";
				print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
				print "<tr valign=\"center\">";
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $groupValidator->isGroupAdmin) {print "<td><div class=\"conversation_header_checkbox\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"deleteId[]\" name=\"deleteId[]\" value=\"$row->id\"></div></td>";}
				print "<td><div class=\"conversation_header_date\">$row->newDateCreated</div></td>";
				print "<td><div class=\"conversation_header_icon\">$showIcon</div></td>";
				print "<td><div class=\"conversation_header_title\">$showTitle</div></td>";
				print "<td><div class=\"conversation_header_author\"><a href=\"/showProfile.php?username=$row->author\">$row->author</a></div></td>";
				print "<td><div class=\"conversation_header_posts\">$row->totalPosts</div></td>";
				print "</tr>";
				print "</table>";
				print "</div>";
				
			}
			
		}
		
		//if the total rows that are not restriced is 0
		if ($totalRows == 0) {
			
			print "<div class=\"conversation_container\">";
			print "There are no conversations associated with this group.";
			print "</div>";
			
		}
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $groupValidator->isGroupAdmin) {
			
			print "<div id=\"conversation_list_options\">";
			print "<div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#deleteMultipleConversations :checkbox').attr('checked', this.checked);\"></div><div class=\"select_all\">Select All</div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleConversations('$s', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete the selected conversations?');\">Delete Selected</a></div>";
			print "</div>";
			
		}
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Conversations</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n', '$dateOrder', '$titleOrder', '$authorOrder', '$postsOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $groupValidator->isGroupAdmin) {
			
			print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
			print "<input type=\"hidden\" id=\"dateOrder\" name=\"dateOrder\" value=\"$dateOrder\">";
			print "<input type=\"hidden\" id=\"titleOrder\" name=\"titleOrder\" value=\"$titleOrder\">";
			print "<input type=\"hidden\" id=\"authorOrder\" name=\"authorOrder\" value=\"$authorOrder\">";
			print "<input type=\"hidden\" id=\"orderBy\" name=\"orderBy\" value=\"$orderBy\">";
			print "</form>";
			
		}		
		
	}
	
}

?>