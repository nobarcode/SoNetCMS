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

//create year selection for blog list
$result = mysql_query("SELECT *, EXTRACT(MONTH FROM dateCreated) AS month, EXTRACT(DAY FROM dateCreated) AS day, EXTRACT(YEAR FROM dateCreated) AS year FROM blogs WHERE id = '{$id}'");
$row = mysql_fetch_object($result);

if (mysql_num_rows($result) == 0) {
	
	$_SESSION['status'] = array('not found', $id, '');
	header("Location: /status.php?type=blog");
	exit;
	
}

//exit if not published and current user is not an admin or the author
if (($row->publishState != "Published") && $_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4 && $_SESSION['username'] != $row->usernameCreated) {
	
	$_SESSION['status'] = array('not found', $id, '');
	header("Location: /status.php?type=blog");
	exit;
	
}

//assign category to category variable to allow menu to generate an active tab
$category = $row->category;

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowRead() && $_SESSION['username'] != $row->usernameCreated) {
	
	$_SESSION['status'] = array('user group access', $id, '');
	header("Location: /status.php?type=blog");
	exit;
	
}


if ($row->publishState == "Unpublished") {
	
	$unpublished_message = "				<div id=\"unpublished_message\">This blog is not published. To publish this blog now, <a href=\"javascript:togglePublishState();\" onclick=\"return confirm('Are you sure you want to publish this blog?');\">click here.</a></div>\n";
	
}

$month = $row->month;
$day = $row->day;
$year = $row->year;

//setup the comment button to either show the comment text area or prompt for login
if (trim($_SESSION['username']) == "") {
	
	$commentButton = "<a class=\"button\" href=\"/signIn.php?jb=$jb&sr=1\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
	
} else {
	
	$commentButton = "<a class=\"button\" href=\"javascript:showAddComment();\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
}

//load document information
$result = mysql_query("SELECT blogs.id, blogs.usernameCreated, blogs.category, blogs.subcategory, blogs.subject, blogs.rating, blogs.customHeader, blogs.title, blogs.body, blogs.dateCreated, EXTRACT(MONTH FROM blogs.dateCreated) AS month, EXTRACT(YEAR FROM blogs.dateCreated) AS year, blogs.dateUpdated, blogs.datePublished, categories.hidden, categories.useAlternateClass, IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes, IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo, IFNULL(ROUND(SUM(documentVotes.voteYes) / COUNT(documentVotes.parentId) * 100, 1),0) AS voteScore, (SELECT COUNT(parentId) FROM commentsDocuments WHERE commentsDocuments.parentId = blogs.id AND commentsDocuments.type = 'blogComment') AS totalComments FROM blogs LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' INNER JOIN categories ON categories.category = blogs.category WHERE blogs.id = '{$id}' GROUP BY blogs.id LIMIT 1");

if (mysql_num_rows($result) == 0) {
	
	header("Status: 404 Not Found");
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
		
		print "<b>Resource ID #$id Not Found!</b><br><br>The requested resource is not available. There are two possible reasons for this error:<ol><li>The requested resource ID has not yet been assigned to anything.<li>The resource associated to the requested ID has been deleted.</ol>";
		
	}
	
	exit;
	
} else {
	
	$row = mysql_fetch_object($result);
	
}

$showAuthor = "<a href=\"/documentList.php?listAuthor=$row->usernameCreated\">" . htmlentities($row->usernameCreated) . "</a>";

//define body variable
$body = $row->customHeader;
$body .= $row->body;

//create component loader class object and callback array that contains a reference to the object and the desired method
$componentLoader = new ComponentLoader();
$loadAttributes = array($componentLoader, 'loadAttributes');

//document attributes
$body = preg_replace_callback("/\[attribute type=\"(.*?)\"\]/i", $loadAttributes, $body);

//update hit counter and last access date/time
$time = date("Y-m-d H:i:s", time());
mysql_query("UPDATE blogs SET hits = hits + 1, lastAccess = '{$time}' WHERE id = '{$id}'");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showBlog.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showBlog.js"></script>
<script language="javascript">
id = $id;
usernameCreated = "$row->usernameCreated";
last_s = 0;
month = '$month';
day = '$day';
year = '$year';
</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div id="left_column_container">
$unpublished_message
				<div class="document_body">$body</div>
EOF;

include("assets/core/config/part_social_sites_toolbar.php");

print "<div id=\"voting\">\n<table height=\"26\" cellpadding=\"0\" cellspacing=\"0\" align=\"right\"><tr valign=\"center\"><td style=\"padding-right:5px;\">";

if (trim($row->voteScore) != "") {
	
	$voteScore = $row->voteScore . "%";
	
} else {
	
	$voteScore = "no votes";
	
}

if (trim($_SESSION['username']) == "") {
	
	print "<a href=\"/signIn.php?jb=$jb\">sign in</a> to vote</td>\n";
	
} else {
	
	print "<a href=\"javascript:vote('score', 'blog', '$id', '1');\"><img style=\"float:left;\" src=\"/assets/core/resources/images/icon_vote_yes_large.gif\" border=\"0\"></a></td><td style=\"padding-right:5px;\"><a href=\"javascript:vote('score', 'blog', '$id', '0');\"><img style=\"float:left;\" src=\"/assets/core/resources/images/icon_vote_no_large.gif\" border=\"0\"></a></td>\n";
	
}

print "<td><span id=\"score\">$voteScore</span></td></tr></table><div class=\"clear_both\"></div><div id=\"document_stats\"><div id=\"total_up_votes\">$row->totalVoteYes</div><div id=\"total_down_votes\">$row->totalVoteNo</div><div id=\"total_comments\">$row->totalComments</div></div></div>\n\n";

if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['username'] == $row->usernameCreated) {
	
	print "			<div class=\"clear_both\"></div>\n";
	print "			<div id=\"editor_options\"><a class=\"button\" href=\"/showMyBlogEditor.php?id=$id\" onclick=\"this.blur();\"><span>Edit</span></a></div>\n";
	
}

print <<< EOF
				<div class="clear_both"></div>
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
										<input type="hidden" id="type" name="type" value="blogComment">
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="right_column_container">
				<div id="calendar_container"></div>
				<div id="blog_list"></div>
			</div>
EOF;

$site_container->showSiteContainerBottom();

?>