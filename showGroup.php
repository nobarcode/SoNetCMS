<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");
include("class_process_bbcode.php");

$groupId = sanitize_string($_REQUEST['groupId']);

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, jump to login page
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	include("part_session_check.php");
	
}

//validate group
$result = mysql_query("SELECT id FROM groups WHERE id = '{$groupId}'");

if (mysql_num_rows($result) == 0) {

	$_SESSION['status'] = array('not found', $groupId, '');
	header("Location: /status.php?type=group");
	exit;

}

function showGroupInfo($groupId, $jb, $w, $h) {
	
	$result = mysql_query("SELECT id FROM groups WHERE id = '{$groupId}' LIMIT 1");
	
	//setup the comment button to either show the comment text area or prompt for login
	if (trim($_SESSION['username']) == "") {
		
		$commentButton = "<a class=\"button\" href=\"/signIn.php?jb=$jb&sr=1\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
		
	} else {
		
		$commentButton = "<a class=\"button\" href=\"javascript:showAddComment();\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
		
	}
	
	//load document information
	$result = mysql_query("SELECT groups.id, groups.name, groups.about, groups.summaryImage, groups.summary, DATE_FORMAT(groups.dateCreated, '%M %d, %Y') AS newDateCreated, groupsMembers.username, (SELECT COUNT(parentId) FROM groupsMembers WHERE groupsMembers.parentId = groups.id AND groupsMembers.status = 'approved') AS totalMembers FROM groups LEFT JOIN groupsMembers ON groups.id = groupsMembers.parentId AND groupsMembers.memberLevel = '1' WHERE groups.id = '{$groupId}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	$showName = htmlentities($row->name);
	$owner = $row->username;
	$showDate = $row->newDateCreated;
	$totalMembers = $row->totalMembers;
	
	if (trim($row->summaryImage) != "") {
		
		$summaryImage = "<div id=\"summary_image\"><img src=\"/file.php?load=$row->summaryImage&w=$w&h=$h\" border=\"0\"></div>";
		
	}
	
	$summary = htmlentities($row->summary);
	$summary = preg_replace("/\\n/", "<br>", $summary);
	
	//check if the user is logged in and change the join group link accordingly
	if (trim($_SESSION['username']) == "") {

		$showJoinGroup = "<div class=\"join\"><a href=\"/signIn.php?jb=$jb&sr=1\">Join Group</a></div><div class=\"leave\"><a href=\"/signIn.php?jb=$jb&sr=1\">Leave Group</a></div>\n";

	} else {

		$showJoinGroup = "<div class=\"join\"><a href=\"javascript:joinGroup();\">Join Group</a></div><div class=\"leave\"><a href=\"javascript:leaveGroup();\" onClick=\"return confirm('Are you sure you want to leave this group?');\">Leave Group</a></div>\n";

	}
	
	$return .= "<div id=\"group_info_container\">";
	$return .= "	<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$return .= "		<tr valign=\"top\"><td>$summaryImage</td>";
	$return .= "		<td>";
	$return .= "			<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$return .= "				<tr valign=\"top\"><td colspan=\"2\"><div class=\"group_name\">$showName</div></td></tr>";
	$return .= "				<tr valign=\"top\"><td class=\"stats_header\">Established</td><td class=\"stats_data\">$showDate</td></tr>";
	$return .= "				<tr valign=\"top\"><td class=\"stats_header\">Owner</td><td class=\"stats_data\"><span id=\"owner_container\">$owner</span></td></tr>";
	$return .= "				<tr valign=\"top\"><td class=\"stats_header\">Members</td><td class=\"stats_data\"><span id=\"total_members_container\">$totalMembers</span></td></tr>";
	$return .= "				<tr valign=\"top\"><td class=\"stats_header\">About</td><td class=\"stats_data\">$summary</td></tr>";
	$return .= "			</table>";
	$return .= "		</td></tr>";
	$return .= "	</table>";
	$return .= "</div>";
	$return .= "<div id=\"group_options_container\">";
	$return .= "	<div class=\"options\">$showJoinGroup</div>";
	$return .= "</div>";
	$return .= "<div id=\"message_box\" style=\"display:none;\" onClick=\"$(this).hide();\"></div>";
	$return .= "<div id=\"members_list_container\"></div>";
	
	return($return);
	
}

///if this function gets updated the ajaxShowUpcomingGroupEvents.php file needs to also be updated.
function showEventList($groupId, $title) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showGroup/event_list.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	$showSummary = $config->readValue('displaySummary');
	$w = $config->readValue('maxImageSizeX');
	$h = $config->readValue('maxImageSizeY');
	
	//create user groups validation object
	$userGroup = new CategoryUserGroupValidator();
	$excludeCategories = $userGroup->viewCategoryExclusionList('events');
	
	$return .= "				<div id=\"event_list\">\n";
	$return .= "					<div class=\"header\">$title</div>\n";
	$return .= "					<div class=\"body\">\n";
	$return .= "						<div id=\"upcoming_events_container\">\n";
	
	$todaysDate = getdate();
	
	$month = $todaysDate['mon'];
	$day = $todaysDate['mday'];
	$year = $todaysDate['year'];
	
	$getDate = $todaysDate['year'] . "-" . $todaysDate['mon'] . "-" . $todaysDate['mday'] . " 00:00:00";
	
	$s = 0;
	
	$result = mysql_query("SELECT events.id FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE events.groupId = '{$groupId}' AND events.startDate >= '{$getDate}' AND events.publishState = 'Published'$excludeCategories AND ((events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.private = '0')) GROUP BY events.id");
	$totalRows = mysql_num_rows($result);
	
	//if there's nothing to display, just exit (remove this test to display "no events currently scheduled" message)
	if ($totalRows == 0) {
		
		return;
		
	}
	
	$showTotalPages = ceil($totalRows / $maxDisplay);

	if ($totalRows > 0) {

		$showCurrentPage = floor($s / $maxDisplay) + 1;

	} else {

		$showCurrentPage = 0;

	}
	
	$result = mysql_query("SELECT events.id, events.title, events.summary, events.summaryImage, DATE_FORMAT(startDate, '%M %d, %Y %h:%i %p') AS newStartDate, DATE_FORMAT(expireDate, '%M %d, %Y %h:%i %p') AS newExpireDate FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE events.groupId = '{$groupId}' AND events.startDate >= '{$getDate}' AND events.publishState = 'Published'$excludeCategories AND ((events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.private = '0')) GROUP BY events.id ORDER BY startDate ASC, title ASC LIMIT $maxDisplay");
	$count = mysql_num_rows($result);
	
	if ($count > 0) {
		
		while ($row = mysql_fetch_object($result)) {
			
			$x++;
			
			if ($x < $count) {
				
				$style = " event_item_row_separator";
				
			} else {
				
				$style = "";
				
			}
			
			$title = htmlentities($row->title);
			
			if (trim($row->summaryImage) != "") {
				
				$image = "							<div class=\"summary_image\">\n<a href=\"/events/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&w=$w&h=$h\"></a></div>\n";
				$imageOffsetClass = " image_offset";
				
			} else {
				
				$image = "";
				$imageOffsetClass = "";
				
			}
			
			$return .= "						<div class=\"event_item$style\">\n";
			$return .= "$image";
			$return .= "							<div class=\"details_container$imageOffsetClass\">\n";
			$return .= "								<div class=\"title\"><a href=\"/events/id/$row->id\">$title</a></div>\n";
			$return .= "								<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$return .= "									<tr><td class=\"start_date\">$row->newStartDate</td></tr><tr><td class=\"end_date\">$row->newExpireDate</td></tr>\n";
			$return .= "								</table>\n";
			
			if ($showSummary == "true") {
				
				$summary = preg_replace("/\\n/", "<br>", htmlentities($row->summary));
					
				$return .= "							<div class=\"summary\">\n";
				$return .= "								$summary\n";
				$return .= "							</div>\n";
				
			}
			
			$return .= "							</div>\n";
			$return .= "						</div>\n";
			
		}
		
		$return .= "<div id=\"event_list_navigation\">";
		$return .= "	<div class=\"totals\">$totalRows Events</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateEventList('$s', 'b');\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateEventList('$s', 'n');\">Next</a></div></div>";
		$return .= "</div>";
		
	} else {
		
		$return .= "							<div class=\"event_item\">No events are currently scheduled.</div>\n";
		
	}
	
	$return .= "						</div>\n";
	$return .= "					</div>\n";
	$return .= "				</div>\n";
	
	return($return);
	
}

