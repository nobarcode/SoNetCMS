<?php

//ini_set('display_errors',1); 
//error_reporting(E_ALL);

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_config_reader.php");
include("class_component_loader.php");
include("class_process_bbcode.php");

$id = sanitize_string($_REQUEST['id']);
$shortcut = sanitize_string($_REQUEST['shortcut']);

//load the document ID if a shortcut is provided, otherwise load the default URL from the first category, then load 
if (trim($shortcut) != "") {
	
	$result = mysql_query("SELECT id FROM documents WHERE shortcut = '{$shortcut}' LIMIT 1");
	
} else {
	
	$result = mysql_query("SELECT defaultUrl FROM categories ORDER BY weight ASC LIMIT 1");
	$row = mysql_fetch_object($result);
	$defaultUrl = basename($row->defaultUrl);
	
	//load the document ID
	$result = mysql_query("SELECT id FROM documents WHERE shortcut = '{$defaultUrl}' LIMIT 1");
	
}

$row = mysql_fetch_object($result);
$id = $row->id;

//verify that the requested document exists (if user is not an admin, also check that is published)
if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
	
	$result = mysql_query("SELECT id, category, publishState FROM documents WHERE id = '{$id}' LIMIT 1");
	
} else {
	
	//if the user is not an admin make sure the document is published and that it's not a component, also grab the requireAuthentication settings
	$result = mysql_query("SELECT id, category, requireAuthentication, publishState FROM documents WHERE id = '{$id}' AND publishState = 'Published' AND component != '1' LIMIT 1");
	
}

//if document does not exist
if (mysql_num_rows($result) == 0) {
	
	$_SESSION['status'] = array('not found', $shortcut, '');
	header("Location: /status.php?type=document");
	exit;
	
} else {
	
	$row = mysql_fetch_object($result);
	
	//check for session if requireAuthentication is true for this document
	if ($row->requireAuthentication == 1) {
		
		include("part_session_check.php");
		
	}
	
}

if (($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) && $row->publishState == "Unpublished") {
	
	$unpublished_message = "			<div id=\"unpublished_message\">This document is not published. To publish this document now, <a href=\"javascript:togglePublishState();\" onclick=\"return confirm('Are you sure you want to publish this document?');\">click here.</a></div>\n";
	
}

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowRead()) {
	
	$_SESSION['status'] = array('user group access', $shortcut, '');
	header("Location: /status.php?type=document");
	exit;
	
}

//setup the comment button to either show the comment text area or prompt for login
if (trim($_SESSION['username']) == "") {
	
	$commentButton = "<a class=\"button\" href=\"/signIn.php?jb=$jb&sr=1\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
	
} else {
	
	$commentButton = "<a class=\"button\" href=\"javascript:showAddComment();\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
}

//check if gallery is available for this document
$result = mysql_query("SELECT imagesDocuments.id, documents.galleryLinkText FROM imagesDocuments INNER JOIN documents ON documents.id = imagesDocuments.parentId WHERE imagesDocuments.parentId = '{$id}' AND imagesDocuments.inSeriesImage = 1");
$row = mysql_fetch_object($result);

//if gallery link text is provided, create the link
if (mysql_num_rows($result) > 0) {
	
	if (trim($row->galleryLinkText) != "") {
		
		$showGalleryLink = "<div class=\"gallery_link\"><a href=\"/galleries/id/$id\">" . htmlentities($row->galleryLinkText) . "</a></div><div class=\"clear_both\"></div>\n";
		
	}
	
}

//load document information
$result = mysql_query("SELECT documents.category, documents.subcategory, documents.subject, documents.rating, documents.dateCreated, documents.dateUpdated, documents.datePublished, documents.publishState, documents.title, documents.author, documents.body, documents.cssPath, documents.showToolbar, documents.showComments, documents.component, categories.hidden, categories.useAlternateClass, IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes, IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo, IFNULL(ROUND(SUM(documentVotes.voteYes) / COUNT(documentVotes.parentId) * 100, 1),0) AS voteScore, (SELECT COUNT(parentId) FROM commentsDocuments WHERE commentsDocuments.parentId = documents.id AND commentsDocuments.type = 'documentComment') AS totalComments FROM documents LEFT JOIN documentVotes ON documentVotes.parentId = documents.id AND documentVotes.type = 'document' INNER JOIN categories ON categories.category = documents.category WHERE documents.id = '{$id}' GROUP BY documents.id LIMIT 1");
$row = mysql_fetch_object($result);

