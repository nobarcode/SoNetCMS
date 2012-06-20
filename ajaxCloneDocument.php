<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

if (trim($id) == "") {$error = 1;}

if ($error != 1) {
	
	$time = date("Y-m-d H:i:s", time());
	
	//clone document
	$result = mysql_query("INSERT INTO documents (shortcut, usernameCreated, usernameUpdated, documentType, category, subcategory, subject, dateCreated, dateUpdated, publishState, title, body, author, summaryImage, summary, summaryLinkText, keywords, galleryLinkText, galleryLinkBackUrl, galleryLinkBackText, cssPath, showToolbar, showComments, requireAuthentication, doNotSyndicate, component) SELECT CONCAT(shortcut, '_', UNIX_TIMESTAMP()), usernameCreated, usernameUpdated, documentType, category, subcategory, subject, '{$time}' AS dateCreated, dateUpdated, 'Unpublished' AS publishState, title, body, author, summaryImage, summary, summaryLinkText, keywords, galleryLinkText, galleryLinkBackUrl, galleryLinkBackText, cssPath, showToolbar, showComments, requireAuthentication, doNotSyndicate, component FROM documents WHERE id = '{$id}'");
	
	if ($result) {
		
		//grab the id for this new (cloned) base article
		$lastId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
		
		////clone image gallery
		//$result = mysql_query("INSERT INTO imagesDocuments (parentId, inSeriesImage, caption, title, body, showDetails, imageUrl, weight) SELECT '{$lastId}' AS parentId, inSeriesImage, caption, title, body, showDetails, imageUrl, weight FROM imagesDocuments WHERE parentId = '{$id}'");
		
		//load the new document
		$result = mysql_query("SELECT body FROM documents WHERE id = '{$lastId}' LIMIT 1");
		$row = mysql_fetch_object($result);
		$body = $row->body;
		
		//check the newly cloned document exists
		if (!$result) {

			header('Content-type: application/javascript');
			print "$('#message_box').html('<b>There was an error processing your request, please check the following:</b><br>- System error. Unable to clone associated images.');";
			print "$('#message_box').show();";
			exit;

		}
		
	} else {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<b>There was an error processing your request, please check the following:</b><br>- System error. Unable to clone document.');";
		print "$('#message_box').show();";
		exit;
		
	}
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('Document cloned successfully.');";
	print "$('#message_box').show();";
	exit;
		
}

?>