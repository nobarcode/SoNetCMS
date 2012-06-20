<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

$id = sanitize_string($_REQUEST['id']);
$documentType = sanitize_string($_REQUEST['documentType']);
$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);
$rating = sanitize_string($_REQUEST['rating']);
$title = sanitize_string($_REQUEST['title']);
$summaryImage = sanitize_string($_REQUEST['summaryImage']);
$summary = sanitize_string($_REQUEST['summary']);
$keywords = sanitize_string($_REQUEST['keywords']);
$customHeader = sanitize_string($_REQUEST['customHeader']);
$documentBody = sanitize_string($_REQUEST['documentBody']);

$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);

//build error messages if any of the following fields are empty
if (trim($documentType) == "") {$error = 1; $errorMessage .= "- Please select a document type.<br>";}
if (trim($category) == "") {$error = 1; $errorMessage .= "- Please select a category.<br>";}
if (!$userGroup->allowEditing()) {$error = 1; $errorMessage .= "- Invalid category selection.<br>";}
if (trim($subcategory) == "") {$error = 1; $errorMessage .= "- Please select a subcategory.<br>";}
if (trim($subject) == "") {$error = 1; $errorMessage .= "- Please select a subject.<br>";}
if (trim($title) == "") {$error = 1; $errorMessage .= "- Please enter a title.<br>";}
if (trim($summary) == "") {$error = 1; $errorMessage .= "- Please enter a document summary.<br>";}
if (trim($customHeader) == "") {$error = 1; $errorMessage .= "- Please supply a header.<br>";}
if (trim($documentBody) == "") {$error = 1; $errorMessage .= "- Please enter text in the body of your document.<br>";}
if (trim($documentBody) !="" && (preg_match("/[a-z0-9_$]+\((.*?)\)/i", $documentBody) || preg_match("/<script*/i", $documentBody))) {$error = 1; $errorMessage .="- Javascript references are not allowed.<br>";}

//if an error has occurred, perform the following:
if ($error == 1) {
	
	//build main error container
	$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('$showErrorMessage');";
	print "$('#message_box').show();";
	exit;

// if no error occurred, handle the data:	
} else {
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	//if this is a new base article, do an insert
	if (trim($id) == "") {
		
		//populate the database
		$result = mysql_query("INSERT INTO blogs (usernameCreated, documentType, category, subcategory, subject, rating, dateCreated, publishState, customHeader, title, summaryImage, summary, keywords, body) VALUES ('{$_SESSION['username']}', '{$documentType}', '{$category}', '{$subcategory}', '{$subject}', '{$rating}', '{$time}', 'Unpublished', '{$customHeader}', '{$title}', '{$summaryImage}', '{$summary}', '{$keywords}', '{$documentBody}')");
		
		if ($result) {
			
			//clear the autosave session after manually saving the document successfully
			$sessionName = "autosave" . $_SESSION['username'];
			$_SESSION[$sessionName] = "";
			
			//grab the id for this new base article
			$id = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
			
			//display the new blog
			header('Content-type: application/javascript');
			print "window.location = '/blogs/id/$id';";
			exit;
			
		} else {
			
			header('Content-type: application/javascript');
			print "$('#message_box').html('Unknown error! Please try your request again.');";
			print "$('#message_box').show();";
			exit;
			
		}
		
	//if this is an update to an existing blog, do an update	
	} else {
		
		//validate the user submitting the update
		$result = mysql_query("SELECT usernameCreated FROM blogs WHERE id = '{$id}' LIMIT 1");
		$row = mysql_fetch_object($result);

		//if the user is not an admin or the usernameCreated, exit
		if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['username'] != $row->usernameCreated) {exit;}
		
		//populate the database
		$result = mysql_query("UPDATE blogs SET usernameUpdated = '{$_SESSION['username']}', documentType = '{$documentType}', category = '{$category}', subcategory = '{$subcategory}', subject = '{$subject}', rating = '{$rating}', dateUpdated = '{$time}', customHeader = '{$customHeader}', title = '{$title}', summaryImage = '{$summaryImage}', summary = '{$summary}', keywords = '{$keywords}', body = '{$documentBody}' WHERE id = '{$id}'");
		
		if ($result) {
			
			//clear the autosave session after manually saving the document successfully
			$sessionName = "autosave" . $_SESSION['username'];
			$_SESSION[$sessionName] = "";
			
			//jump to the blog list
			header('Content-type: application/javascript');
			print "window.location = '/blogs/id/$id';";
			exit;
			
		} else {
			
			header('Content-type: application/javascript');
			print "$('#message_box').html('<b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.');";
			print "$('#message_box').show();";
			exit;
			
		}
		
	}
	
}
	
?>