//assign category to category variable to allow menu to generate an active tab
$category = $row->category;

if (trim($row->cssPath) != "") {
	
	$importCss = "\n@import url(\"$row->cssPath\");";
	
}

if ($row->showToolbar == 1) {
	
	if (trim($row->voteScore) != "") {
		
		$voteScore = $row->voteScore . "%";
		
	} else {
		
		$voteScore = "no votes";
		
	}
	
	$showToolbar .= "<div id=\"voting\">\n<table height=\"26\" cellpadding=\"0\" cellspacing=\"0\" align=\"right\"><tr valign=\"center\"><td style=\"padding-right:5px;\">";

	if (trim($_SESSION['username']) == "") {

		$showToolbar .= "<a href=\"/signIn.php?jb=$jb\">sign in</a> to vote</td>\n";

	} else {

		$showToolbar .= "<a href=\"javascript:vote('score', 'document', '$id', '1');\"><img style=\"float:left;\" src=\"/assets/core/resources/images/icon_vote_yes_large.gif\" border=\"0\"></a></td><td style=\"padding-right:5px;\"><a href=\"javascript:vote('score', 'document', '$id', '0');\"><img style=\"float:left;\" src=\"/assets/core/resources/images/icon_vote_no_large.gif\" border=\"0\"></a></td>\n";

	}

	$showToolbar .= "<td><span id=\"score\">$voteScore</span></td></tr></table><div class=\"clear_both\"></div><div id=\"document_stats\"><div id=\"total_up_votes\">$row->totalVoteYes</div><div id=\"total_down_votes\">$row->totalVoteNo</div>";
	
	if ($row->showComments == 1) {
		
		$showToolbar .= "<div id=\"total_comments\">$row->totalComments</div>";
		
	}
	
	$showToolbar .= "</div></div>\n";
	
}

//define body variable

//create component loader class object and callback array that contains a reference to the object and the desired method
$componentLoader = new ComponentLoader();
$loadComponentFile = array($componentLoader, 'loadComponentFile');
$loadComponent = array($componentLoader, 'loadComponent');
$displayAuthenticatedContent = array($componentLoader, 'displayAuthenticatedContent');
$displayGroupContent = array($componentLoader, 'displayGroupContent');
$convertSmartlinks = array($componentLoader, 'convertSmartlinks');
$convertToggler = array($componentLoader, 'convertToggler');
$loadAttributes = array($componentLoader, 'loadAttributes');
$loadDocumentPortlet = array($componentLoader, 'loadDocumentRcComponent');
$loadBlogPortlet = array($componentLoader, 'loadBlogRcComponent');
$loadEventPortlet = array($componentLoader, 'loadEventRcComponent');
$loadAnnouncementPortlet = array($componentLoader, 'loadAnnouncementRcComponent');
$loadConversationPortlet = array($componentLoader, 'loadConversationRcComponent');
$loadMemberPortlet = array($componentLoader, 'loadMemberRcComponent');
$loadCommentPortlet = array($componentLoader, 'loadCommentRcComponent');
$loadConversationPortlet = array($componentLoader, 'loadConversationRcComponent');
$loadGroupPortlet = array($componentLoader, 'loadGroupRcComponent');

//component file & <p> cleanup
$body = preg_replace("/<p>\n[\t]+\[\[component file=\"(.*?)\"(.*?)\\]]<\/p>/i", "[[component file=\"$1\"$2]]", $row->body);

//component file
$body = preg_replace_callback("/\[\[component file=\"(.*?)\"(.*?)\]\]/i", $loadComponentFile, $body);

