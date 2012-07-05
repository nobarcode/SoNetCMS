<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$shortcut = sanitize_string($_REQUEST['shortcut']);
$documentType = sanitize_string($_REQUEST['documentType']);
$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);
$rating = sanitize_string($_REQUEST['rating']);
$title = sanitize_string($_REQUEST['title']);
$documentBody = sanitize_string($_REQUEST['documentBody']);
$author = sanitize_string($_REQUEST['author']);
$summaryImage = sanitize_string($_REQUEST['summaryImage']);
$summary = sanitize_string($_REQUEST['summary']);
$summaryLinkText = sanitize_string($_REQUEST['summaryLinkText']);
$keywords = sanitize_string($_REQUEST['keywords']);
$galleryLinkText = sanitize_string($_REQUEST['galleryLinkText']);
$galleryLinkBackUrl = sanitize_string($_REQUEST['galleryLinkBackUrl']);
$galleryLinkBackText = sanitize_string($_REQUEST['galleryLinkBackText']);
$cssPath = sanitize_string($_REQUEST['cssPath']);
$showToolbar = sanitize_string($_REQUEST['showToolbar']);
$showComments = sanitize_string($_REQUEST['showComments']);
$requireAuthentication = sanitize_string($_REQUEST['requireAuthentication']);
$doNotSyndicate = sanitize_string($_REQUEST['doNotSyndicate']);
$component = sanitize_string($_REQUEST['component']);

$id = sanitize_string($_REQUEST['id']);
$oldShortcut = sanitize_string($_REQUEST['oldShortcut']);
$componentJb = sanitize_string($_REQUEST['componentJb']);

$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);

//build error messages if any of the following fields are empty
if (trim($documentType) == "") {$error = 1; $errorMessage .= "- Please select a document type.<br>";}
if (trim($category) == "") {$error = 1; $errorMessage .= "- Please select a category.<br>";}
if (!$userGroup->allowEditing()) {$error = 1; $errorMessage .= "- Invalid category selection.<br>";}
if (trim($subcategory) == "") {$error = 1; $errorMessage .= "- Please select a subcategory.<br>";}
if (trim($subject) == "") {$error = 1; $errorMessage .= "- Please select a subject.<br>";}

//if the shortcut is empty, use the title
if (trim($shortcut) == "" && trim($_REQUEST['title']) != "") {
	
	$shortcut = cleanShortcut($_REQUEST['title']);
	
}

//validate the shortcut
if (trim($shortcut) == "") {
	
	$error = 1; $errorMessage .= "- Please enter a shortcut.<br>";
	
} else {
	
	$shortcut = cleanShortcut($shortcut);
	
	if (strtolower($oldShortcut) != $shortcut) {
		
		$result = mysql_query("SELECT id, category, subcategory, subject FROM documents WHERE shortcut = '{$shortcut}' LIMIT 1");
		$row = mysql_fetch_object($result);
		$showCategory = htmlentities($row->category);
		$showSubcategory = htmlentities($row->subcategory);
		$showSubject = htmlentities($row->subject);
		
		if (mysql_num_rows($result) > 0) {$error = 1; $errorMessage .= "- A document with a shortcut of <i>$shortcut</i> already exists in: <a href=\"documentEditor.php?id=$row->id\" target=\"_blank\">/$showCategory/$showSubcategory/$showSubject/$shortcut</a>.<br>";}
		
	}
	
}

if (trim($title) == "") {$error = 1; $errorMessage .= "- Please enter a title.<br>";}
if (trim($summary) == "") {$error = 1; $errorMessage .= "- Please enter a document summary.<br>";}
if (trim($summaryLinkText) == "") {$error = 1; $errorMessage .= "- Please enter summary link text.<br>";}
if (trim($author) == "") {$error = 1; $errorMessage .= "- Please enter the author\'s name for this document.<br>";}
if (trim($documentBody) == "") {$error = 1; $errorMessage .= "- Please enter text in the body of your document.<br>";}

