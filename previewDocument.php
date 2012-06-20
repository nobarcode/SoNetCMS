<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_component_loader.php");
include("class_process_bbcode.php");

class DynamicProperties { }
$row = new DynamicProperties;

$row->documentType = $_REQUEST['documentType'];
$row->category = $_REQUEST['category'];
$row->subcategory = $_REQUEST['subcategory'];
$row->subject = $_REQUEST['subject'];
$row->author = $_REQUEST['author'];
$row->title = $_REQUEST['title'];
$row->rating = $_REQUEST['rating'];
$row->cssPath = $_REQUEST['cssPath'];
$body = $_REQUEST['htmlData'];

if (trim($row->cssPath) != "") {
	
	$importCustomCss = "\n@import url(\"$row->cssPath\");";
	
}

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
$loadGroupPortlet = array($componentLoader, 'loadGroupRcComponent');

//component file & <p> cleanup
$body = preg_replace("/<p>\n[\t]+\[\[component file=\"(.*?)\"(.*?)\\]]<\/p>/i", "[[component file=\"$1\"$2]]", $body);

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
$body = preg_replace("/<p>\n[\t]+\[\[smartlink(.*?)\]\](.*?)\[\[\/smartlink\]\]<\/p>/i", "[[smartlink$1]]$2[[/smartlink]]", $body);

//smartlink
$body = preg_replace_callback("/\[\[smartlink activeDocument=\"(.*?)\" cssClass=\"(.*?)\" activeCssClass=\"(.*?)\" url=\"(.*?)\" linkOnActive=\"(.*?)\"\]\](.*?)\[\[\/smartlink\]\]/i", $convertSmartlinks, $body);

//toggler & <p> cleanup
$body = preg_replace("/<p>\n[\t]+\[\[toggler(.*?)\]\](.+?)<\/p>/i", "[[toggler$1]]\n<p>$2</p>", $body);
$body = preg_replace("/<p>\n[\t]+\[\[toggler/i", "[[toggler", $body);
$body = preg_replace("/togglerStyle=\"(.*?)\"\]\]<\/p>/i", "togglerStyle=\"$1\"]]", $body);
$body = preg_replace("/<p>\n[\t]+\[\[\/toggler\]\]<\/p>/i", "[[/toggler]]", $body);
$body = preg_replace("/<p>\n[\t]+(.*?)\[\[\/toggler\]\]<\/p>/i", "<p>$1</p>\n[[/toggler]]", $body);
$body = preg_replace("/\[\[\/toggler\]\]<\/p>/i", "[[/toggler]]", $body);

//toggler
$body = preg_replace_callback("/\[\[toggler text=\"(.*?)\" id=\"(.*?)\" activeDocument=\"(.*?)\" cssClassLink=\"(.*?)\" activeCssClassLink=\"(.*?)\" cssClassContent=\"(.*?)\" togglerStyle=\"(.*?)\"\]\](.*?)\[\[\/toggler\]\]/is", $convertToggler, $body);

//document attributes
$body = preg_replace_callback("/\[attribute type=\"(.*?)\"\]/i", $loadAttributes, $body);

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

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/superfish/js/superfish.js"></script>

<style>
@import url("/assets/core/resources/css/main/global.css");
@import url("/assets/core/resources/css/main/index.css");
@import url("/assets/core/resources/css/main/custom.css");$importCustomCss
</style>

<title>PREVIEW</title>

</head>

<body>
<div style="width:100%; padding:10px 0px 10px 0px; text-align:center; font-weight:bold; color:#ffffff; background:#dd4a4a;">PREVIEW</div>
<div id="body_container">
	<div id="body_inner">
		<div class="document_body">
$body
		</div>
	</div>
</div>
<div class="clear_both"></div>
<div id="footer_container">
	<div id="footer_inner">
		FOOTER
	</div>
</div>
</body>

</html>
EOF;

?>