//component & <p> cleanup
$body = preg_replace("/<p>\n[\t]+\[\[component id=\"(.*?)\"(.*?)\\]]<\/p>/i", "[[component id=\"$1\"$2]]", $body);

//component
$body = preg_replace_callback("/\[\[component id=\"(.*?)\"(.*?)\]\]/i", $loadComponent, $body);

//authenticated content & <p> cleanup
$body = preg_replace("/<p>\n[\t]+\[\[authenticated_content\]\](.+?)<\/p>/i", "[[authenticated_content$1]]\n<p>$2</p>", $body);
$body = preg_replace("/<p>\n[\t]+\[\[authenticated_content/i", "[[authenticated_content", $body);
$body = preg_replace("/authenticated_content\]\]<\/p>/i", "authenticated_content]]", $body);
$body = preg_replace("/<p>\n[\t]+\[\[\/authenticated_content\]\]<\/p>/i", "[[/authenticated_content]]", $body);
$body = preg_replace("/<p>\n[\t]+(.*?)\[\[\/authenticated_content\]\]<\/p>/i", "<p>$1</p>\n[[/authenticated_content]]", $body);
$body = preg_replace("/\[\[\/authenticated_content\]\]<\/p>/i", "[[/authenticated_content]]", $body);

//authenticated content
$body = preg_replace_callback("/\[\[authenticated_content\]\](.*?)\[\[\/authenticated_content\]\]/is", $displayAuthenticatedContent, $body);

//group content & <p> cleanup
$body = preg_replace("/<p>\n[\t]+\[\[group_content(.*?)\]\](.+?)<\/p>/i", "[[group_content$1]]\n<p>$2</p>", $body);
$body = preg_replace("/<p>\n[\t]+\[\[group_content/i", "[[group_content", $body);
$body = preg_replace("/groups=\"(.*?)\"\]\]<\/p>/i", "groups=\"$1\"]]", $body);
$body = preg_replace("/<p>\n[\t]+\[\[\/group_content\]\]<\/p>/i", "[[/group_content]]", $body);
$body = preg_replace("/<p>\n[\t]+(.*?)\[\[\/group_content\]\]<\/p>/i", "<p>$1</p>\n[[/group_content]]", $body);
$body = preg_replace("/\[\[\/group_content\]\]<\/p>/i", "[[/group_content]]", $body);

//group content
$body = preg_replace_callback("/\[\[group_content groups=\"(.*?)\"\]\](.*?)\[\[\/group_content\]\]/is", $displayGroupContent, $body);

//smartlink & <p> cleanup
$body = preg_replace("/<p>[\s]*\[\[smartlink(.*?)\]\](.*?)\[\[\/smartlink\]\]<\/p>/i", "[[smartlink$1]]$2[[/smartlink]]", $body);

//smartlink
$body = preg_replace_callback("/\[\[smartlink activeDocument=\"(.*?)\" cssClass=\"(.*?)\" activeCssClass=\"(.*?)\" url=\"(.*?)\" linkOnActive=\"(.*?)\"\]\](.*?)\[\[\/smartlink\]\]/i", $convertSmartlinks, $body);

//toggler & <p> cleanup
$body = preg_replace("/<p>\n[\t]+\[\[toggler(.*?)\]\](.+?)<\/p>/i", "[[toggler$1]]\n$2", $body);
$body = preg_replace("/<p>\n[\t]+\[\[toggler/i", "[[toggler", $body);
$body = preg_replace("/togglerStyle=\"(.*?)\"\]\]<\/p>/i", "togglerStyle=\"$1\"]]", $body);
$body = preg_replace("/<p>\n[\t]+\[\[\/toggler\]\]<\/p>/i", "[[/toggler]]", $body);
$body = preg_replace("/<p>\n[\t]+(.*?)\[\[\/toggler\]\]<\/p>/i", "$1\n[[/toggler]]", $body);
$body = preg_replace("/\[\[\/toggler\]\]<\/p>/i", "[[/toggler]]", $body);

