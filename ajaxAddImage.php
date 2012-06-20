<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$caption = sanitize_string($_REQUEST['caption']);
$title = sanitize_string($_REQUEST['title']);
$documentBody = sanitize_string($_REQUEST['documentBody']);
$imageUrl = sanitize_string($_REQUEST['imageUrl']);
$update_image_id = sanitize_string($_REQUEST['update_image_id']);

$result = mysql_query("SELECT category FROM documents WHERE id = '{$id}' LIMIT 1");
$row = mysql_fetch_object($result);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowEditing()) {exit;}

//build error messages if any of the following fields are empty
if (trim($imageUrl) == "") {$error = 1; $errorMessage .= "- Please choose an image.<br>";}
preg_match("/\.([^\.]+)$/", $imageUrl, $imageType);
$imageExtentsion = strtolower($imageType[1]);
if ($imageExtentsion != "jpg" && $imageExtentsion != "jpeg" && $imageExtentsion != "png" && $imageExtentsion != "gif") {$error = 1; $errorMessage .= "- The image gallery only supports the following image types: jpg, png, or gif.<br>";}

//if an error has occurred, perform the following:
if ($error == 1) {
	
	//build main error container
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;

// if no error occurred, handle the data:	
} else {
	
	//define showDetails if it's empty
	if (trim($showDetails) == "") {$showDetails = "0";}
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	//if this is a new image, do an insert
	if (trim($update_image_id) == "") {
		
		//get current weight and add one
		$result = mysql_query("SELECT * FROM imagesDocuments WHERE parentId = '{$id}'");
		$weight = mysql_num_rows($result) + 1;
				
		//populate the database
		$result = mysql_query("INSERT INTO imagesDocuments (parentId, caption, title, body, imageUrl, weight) VALUES ('{$id}', '{$caption}', '{$title}', '{$documentBody}', '{$imageUrl}', '{$weight}')");
		
		//grab the id for this new base article
		$lastId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
		
		if (!$result) {
			
			//build main error container
			header('Content-type: application/javascript');
			print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.</div>');";
			print "$('#message_box').show();";
			exit;
			
		} else {
			
			$result = mysql_query("INSERT INTO documentVersioning (parentId, documentType, version, dateCreated, title, body, usernameCreated) VALUES ('{$lastId}', 'documentImage', '1', '{$time}', '{$title}', '{$documentBody}', '{$_SESSION['username']}')");
				
			//check if versioning was successful, if not display a warning
			if (!$result) {
				
				//build main error container
				header('Content-type: application/javascript');
				print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Your document was saved, but versioning was unsuccessful.</div>');";
				print "$('#message_box').show();";
				exit;
				
			}
				
			//clear the autosave session after manually saving the document successfully
			$sessionName = "autosave" . $_SESSION['username'];
			$_SESSION[$sessionName] = "";
			
			ajaxUpdate("Image added sucessfully.");
			exit;
			
		}
		
	//if this is an update to an existing image, do an update	
	} else {
		
		$result = mysql_query("SELECT publishState FROM documents WHERE id = '{$id}'");
		$row = mysql_fetch_object($result);
		
		//validate user access level
		if ($row->publishState == "Published" && ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3)) {
			
			//build main error container
			header('Content-type: application/javascript');
			print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- You are not authorized to edit documents.</div>');";
			print "$('#message_box').show();";
			exit;
			
		}
		
		//populate the database
		$result = mysql_query("UPDATE imagesDocuments SET caption = '{$caption}', title = '{$title}', body = '{$documentBody}', imageUrl = '{$imageUrl}' WHERE parentId = '{$id}' AND id = '{$update_image_id}'");
		
		if (!$result) {

			//build main error container
			header('Content-type: application/javascript');
			print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.</div>');";
			print "$('#message_box').show();";
			exit;

		} else {
			
			//grab current version body
			$result = mysql_query("SELECT body FROM documentVersioning WHERE parentId = '{$update_image_id}' AND documentType = 'documentImage' ORDER BY version DESC LIMIT 1");
			$row = mysql_fetch_object($result);
			
			//check if last version is the same as submitted version. if not, create versioning
			if ($documentBody !== sanitize_string($row->body)) {
				
				//create versioning
				$result = mysql_query("SELECT parentId FROM documentVersioning WHERE parentId = '{$update_image_id}' AND documentType = 'documentImage'");
				$versionNumber = mysql_num_rows($result) + 1;
				
				$result = mysql_query("INSERT INTO documentVersioning (parentId, documentType, version, dateCreated, title, body, usernameCreated) VALUES ('{$update_image_id}', 'documentImage', '{$versionNumber}', '{$time}', '{$title}', '{$documentBody}', '{$_SESSION['username']}')");
				
				//check if versioning was successful, if not display a warning
				if (!$result) {
					
					//build main error container
					header('Content-type: application/javascript');
					print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Your document was saved, but versioning was unsuccessful.</div>');";
					print "$('#message_box').show();";
					exit;
					
				}
				
			}
			
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
	print "$('#message_box').html('<div>$message</div>');\n";
	print "$('#message_box').show();\n";
	
}
	
?>