function showLatestConversations($groupId, $title) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showGroup/latest_conversations.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	$maxCharCount = $config->readValue('maxPostCharacters');
	
	$return .= "				<div id=\"latest_conversation_posts\">\n";
	$return .= "					<div class=\"header\"><a href=\"/showGroupConversationsList.php?groupId=$groupId\">$title</a></div>\n";
	$return .= "					<div class=\"body\">\n";
	$return .= "						<div id=\"conversation_list\">\n";
	
	$result = mysql_query("SELECT conversationsPosts.parentId, conversationsPosts.id, conversationsPosts.body, DATE_FORMAT(conversationsPosts.dateCreated, '%M %d, %Y %h:%i %p') AS newDateCreated, conversationsPosts.author, conversations.title, conversations.restricted FROM conversationsPosts INNER JOIN conversations ON conversations.groupId = '{$groupId}' LEFT JOIN groupsMembers ON conversations.groupId = groupsMembers.parentId WHERE conversationsPosts.parentId = conversations.id AND ((conversations.restricted = '1' AND groupsMembers.parentId = conversations.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (conversations.restricted = '0')) GROUP BY conversationsPosts.id ORDER BY conversationsPosts.dateCreated DESC LIMIT $maxDisplay");
	$total = mysql_num_rows($result);
	
	if ($total > 0) {
		
		$bbcode = new ProcessBbcode();
		
		while ($row = mysql_fetch_object($result)) {
			
			$x++;
			
			if ($x < $total) {
				
				$style = " latest_conversation_separator";
				
			} else {
				
				$style = "";
				
			}
			
			$title = htmlentities($row->title);
			$body = $bbcode->strip($row->body);
			
			//clear any extra line breaks at the beginning
			$body = preg_replace("/^[\s]*/", "", $body);
			
			if (trim($maxCharCount) != "" && (strlen($body) > $maxCharCount)) {
				
				$body = substr($body, 0, strrpos(substr($body, 0, $maxCharCount), ' ')) . '...';
				
			}
			
			$return .= "						<div class=\"latest_conversation_item$style\"><div class=\"latest_conversation_title\"><a href=\"/showGroupConversation.php?parentId=$row->parentId&findId=$row->id\">$title</a></div><div class=\"latest_conversation_date\">$row->newDateCreated</div><div class=\"latest_conversation_author\">$row->author</div><div class=\"latest_conversation_summary\">$body</div></div>\n";
			
		}
		
	} else {
		
		$return .= "						<a class=\"latest_conversation_link\" href=\"/showGroupConversationsList.php?groupId=$groupId\">Click here</a> to start a conversation!\n";
		
	}
	
	$return .= "						</div>\n";
	$return .= "					</div>\n";
	$return .= "				</div>\n";
	
	return($return);
	
}