//toggler
$body = preg_replace_callback("/\[\[toggler text=\"(.*?)\" id=\"(.*?)\" activeDocument=\"(.*?)\" cssClassLink=\"(.*?)\" activeCssClassLink=\"(.*?)\" cssClassContent=\"(.*?)\" togglerStyle=\"(.*?)\"\]\](.*?)\[\[\/toggler\]\]/is", $convertToggler, $body);

//document attributes
$body = preg_replace_callback("/\[\[attribute type=\"(.*?)\"\]\]/i", $loadAttributes, $body);

//rich content component & <p> cleanup
$body = preg_replace("/<p>\n[\t]+\[\[rc_component (.*?)\]\]<\/p>/i", "[[rc_component $1]]", $body);

//document portlet
$body = preg_replace_callback("/\[\[rc_component type=\"document\" documentType=\"(.*?)\" category=\"(.*?)\" subcategory=\"(.*?)\" subject=\"(.*?)\" showTitle=\"(.*?)\" showAuthor=\"(.*?)\" showDate=\"(.*?)\" showSummary=\"(.*?)\" maxCharCount=\"(.*?)\" showSummaryLink=\"(.*?)\" showRatingGraphic=\"(.*?)\" showRatingText=\"(.*?)\" showVotes=\"(.*?)\" showScore=\"(.*?)\" showTotalComments=\"(.*?)\" organizedBy=\"(.*?)\" showImage=\"(.*?)\" imageWidth=\"(.*?)\" imageHeight=\"(.*?)\" maxDisplay=\"(.*?)\" startAt=\"(.*?)\" maxPerRow=\"(.*?)\" highlightCurrent=\"(.*?)\" skip=\"(.*?)\" noContent=\"(.*?)\"\]\]/is", $loadDocumentPortlet, $body);

//blog portlet
$body = preg_replace_callback("/\[\[rc_component type=\"blog\" author=\"(.*?)\" documentType=\"(.*?)\" category=\"(.*?)\" subcategory=\"(.*?)\" subject=\"(.*?)\" showTitle=\"(.*?)\" showAuthor=\"(.*?)\" showDate=\"(.*?)\" showSummary=\"(.*?)\" maxCharCount=\"(.*?)\" showRatingGraphic=\"(.*?)\" showRatingText=\"(.*?)\" showVotes=\"(.*?)\" showScore=\"(.*?)\" showTotalComments=\"(.*?)\" organizedBy=\"(.*?)\" showImage=\"(.*?)\" imageWidth=\"(.*?)\" imageHeight=\"(.*?)\" maxDisplay=\"(.*?)\" startAt=\"(.*?)\" maxPerRow=\"(.*?)\" skip=\"(.*?)\" noContent=\"(.*?)\"\]\]/is", $loadBlogPortlet, $body);

//event portlet
$body = preg_replace_callback("/\[\[rc_component type=\"event\" category=\"(.*?)\" subcategory=\"(.*?)\" subject=\"(.*?)\" showTitle=\"(.*?)\" showDate=\"(.*?)\" showAuthor=\"(.*?)\" showSummary=\"(.*?)\" maxCharCount=\"(.*?)\" showImage=\"(.*?)\" imageWidth=\"(.*?)\" imageHeight=\"(.*?)\" showTotalComments=\"(.*?)\" organizedBy=\"(.*?)\" maxDisplay=\"(.*?)\" startAt=\"(.*?)\" maxPerRow=\"(.*?)\" noContent=\"(.*?)\"\]\]/is", $loadEventPortlet, $body);

//announcement portlet
$body = preg_replace_callback("/\[\[rc_component type=\"announcement\" id=\"(.*?)\" showDate=\"(.*?)\" showLink=\"(.*?)\" togglerStyle=\"(.*?)\" maxDisplay=\"(.*?)\" startAt=\"(.*?)\" maxPerRow=\"(.*?)\" noContent=\"(.*?)\"\]\]/is", $loadAnnouncementPortlet, $body);

//conversation portlet
$body = preg_replace_callback("/\[\[rc_component type=\"conversation\" showTitle=\"(.*?)\" showDate=\"(.*?)\" showAuthor=\"(.*?)\" showBody=\"(.*?)\" maxCharCount=\"(.*?)\" maxDisplay=\"(.*?)\" startAt=\"(.*?)\" maxPerRow=\"(.*?)\" noContent=\"(.*?)\"\]\]/is", $loadConversationPortlet, $body);

