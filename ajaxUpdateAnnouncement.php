<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$year = sanitize_string($_REQUEST['year']);
$month = sanitize_string($_REQUEST['month']);
$day = sanitize_string($_REQUEST['day']);
$title = sanitize_string($_REQUEST['title']);
$body = sanitize_string($_REQUEST['body']);
$linkText = sanitize_string($_REQUEST['linkText']);
$editLinkUrl = sanitize_string($_REQUEST['editLinkUrl']);

if (trim($id) == "") {exit;}

if (trim($title) == "") {$error = 1; $errorMessage .= "- Please provide a title.<br>";}
if (trim($body) == "") {$error = 1; $errorMessage .= "- Please enter text in the body of your announcement.<br>";}

if ($error != 1) {
	
	$expireDate = "$year-$month-$day 00:00";
	
	mysql_query("UPDATE announcements SET usernameUpdated = '{$_SESSION['username']}', dateExpires = '{$expireDate}', title = '{$title}', body = '{$body}', linkText = '{$linkText}', linkUrl = '{$editLinkUrl}' WHERE id = '{$id}'");

	header('Content-type: application/javascript');
	print "$('#message_box').html('<div>Announcement updated successfully.</div>');";
	print "$('#message_box').show();";
	print "cancelEditAnnouncement();";
	exit;
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>