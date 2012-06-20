<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$parentId = sanitize_string($_REQUEST['parentId']);
$imageId = sanitize_string($_REQUEST['imageId']);

//load image information
$result = mysql_query("SELECT * FROM imagesDocuments WHERE parentId = '{$parentId}' AND id = '{$imageId}' ORDER BY weight ASC LIMIT 1");
$row = mysql_fetch_object($result);

if (trim($row->title) != "") {
	
	$escapeTitle = preg_replace('/\\\/', '\\\\\\', $row->title);
	$escapeTitle = preg_replace('/\'/', '\\\'', $escapeTitle);

	
} else {
	
	$escapeTitle = preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']);
	
}

$showImage = "<div class=\"document_image_container\"><img id=\"main_image\" onLoad=\"fadeinMainImage();\" src=\"/file.php?load=$row->imageUrl&w=720\" border=\"0\"></div>";

if (trim($row->caption) != "") {

	$showCaption = "<div id=\"document_image_caption\"><div class=\"document_image_caption_content\">" . $row->caption . "</div></div>\n";

}

if (trim($row->body) != "") {
	
	$showBody = "<div id=\"document_body\">$row->body</div>\n";

}

print $showImage;
print $showCaption;
print $showBody;

print "<script>";
print "if ($('#id').length > 0) {";
print "$('#id').val('$imageId');";
print "}";
print "imageId = '$imageId';";
print "if ($('#comments_container').length > 0) {";
print "regenerateCommentsList();";
print "}";
print "document.title='$escapeTitle';";
print "</script>";

?>