//member portlet
$body = preg_replace_callback("/\[\[rc_component type=\"member\" showImage=\"(.*?)\" imageWidth=\"(.*?)\" imageHeight=\"(.*?)\" showOnlineNow=\"(.*?)\" onlineLabel=\"(.*?)\" offlineLabel=\"(.*?)\" showUsername=\"(.*?)\" showLastActive=\"(.*?)\" lastActiveLabel=\"(.*?)\" showLastLogin=\"(.*?)\" lastLoginLabel=\"(.*?)\" showMemberSince=\"(.*?)\" memberSinceLabel=\"(.*?)\" hasImage=\"(.*?)\" noImageUrl=\"(.*?)\" organizedBy=\"(.*?)\" maxDisplay=\"(.*?)\" startAt=\"(.*?)\" maxPerRow=\"(.*?)\" separatorCharacter=\"(.*?)\" noContent=\"(.*?)\"\]\]/is", $loadMemberPortlet, $body);

//comment portlet
$body = preg_replace_callback("/\[\[rc_component type=\"comment\" category=\"(.*?)\" subcategory=\"(.*?)\" subject=\"(.*?)\" showTitle=\"(.*?)\" showDate=\"(.*?)\" showUsername=\"(.*?)\" showBody=\"(.*?)\" maxCharCount=\"(.*?)\" showVotes=\"(.*?)\" showScore=\"(.*?)\" organizedBy=\"(.*?)\" maxDisplay=\"(.*?)\" startAt=\"(.*?)\" maxPerRow=\"(.*?)\" noContent=\"(.*?)\"\]\]/is", $loadCommentPortlet, $body);

//group portlet
$body = preg_replace_callback("/\[\[rc_component type=\"group\" showName=\"(.*?)\" showOwner=\"(.*?)\" showDate=\"(.*?)\" showAbout=\"(.*?)\" maxCharCount=\"(.*?)\" showLabels=\"(.*?)\" showTotalMembers=\"(.*?)\" showTotalConversations=\"(.*?)\" showTotalEvents=\"(.*?)\" showImage=\"(.*?)\" imageWidth=\"(.*?)\" imageHeight=\"(.*?)\" organizedBy=\"(.*?)\" maxDisplay=\"(.*?)\" startAt=\"(.*?)\" maxPerRow=\"(.*?)\" noContent=\"(.*?)\"\]\]/is", $loadGroupPortlet, $body);

$showBody = "<div class=\"document_body\">$body</div>";

//update hit counter and last access date/time
$time = date("Y-m-d H:i:s", time());
mysql_query("UPDATE documents SET hits = hits + 1, lastAccess = '{$time}' WHERE id = '{$id}'");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/index.css");$importCss
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/index.js"></script>
<script language="javascript">
id = $id;
last_s = 0;
</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(true, $id, $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
$unpublished_message
			$showDetails
			$showBody
			$showGalleryLink
EOF;

if ($row->showToolbar == 1) {
	
	include("assets/core/config/part_social_sites_toolbar.php");
	
}

print $showToolbar;


if (($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) || ($_SESSION['userLevel'] == 4 && $publishState == "Unpublished" && $userGroup->allowEditing())) {
	
	print "			<div class=\"clear_both\"></div>\n";
	print "			<div id=\"editor_options\"><a class=\"button\" href=\"/documentEditor.php?id=$id\" onclick=\"this.blur();\"><span>Edit</span></a></div>\n";
	
}

if ($row->showComments == 1) {

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
									<textarea id="body" class="comment_body_text_area" name="body" rows="8"></textarea><input type="submit" id="submit" value="Save"> <input type="button" id="cancel" value="Cancel" onClick="showAddComment();">
									<input type="hidden" id="parentId" name="parentId" value="$id">
									<input type="hidden" id="type" name="type" value="documentComment">
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
EOF;

}

$site_container->showSiteContainerBottom();

?>