function showLatestBlogs($groupId, $title) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showGroup/latest_blogs.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	
	$return .= "				<div id=\"latest_blogs\">\n";
	$return .= "					<div class=\"header\">$title</div>\n";
	$return .= "					<div class=\"body\">\n";
	$return .= "						<div id=\"member_blogs_list\">\n";
	
	//create user groups validation object
	$userGroup = new CategoryUserGroupValidator();
	$excludeCategories = $userGroup->viewCategoryExclusionList('blogs');
	
	$result = mysql_query("SELECT blogs.id, DATE_FORMAT(dateCreated, '%M %d, %Y %h:%i %p') AS newDateCreated, blogs.title, blogs.usernameCreated, blogs.summary FROM blogs INNER JOIN groupsMembers ON groupsMembers.parentId = '{$groupId}' AND blogs.usernameCreated = groupsMembers.username WHERE blogs.publishState = 'Published'$excludeCategories GROUP BY blogs.id ORDER BY dateCreated DESC LIMIT $maxDisplay");
	$total = mysql_num_rows($result);
	
	if (mysql_num_rows($result) > 0) {
		
		while ($row = mysql_fetch_object($result)) {
			
			$x++;
			
			if ($x < $total) {
				
				$style = " latest_blog_row_separator";
				
			} else {
				
				$style = "";
				
			}
			
			$title = htmlentities($row->title);
			$showDateCreated = $row->newDateCreated;
			
			$body = $row->summary;
			
			if (strlen($body) > 160) {
				
				$body = substr($body, 0, strrpos(substr($body, 0, 180), ' ')) . '...';
				
			}
			
			$body = preg_replace("/\\n/", "<br>", htmlentities($body));
			
			$return .= "							<div class=\"latest_blog_item$style\"><div class=\"latest_blog_title\"><a href=\"/blogs/id/$row->id\">$title</a></div><div class=\"latest_blog_date\">$showDateCreated</div><div class=\"latest_blog_author\">$row->usernameCreated</div><div class=\"latest_blog_summary\">$body</div></div>\n";

		}
		
	} else {
		
		$return .= "							<div class=\"latest_blog_item\">Coming Soon!</div>\n";
		
	}
	
	$return .= "						</div>\n";
	$return .= "					</div>\n";
	$return .= "				</div>\n";
	
	return($return);
	
}

