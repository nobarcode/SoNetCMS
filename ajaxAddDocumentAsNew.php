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
	
	//replace all white space characters with a dash
	$shortcut = str_replace(' ', '-', unsanitize_string($shortcut));
	
	//strip all non alphanumeric except dashes
	$shortcut = sanitize_string(ereg_replace("[^A-Za-z0-9\-]", "", $shortcut));
	
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
	print "$('#message_box').html('</div><b>There was an error processing your request, please check the following:</b><br>$errorMessage<div>');";
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
	
	//set document editing tracker
	$result = mysql_query("DELETE FROM documentEditTracking WHERE documentType = 'document' AND id = '{$id}' AND username = '{$_SESSION['username']}'");
	
	//populate the database
	$result = mysql_query("INSERT INTO documents (shortcut, usernameCreated, documentType, category, subcategory, subject, rating, dateCreated, publishState, title, body, author, summaryImage, summary, summaryLinkText, keywords, galleryLinkText, galleryLinkBackUrl, galleryLinkBackText, showToolbar, showComments, requireAuthentication, doNotSyndicate, component) VALUES ('{$shortcut}', '{$_SESSION['username']}', '{$documentType}', '{$category}', '{$subcategory}', '{$subject}', '{$rating}', '{$time}', 'Unpublished', '{$title}', '{$documentBody}', '{$author}', '{$summaryImage}', '{$summary}', '{$summaryLinkText}', '{$keywords}', '{$galleryLinkText}', '{$galleryLinkBackUrl}', '{$galleryLinkBackText}', '{$showToolbar}', '{$showComments}', '{$requireAuthentication}', '{$doNotSyndicate}', '{$component}')");
	
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
		if ($documentBody !== sanitize_string($row->body)) {
			
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