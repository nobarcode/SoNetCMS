<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);

if (trim($groupId) == "") {
	
	exit;
	
}

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, jump to login page
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	include("part_session_check.php");
	
}

//link back
$showGalleryLinkBack = "<div id=\"link_back\"><a href=\"/groups/id/$groupId\">back to group</a></div>\n";

//setup the comment button to either show the comment text area or prompt for login
if (trim($_SESSION['username']) == "") {
	
	$commentButton = "<a class=\"button\" href=\"/signIn.php?jb=$jb&sr=1\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
	
} else {
	
	$commentButton = "<a class=\"button\" href=\"javascript:showAddComment();\" onclick=\"this.blur();\"><span>Add Comment</span></a>";
}

//get the total pages (3 images per page) for javascript functions (subtract 1 because first page is 0)
$result = mysql_query("SELECT id FROM imagesGroups WHERE parentId = '{$groupId}' AND inSeriesImage = 1");
$totalPages = ceil((mysql_num_rows($result) / 3) - 1);

//get the total width of all thumbnails for javascript/css scroller
$thumbsContainerWidth = ($totalPages + 1) * 180;

//load the first set of thumbnails
$result = mysql_query("SELECT parentId, id, imageUrl FROM imagesGroups WHERE parentId = '{$groupId}' AND inSeriesImage = 1 ORDER BY weight ASC LIMIT 3");

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
$result = mysql_query("SELECT id, showComments FROM imagesGroups WHERE parentId = '{$groupId}' AND inSeriesImage = 1 ORDER BY weight ASC LIMIT 1");
$row = mysql_fetch_object($result);

if ($row->showComments != 1) {
	
	 $showComments = " style=\"display:none;\"";
	
}

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showGroupGallery.css");
@import url("/assets/core/resources/css/main/groupAdminOptions.css");
EOF;

if (mysql_num_rows($result) == 0) {
	
$_SESSION['status'] = array('empty', '', $username);
header("Location: /status.php?type=gallery");
exit;
	
} else {

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showGroupGallery.js"></script>
<script language="javascript">

parentId = '$groupId';
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

include("part_group_admin_options.php");

print <<< EOF
			$showGalleryLinkBack
			<div id="left_column">
			<div id="thumbs_container"><div id="thumbs" style="width:{$thumbsContainerWidth}px;">$showThumbs</div></div>
			<div id="thumbnail_arrow_left" style="visibility:hidden;"><a href="javascript:previousThumbSet();"></a></div>$showRightArrow
			</div>
			<div id="right_column">
			<div id="outer_content_container"><div id="main_content_container"></div></div>
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
									<input type="hidden" id="parentId" name="parentId" value="$groupId">
									<input type="hidden" id="id" name="id" value="$row->id">
									<input type="hidden" id="type" name="type" value="groupImageComment">
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>
EOF;

$site_container->showSiteContainerBottom();

}

?>