<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_config_reader.php");
include("class_component_loader.php");

$id = sanitize_string($_REQUEST['id']);

//if the current user is not a site admin
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4) {
	
	$showUnpublished = " AND ((events.groupId IS NOT NULL AND events.publishState = 'Unpublished' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2') AND groupsMembers.status =  'approved') OR events.publishState = 'Published')";
	
}

$result = mysql_query("SELECT events.id, events.groupId, events.usernameCreated, events.usernameUpdated, events.category, events.subcategory, events.subject, events.customHeader, events.title, EXTRACT(YEAR FROM startDate) AS startYear, EXTRACT(MONTH FROM startDate) AS startMonth, EXTRACT(DAY FROM startDate) AS startDay, events.startDate, events.expireDate, events.body, events.showComments, events.publishState, groups.name FROM events LEFT JOIN groups ON events.groupId = groups.id LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE 1$dateFilter$hideGroupEventsSQL$excludeCategories AND ((events.groupId IS NOT NULL AND events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.groupId IS NULL OR (events.groupId IS NOT NULL AND events.private = '0')))$showUnpublished AND events.id = '{$id}'");

//check if any results were returned
if (mysql_num_rows($result) == 0) {
	
	$_SESSION['status'] = array('not found', $id, '');
	header("Location: /status.php?type=event");
	exit;
	
}

//load the event
$row = mysql_fetch_object($result);

//load site group validator
$groupValidator = new GroupValidator();
$groupValidator->isGroupAdmin($row->groupId, $_SESSION['username']);

//display the publish option message
if ((($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) || $groupValidator->isGroupAdmin) && $row->publishState == "Unpublished") {
	
	$unpublished_message = "			<div id=\"unpublished_message\">This event is not published. To publish this event now, <a href=\"javascript:togglePublishState();\" onclick=\"return confirm('Are you sure you want to publish this event?');\">click here</a></div>\n";
	
}

//check if this is event belongs to a group, if it does and read access is not permited, check to see if this user created the event (prevents group admins from not being able to access an event that was created with a group that was later assigned a group)
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));

if (!$userGroup->allowRead() && !$groupValidator->isGroupAdmin) {
	
	$_SESSION['status'] = array('user group access', $id, '');
	header("Location: /status.php?type=event");
	exit;
	
}

//assign category to category variable to allow menu to generate an active tab
$category = $row->category;

//set the title
$title = htmlentities($row->title);

//setup the comment button to either show the comment text area or prompt for login
if (trim($_SESSION['username']) == "") {
	
	$commentButton = "<a class=\"button\" href=\"/signIn.php?jb=$jb&sr=1\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
	
} else {
	
	$commentButton = "<a class=\"button\" href=\"javascript:showAddComment();\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
}

$body =<<< EOF
				<div id="hide_group_events">
					<input type="checkbox" id="hideGroupEvents" value="1" onClick="toggleGroupEvents();"$hideGroupEventsChecked> Hide events published by groups
				</div>
				<div class="clear_both"></div>
EOF;

//define body variable
$body = $row->customHeader;
$body .= $row->body;

//create component loader class object and callback array that contains a reference to the object and the desired method
$componentLoader = new ComponentLoader();
$loadAttributes = array($componentLoader, 'loadAttributes');

//document attributes
$body = preg_replace_callback("/\[attribute type=\"(.*?)\"\]/i", $loadAttributes, $body);

if (trim($row->groupId) != "") {
	
	$showGroupName = "				<div id=\"group_name\">This event was published by the following group: <a href=\"/groups/id/$row->groupId\">" . htmlentities($row->name) . "</a></div>\n<div class=\"clear_both\"></div>\n";
	$javascript_set_group_event_filter = "\nhideGroupEvents = '0';\n";
	$hideGroupEventsChecked = "";
	
} else {
	
	$javascript_set_group_event_filter = "\nhideGroupEvents = '1';\n";
	$hideGroupEventsChecked = " checked";
	
}

//update last access date/time
$time = date("Y-m-d H:i:s", time());
mysql_query("UPDATE events SET lastAccess = '{$time}' WHERE id = '{$id}'");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showEventCalendar.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showEventCalendar.js"></script>
<script language="javascript">
id = $id;
groupId= '$groupId';$javascript_set_group_event_filter
last_s = 0;
month = '$row->startMonth';
day = '$row->startDay';
year = '$row->startYear';
</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div id="left_column_container">
$unpublished_message
$showGroupName
				<div class="document_body">$body</div>
EOF;

if (trim($row->groupId) == "") {
	
	if ((($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) || ($row->publishState == "Unpublished" && $_SESSION['userLevel'] == 4)) && $userGroup->allowEditing()) {
		
		print "				<div id=\"editor_options\"><a class=\"button\" href=\"/eventEditor.php?id=$id\" onclick=\"this.blur();\"><span>Edit</span></a></div>\n";
		print "				<div class=\"clear_right\"></div>\n";
		
	}
	
} else {
	
	//ignores user group levels on group events to prevent orphaned group events
	if ((($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) || ($row->publishState == "Unpublished" && $_SESSION['userLevel'] == 4)) || $groupValidator->isGroupAdmin) {
		
		print "				<div id=\"editor_options\"><a class=\"button\" href=\"/groupEventEditor.php?id=$id&groupId=$row->groupId\" onclick=\"this.blur();\"><span>Edit</span></a></div>\n";
		print "				<div class=\"clear_right\"></div>\n";
		
	}
	
}

if ($row->showComments == 1) {

print <<< EOF
				<div id="comment_toggle_navigation"><a class="button" href="javascript:showCommentsList();" onclick="this.blur();"><span>Show Comments</span></a></div>
				<div id="comments_main_container" style="display:none;">
					<div>
						<div class="header">
							<table border="0" cellpadding="0" cellspacing="0" width="100%">
								<tr valign="center">
									<td>
										<form id="comment_filter_form" style="margin:0px; padding:0px;" name="comment_filter_form">
											<select id="commentFilter" name="commentFilter" onChange="regenerateCommentsList();">
												<option value="dateOldest">Date Posted</option>
												<option value="dateNewest">Latest Posts</option>
												<option value="scoreHighest">Voted Highest</option>
												<option value="scoreLowest">Voted Lowest</option>
											</select>
										</form>
									</td>
									<td>
										<a class="hide_comment" href="javascript:showCommentsList();">Hide Comments</a>
									</td>
								</tr>
							</table>
						</div>
						<div class="body">
							<div id="comments_container"></div>
						</div>
						<div id="add_comment_navigation">
							$commentButton
						</div>				
						<div id="add_comment_container" style="display:none;">
							<div>
								<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
								<div id="comment_input_container">
									<div class="add_comment_container_header">Add Comment</div>
									<div class="add_comment_container_body">
										<form id="add_comment" name="add_comment" action="/ajaxAddComment.php" method="post" enctype="multipart/form-data">
										<textarea id="body" class="comment_body_text_area" name="body" rows="8"></textarea>
										<input type="submit" id="submit" value="Save"> <input type="button" id="cancel" value="Cancel" onClick="showAddComment();">
										<input type="hidden" id="parentId" name="parentId" value="$id">
										<input type="hidden" id="type" name="type" value="eventComment">
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
EOF;

}

print <<< EOF
			</div>
			<div id="right_column_container">
				<div id="calendar_container"></div>
				<div id="event_list"></div>
			</div>
EOF;

$site_container->showSiteContainerBottom();

?>