function showcaseGallery($groupId, $title) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showGroup/gallery.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	$maxPerRow = $config->readValue('maxPerRow');
	$w = $config->readValue('maxImageSizeX');
	$h = $config->readValue('maxImageSizeY');
	
	//if the user is not an admin, validate that the user is a member of this group
	$result = mysql_query("SELECT parentId, imageUrl FROM imagesGroups WHERE parentId = '{$groupId}' AND inSeriesImage = 1 ORDER BY RAND() LIMIT $maxDisplay");
	$total = mysql_num_rows($result);
	
	if ($total > 0) {
		
		$urlCategory =urlencode($row->category);
		$caption = htmlentities($row->caption);
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		$return .= "				<div id=\"gallery_container\">\n";
		$return .= "					<div class=\"header\"><a href=\"/groupgalleries/id/$groupId\">$title</a></div>\n";
		$return .= "					<div class=\"body\">\n";
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this row's column itteration
			$x++;
			
			//keep track of total displayed so far
			$count++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			$return .= "<div class=\"gallery_image_container$separator\">\n";
			$return .= "	<a href=\"/groupgalleries/id/$groupId\"><img src=\"/file.php?load=$row->imageUrl&w=$w&h=$h\" border=\"0\"></a>\n";
			$return .= "</div>\n";
			
			//row separator
			if ($x == $maxPerRow && $count < $total) {
				
				$return .= "			<div class=\"gallery_image_row_separator\"></div>\n";
				$x = 0;
				
			}
			
		}
		
		$return .= "					</div>\n";
		$return .= "				</div>\n";
		
	}	
	
	return($return);
	
}

//update last access date/time
$time = date("Y-m-d H:i:s", time());
mysql_query("UPDATE groups SET lastAccess = '{$time}' WHERE id = '{$groupId}'");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showGroup.css");
@import url("/assets/core/resources/css/main/groupAdminOptions.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showGroup.js"></script>
<script language="javascript">
groupId = $groupId;
last_s = 0;
</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("part_group_admin_options.php");

include("assets/core/layout/showgroup/layout_showgroup.php");

$site_container->showSiteContainerBottom();

?>