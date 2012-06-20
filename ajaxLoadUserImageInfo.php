<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$imageId = sanitize_string($_REQUEST['imageId']);

if (trim($_SESSION['username']) == "" || trim($imageId) == "") {$error = 1;}

$result = mysql_query("SELECT * FROM imagesUsers WHERE parentId = '{$_SESSION['username']}' AND id = '{$imageId}'");
$totalRows = mysql_num_rows($result);

if ($totalRows == 0) {$error = 1;}

if ($error != 1) {
	
	$row = mysql_fetch_object($result);
	
	$escapeImageURL = preg_replace('/\\\/', '\\\\\\', $row->imageUrl);
	$escapeImageURL = preg_replace('/\'/', '\\\'', $escapeImageURL);
	
	$escapeCaption = preg_replace('/\\\/', '\\\\\\', $row->caption);
	$escapeCaption = preg_replace('/\'/', '\\\'', $escapeCaption);
	
	$escapeTitle = preg_replace('/\\\/', '\\\\\\', $row->title);
	$escapeTitle = preg_replace('/\'/', '\\\'', $escapeTitle);
	
	$escapeBody = preg_replace('/\\\/', '\\\\\\', $row->body);
	$escapeBody = preg_replace("/\\n/", "\\\\n", $escapeBody);
	$escapeBody = preg_replace("/\\r/", "\\\\r", $escapeBody);
	$escapeBody = preg_replace('/\'/', '\\\'', $escapeBody);
	
	//output javascript
	header('Content-type: application/javascript');
	
	print "$('#fullsize_image').html('<img src=\"/file.php?load=$row->imageUrl&w=920\" border=\"0\">');";
	
	//update the image url box
	print "$('#imageUrl').val('$escapeImageURL');";
	
	//update the caption
	print "$('#caption').val('$escapeCaption');";
	
	//update the title
	print "$('#title').val('$escapeTitle');";
	
	//update the editor window with the escaped summary information, reset the dirty flag, and reset the undo history
	print "CKEDITOR.instances.documentBody.setData('$escapeBody', function() {CKEDITOR.instances.documentBody.resetDirty();CKEDITOR.instances.documentBody.resetUndo();});";
	
	//update the hidden form field with the name of the image
	print "$('#update_image_id').val('$row->id');";
	
	//create the editor options (name of image being edited and cancel button)
	print "$('#editor_options').html('<a id=\"cancel_editing\" class=\"button\" href=\"javascript:cancelEditing();\" onclick=\"this.blur();\"><span>Cancel Editing</span></a>');";
	print "$('#editor_options').show();";
	
}

?>