//if an error has occurred, perform the following:
if ($error == 1) {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;

// if no error occurred, handle the data:	
} else {
	
	//define showToolbar if it's empty
	if (trim($showToolbar) == "") {$showToolbar = "0";}
	
	//define showComments if it's empty
	if (trim($showComments) == "") {$showComments = "0";}
	
	//define requireAuthentication if it's empty
	if (trim($requireAuthentication) == "") {$requireAuthentication = "0";}
	
	//define doNotSyndicate if it's empty
	if (trim($doNotSyndicate) == "") {$doNotSyndicate = "0";}
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	//if this is a new base article, do an insert
	if (trim($id) == "") {
		
		//populate the database
		$result = mysql_query("INSERT INTO documents (shortcut, usernameCreated, documentType, category, subcategory, subject, rating, dateCreated, publishState, title, body, author, summaryImage, summary, summaryLinkText, keywords, galleryLinkText, galleryLinkBackUrl, galleryLinkBackText, cssPath, showToolbar, showComments, requireAuthentication, doNotSyndicate, component) VALUES ('{$shortcut}', '{$_SESSION['username']}', '{$documentType}', '{$category}', '{$subcategory}', '{$subject}', '{$rating}', '{$time}', 'Unpublished', '{$title}', '{$documentBody}', '{$author}', '{$summaryImage}', '{$summary}', '{$summaryLinkText}', '{$keywords}', '{$galleryLinkText}', '{$galleryLinkBackUrl}', '{$galleryLinkBackText}', '{$cssPath}', '{$showToolbar}', '{$showComments}', '{$requireAuthentication}', '{$doNotSyndicate}', '{$component}')");
		
		//grab the id for this new base article
		$id = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
		
		if (!$result) {
			
			//build main error container
			header('Content-type: application/javascript');
			print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.</div>');";
			print "$('#message_box').show();";
			exit;
			
		} else {
			
			//grab current version body
			$result = mysql_query("SELECT body FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'document' ORDER BY version DESC LIMIT 1");
			$row = mysql_fetch_object($result);
			
			//check if last version is the same as submitted version. if not, create versioning
			if ($documentBody != sanitize_string($row->body)) {
				
				//create versioning
				$result = mysql_query("INSERT INTO documentVersioning (parentId, documentType, version, dateCreated, title, body, usernameCreated) VALUES ('{$id}', 'document', '1', '{$time}', '{$title}', '{$documentBody}', '{$_SESSION['username']}')");

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
			
			//read config file and determine content creation notification level
			$config = new ConfigReader();
			$config->loadConfigFile('assets/core/config/config.properties');
			
			if ($config->readValue('contentCreationNotifications') == 'true' && $_SESSION['userLevel'] > 3) {
				
				$notificationQuery .= " AND (";
				
				if ($config->readValue('masterNotification') == 'true') {
					
					$notificationQuery .= "level = 1";
					
				}
				
				if ($config->readValue('adminNotification') == 'true') {
					
					if ($config->readValue('masterNotification') == 'true') {

						$notificationQuery .= " OR ";

					}
					
					$notificationQuery .= "level = 2";
					
				}
				
				if ($config->readValue('editorNotification') == 'true') {
					
					if ($config->readValue('masterNotification') == 'true' || $config->readValue('adminNotification') == 'true') {

						$notificationQuery .= " OR ";

					}
					
					$notificationQuery .= "level = 3";
					
				}
				
				$notificationQuery .= ")";
				
				$result = mysql_query("SELECT name, email FROM users WHERE allowEmailNotifications = '1'$notificationQuery");

				while ($row = mysql_fetch_object($result)) {
					
					include("assets/core/config/notifications/add_document/notification.php");
					
					$to = $row->email;
					
					$notificationEmail = "<html>";
					$notificationEmail .= "<body>";
					$notificationEmail .= $notificationText;
					$notificationEmail .= "</body>";
					$notificationEmail .= "</html>";
					
					$headers = "MIME-Version: 1.0\r\n"; 
					$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
					$headers .= "From: " . $config->readValue('siteEmailAddress') . "\r\n";
					$headers .= "Reply-To: " . $config->readValue('siteEmailAddress') . "\r\n";
					
					mail($to, $subject, $notificationEmail, $headers);

				}				
				
			}
			
			//jump to the image gallery editor for this new base article
			header('Content-type: application/javascript');
			print "window.location = '/documents/open/$shortcut';";
			exit;
			
		}
		
	//if this is an update to an existing base article, do an update	
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
		$result = mysql_query("UPDATE documents SET shortcut = '{$shortcut}', usernameUpdated = '{$_SESSION['username']}', documentType = '{$documentType}', category = '{$category}', subcategory = '{$subcategory}', subject = '{$subject}', rating = '{$rating}', dateUpdated = '{$time}', title = '{$title}', body = '{$documentBody}', author = '{$author}', summaryImage = '{$summaryImage}', summary = '{$summary}', summaryLinkText = '{$summaryLinkText}', keywords = '{$keywords}', galleryLinkText = '{$galleryLinkText}', galleryLinkBackUrl = '{$galleryLinkBackUrl}', galleryLinkBackText = '{$galleryLinkBackText}', cssPath = '{$cssPath}', showToolbar = '{$showToolbar}', showComments = '{$showComments}', requireAuthentication = '{$requireAuthentication}', doNotSyndicate = '{$doNotSyndicate}', component = '{$component}' WHERE id = '{$id}'");
		
		if (!$result) {

			//build main error container
			header('Content-type: application/javascript');
			print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.</div>');";
			print "$('#message_box').show();";
			exit;

		} else {
			
			//clear the editing history for this user
			mysql_query("DELETE FROM documentEditTracking WHERE documentType = 'document' AND id = '{$id}' AND username = '{$_SESSION['username']}'");
			
			//update links if the shortcut for this document was changed
			if ($oldShortcut != $shortcut) {
				
				//update category defaultUrls
				mysql_query("UPDATE categories SET defaultUrl = '/documents/open/{$shortcut}' WHERE defaultUrl = '/documents/open/{$oldShortcut}'");
				
				//update category flyout menu content
				$result = mysql_query("SELECT id, flyoutContent FROM categories");
				
				while ($row = mysql_fetch_object($result)) {
					
					$updatedShortcut = str_replace("/documents/open/$oldShortcut", "/documents/open/$shortcut", $row->flyoutContent);
					$updatedShortcut = sanitize_string($updatedShortcut);
					mysql_query("UPDATE categories SET flyoutContent = '{$updatedShortcut}' WHERE id = '{$row->id}'");
					
				}
				
				//update documents
				$result = mysql_query("SELECT id, body FROM documents");
				
				while ($row = mysql_fetch_object($result)) {
					
					//document body links with full path
					$updatedShortcutPath = str_replace("/documents/open/$oldShortcut", "/documents/open/$shortcut", $row->body);
					
					//component references in documents do not contain the full path so they are handled spearately
					$updatedShortcutPath = preg_replace("/\[\[component id=\"$oldShortcut\"(.*?)\]\]/i", "[[component id=\"$shortcut\"$2]]", $updatedShortcutPath);
					
					//grab the skip section contents in rc_component:document components
					preg_match_all("/\[\[rc_component type=\"document\"(.*?)skip=\"(.*?)\"\]\]/is", $updatedShortcutPath, $rcComponentDocumentSkip);
					
					//replace the skip section contents with new shortcut name
					$newRcComponentDocumentSkip = str_replace("$oldShortcut", "$shortcut", $rcComponentDocumentSkip[2][0]);
					
					//replace the skip section contents with the new skip section contents
					$updatedShortcutPath = preg_replace("/\[\[rc_component type=\"document\"(.*?)skip=\"" . $rcComponentDocumentSkip[2][0] . "\"\]\]/is", "[[rc_component type=\"document\"$1skip=\"" . $newRcComponentDocumentSkip . "\"]]", $updatedShortcutPath);
					
					//grab the activeDocument contents in the smartlinks
					preg_match_all("/\[\[smartlink activeDocument=\"(.*?)\"(.*?)\]\]/is", $updatedShortcutPath, $smartlinkActiveDocument);
					
					//replace the activeDocument parameter contents with new shortcut name
					$newSmartlinkActiveDocument = str_replace("$oldShortcut", "$shortcut", $smartlinkActiveDocument[1][0]);
					
					//replace the current activeDocument parameter with the new activeDocument contents
					$updatedShortcutPath = preg_replace("/\[\[smartlink activeDocument=\"" . $smartlinkActiveDocument[1][0] . "\"(.*?)\]\]/is", "[[smartlink activeDocument=\"" . $newSmartlinkActiveDocument . "\"$1]]", $updatedShortcutPath);
					
					$updatedShortcutPath = sanitize_string($updatedShortcutPath);
					
					mysql_query("UPDATE documents SET body = '{$updatedShortcutPath}' WHERE id = '{$row->id}'");
					
				}
				
				mysql_query("UPDATE documents SET galleryLinkBackUrl = '/documents/open/{$shortcut}' WHERE galleryLinkBackUrl = '/documents/open/{$oldShortcut}'");
				
				//update events
				$result = mysql_query("SELECT id, body FROM events");
				
				while ($row = mysql_fetch_object($result)) {
					
					$updatedShortcut = str_replace("/documents/open/$oldShortcut", "/documents/open/$shortcut", $row->body);
					$updatedShortcut = sanitize_string($updatedShortcut);
					mysql_query("UPDATE events SET body = '{$updatedShortcut}' WHERE id = '{$row->id}'");
					
				}
				
				//update blogs
				$result = mysql_query("SELECT id, body FROM blogs");
				
				while ($row = mysql_fetch_object($result)) {
					
					$updatedShortcut = str_replace("/documents/open/$oldShortcut", "/documents/open/$shortcut", $row->body);
					$updatedShortcut = sanitize_string($updatedShortcut);
					mysql_query("UPDATE blogs SET body = '{$updatedShortcut}' WHERE id = '{$row->id}'");
					
				}
				
				//update announcements
				mysql_query("UPDATE announcements SET linkUrl = '/documents/open/{$shortcut}' WHERE linkUrl = '/documents/open/{$oldShortcut}'");
				
			}
			
			//grab current version body
			$result = mysql_query("SELECT body FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'document' ORDER BY version DESC LIMIT 1");
			$row = mysql_fetch_object($result);
			
			//check if last version is the same as submitted version. if not, create versioning
			if ($documentBody !== sanitize_string($row->body)) {
				
				//create versioning
				$result = mysql_query("SELECT parentId FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'document'");
				$versionNumber = mysql_num_rows($result) + 1;
				
				$result = mysql_query("INSERT INTO documentVersioning (parentId, documentType, version, dateCreated, title, body, usernameCreated) VALUES ('{$id}', 'document', '{$versionNumber}', '{$time}', '{$title}', '{$documentBody}', '{$_SESSION['username']}')");
				
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
			
			//jump back to the document
			header('Content-type: application/javascript');
			
			if (trim($componentJb) == "") {
				
				print "window.location = '/documents/open/$shortcut';";
				
			} else {
				
				print "window.location = '$componentJb';";
				
			}
			
			exit;
			
		}
		
	}
	
}

function cleanShortcut($shortcut) {
	
	$translate['normalizeChars'] = array(
		
		'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
		'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
		'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
		'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
		'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
		'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', ' '=>'-'
		
	);
	
	//translate to ascii and replace spaces with dashes
	$shortcut = strtr($shortcut, $translate['normalizeChars']);
	
	//strip all non alphanumeric except dashes
	$shortcut = sanitize_string(ereg_replace("[^A-Za-z0-9\-]", "", $shortcut));
	
	$shortcut = strtolower($shortcut);
	
	return($shortcut);
	
}

?>