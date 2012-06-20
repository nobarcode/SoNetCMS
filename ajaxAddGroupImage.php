<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$caption = sanitize_string($_REQUEST['caption']);
$title = sanitize_string($_REQUEST['title']);
$documentBody = sanitize_string($_REQUEST['documentBody']);
$showComments = sanitize_string($_REQUEST['showComments']);
$imageUrl = sanitize_string($_REQUEST['imageUrl']);
$update_image_id = sanitize_string($_REQUEST['update_image_id']);

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

//build error messages if any of the following fields are empty
if (trim($imageUrl) == "") {$error = 1; $errorMessage .= "- Please choose an image.<br>";}
preg_match("/\.([^\.]+)$/", $imageUrl, $imageType);
$imageExtentsion = strtolower($imageType[1]);
if ($imageExtentsion != "jpg" && $imageExtentsion != "jpeg" && $imageExtentsion != "png" && $imageExtentsion != "gif") {$error = 1; $errorMessage .= "- The image gallery only supports the following image types: jpg, png, or gif.<br>";}
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
	
	//if this is a new image, do an insert
	if (trim($update_image_id) == "") {
		
		//get current weight and add one
		$result = mysql_query("SELECT * FROM imagesGroups WHERE parentId = '{$groupId}'");
		$weight = mysql_num_rows($result) + 1;
				
		//populate the database
		$result = mysql_query("INSERT INTO imagesGroups (parentId, caption, title, body, showComments, imageUrl, weight) VALUES ('{$groupId}', '{$caption}', '{$title}', '{$documentBody}', '{$showComments}', '{$imageUrl}', '{$weight}')");
		
		//grab the id for this new base article
		$lastId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
		
		if (!$result) {
			
			//build main error container
			$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.";

			header('Content-type: application/javascript');
			print "$('#message_box').html('$showErrorMessage');";
			print "$('#message_box').show();";
			exit;
			
		} else {
			
			//clear the autosave session after manually saving the document successfully
			$sessionName = "autosave" . $_SESSION['username'];
			$_SESSION[$sessionName] = "";
			
			ajaxUpdate("Image added sucessfully.");
			exit;
			
		}
		
	//if this is an update to an existing image, do an update	
	} else {
		
		//populate the database
		$result = mysql_query("UPDATE imagesGroups SET caption = '{$caption}', title = '{$title}', body = '{$documentBody}', showComments = '{$showComments}', imageUrl = '{$imageUrl}' WHERE parentId = '{$groupId}' AND id = '{$update_image_id}'");
		
		if (!$result) {

			//build main error container
			$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.";

			header('Content-type: application/javascript');
			print "$('#message_box').html('$showErrorMessage');";
			print "$('#message_box').show();";
			exit;

		} else {
			
			//clear the autosave session after manually saving the document successfully
			$sessionName = "autosave" . $_SESSION['username'];
			$_SESSION[$sessionName] = "";
			
			ajaxUpdate("Image updated sucessfully.");
			exit;
		
		}
		
	}
	
}

function ajaxUpdate($message) {
	
	//update invisible frame with a callback to our prototype fucntion and then reload the iframe with a dummy page to prevent re-submissions if the window is reloaded
	header('Content-type: application/javascript');
	
	print "regenerateList(last_s, '');\n";
	
	//clear the fullsize image, if there is one
	print "if ($('#fullsize_image').length > 0) {\n";
	print "$('#fullsize_image').remove();\n";
	print "}\n";
	
	//clear the editor window
	//clear undo history and disable buttons
	//set editor to not dirty
	print "CKEDITOR.instances.documentBody.setData('', function() {CKEDITOR.instances.documentBody.resetDirty();CKEDITOR.instances.documentBody.resetUndo();});";
	
	//reset the the form and clear update_image_id hidden field
	print "$('#newDocumentForm')[0].reset();\n";
	print "$('#update_image_id').val('');\n";
	
	//update the message box and display it
	print "$('#message_box').html('$message');\n";
	print "$('#message_box').show();\n";
	
}
	
?>