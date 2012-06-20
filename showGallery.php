<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

if (trim($id) == "") {
	
	exit;
	
}

//verify that the requested document exists (if user is not an admin, also check that is published)
if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
	
	$result = mysql_query("SELECT id, shortcut, galleryLinkBackUrl, galleryLinkBackText FROM documents WHERE id = '{$id}' LIMIT 1");
	
} else {
	
	//if the user is not an admin make sure the document is published and grab the requireAuthentication settings too
	$result = mysql_query("SELECT id, shortcut, galleryLinkBackUrl, galleryLinkBackText, requireAuthentication FROM documents WHERE id = '{$id}' AND publishState = 'Published' LIMIT 1");
	
}

//if document does not exist (or if the visitor is not an admin and the document is not published), exit without error message (creates a blank page) -- this is to hinder hacking and spamming attempts
if (mysql_num_rows($result) == 0) {
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
		
		print "<b>Resource ID #$id Not Found!</b><br><br>The requested resource is not available. There are two possible reasons for this error:<ol><li>The requested resource ID has not yet been assigned to anything.<li>The resource associated to the requested ID has been deleted.</ol>";
		
	}
	
	exit;
	
} else {
	
	$row = mysql_fetch_object($result);
	
	//check for session if requireAuthentication is true for this document
	if ($row->requireAuthentication == 1) {
		
		include("part_session_check.php");
		
	}
	
	if (trim($row->galleryLinkBackText) != "") {
		
		if (trim($row->galleryLinkBackUrl) == "") {
			
			$showGalleryLinkBack = "<div id=\"link_back\"><a href=\"/documents/open/$row->shortcut\">" . htmlentities($row->galleryLinkBackText) . "</a></div>\n";
			
		} else {
			
			$showGalleryLinkBack = "<div id=\"link_back\"><a href=\"" . urlencode($row->galleryLinkBackUrl) . "\">" . htmlentities($row->galleryLinkBackText) . "</a></div>\n";
			
		}	
		
	}
	
}

$result = mysql_query("SELECT id, category, showComments FROM documents WHERE id = '{$id}' LIMIT 1");
$row = mysql_fetch_object($result);

//assign category to category variable to allow menu to generate an active tab
$category = $row->category;

//check if comments are allowed in the parent document
$showComments = $row->showComments;

//setup the comment button to either show the comment text area or prompt for login
if (trim($_SESSION['username']) == "") {
	
	$commentButton = "<a class=\"button\" href=\"/signIn.php?jb=$jb&sr=1\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
	
} else {
	
	$commentButton = "<a class=\"button\" href=\"javascript:showAddComment();\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
}

//get the total pages (3 images per page) for javascript functions (subtract 1 because first page is 0)
$result = mysql_query("SELECT id FROM imagesDocuments WHERE parentId = '{$id}' AND inSeriesImage = 1");
$totalPages = ceil((mysql_num_rows($result) / 3) - 1);

//get the total width of all thumbnails for javascript/css scroller
$thumbsContainerWidth = ($totalPages + 1) * 180;

//load the first set of thumbnails
$result = mysql_query("SELECT parentId, id, imageUrl FROM imagesDocuments WHERE parentId = '{$id}' AND inSeriesImage = 1 ORDER BY weight ASC LIMIT 3");

if (mysql_num_rows($result) > 0) {
	
	if ($totalPages > 0) {

		$showRightArrow = "<div id=\"thumbnail_arrow_right\"><a href=\"javascript:nextThumbSet();\"></a></div>";

	} else {

		$showRightArrow = "<div id=\"thumbnail_arrow_right\" style=\"visibility:hidden;\"><a href=\"javascript:nextThumbSet();\"></a></div>";

	}
	
	$showThumbs .= "<div id=\"page_0\" class=\"thumb_set\">";
	
	while ($row = mysql_fetch_object($result)) {
		
		$showThumbs .= "<div id=\"image_$row->id\" class=\"thumb_image_container\" onclick=\"showImage('$row->id');\"><div class=\"image\"><img src=\"/file.php?load=$row->imageUrl&thumbs=true\" border=\"0\"></div></div>";
		
	}
	
	$showThumbs .= "</div>";
	
}

//load first image data
$result = mysql_query("SELECT id FROM imagesDocuments WHERE parentId = '{$id}' AND inSeriesImage = 1 ORDER BY weight ASC LIMIT 1");
$row = mysql_fetch_object($result);

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showGallery.css");
EOF;

if (mysql_num_rows($result) == 0) {
	
$_SESSION['status'] = array('empty', '', $username);
header("Location: /status.php?type=gallery");
exit;
	
} else {

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showGallery.js"></script>
<script language="javascript">

parentId = '$id';
imageId = '$row->id';
lastSelection = '$row->id';
totalPages = '$totalPages';
page = 0;
highestPageRequested = 0;

</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			$showGalleryLinkBack
			<div id="left_column">
			<div id="thumbs_container"><div id="thumbs" style="width:{$thumbsContainerWidth}px;">$showThumbs</div></div>
			<div id="thumbnail_arrow_left" style="visibility:hidden;"><a href="javascript:previousThumbSet();"></a></div>$showRightArrow
			</div>
			<div id="right_column">
			<div id="main_content_container"></div>
			<div class="clear_both"></div>
EOF;

if ($showComments == 1) {

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
									<input type="hidden" id="id" name="id" value="$row->id">
									<input type="hidden" id="type" name="type" value="documentImageComment">
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>
EOF;

}

$site_container->showSiteContainerBottom();

}

?>