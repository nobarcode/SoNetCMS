<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);
$title = sanitize_string($_REQUEST['title']);
$startMonth = sanitize_string($_REQUEST['startMonth']);
$startDay = sanitize_string($_REQUEST['startDay']);
$startYear = sanitize_string($_REQUEST['startYear']);
$startHour = sanitize_string($_REQUEST['startHour']);
$startMinute = sanitize_string($_REQUEST['startMinute']);
$start_AMPM = sanitize_string($_REQUEST['start_AMPM']);
$expireMonth = sanitize_string($_REQUEST['expireMonth']);
$expireDay = sanitize_string($_REQUEST['expireDay']);
$expireYear = sanitize_string($_REQUEST['expireYear']);
$expireHour = sanitize_string($_REQUEST['expireHour']);
$expireMinute = sanitize_string($_REQUEST['expireMinute']);
$expire_AMPM = sanitize_string($_REQUEST['expire_AMPM']);
$documentBody = sanitize_string($_REQUEST['documentBody']);
$summaryImage = sanitize_string($_REQUEST['summaryImage']);
$summary = sanitize_string($_REQUEST['summary']);
$summaryLinkText = sanitize_string($_REQUEST['summaryLinkText']);
$customHeader = sanitize_string($_REQUEST['customHeader']);
$showComments = sanitize_string($_REQUEST['showComments']);

$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);

//build error messages if any of the following fields are empty
if (trim($category) == "") {$error = 1; $errorMessage .= "- Please select a category.<br>";}
if (!$userGroup->allowEditing()) {$error = 1; $errorMessage .= "- Invalid category selection.<br>";}
if (trim($subcategory) == "") {$error = 1; $errorMessage .= "- Please select a subcategory.<br>";}
if (trim($subject) == "") {$error = 1; $errorMessage .= "- Please select a subject.<br>";}
if (trim($title) == "") {$error = 1; $errorMessage .= "- Please enter a title.<br>";}
if (trim($summary) == "") {$error = 1; $errorMessage .= "- Please enter a document summary.<br>";}
if (trim($summaryLinkText) == "") {$error = 1; $errorMessage .= "- Please enter summary link text.<br>";}
if (trim($customHeader) == "") {$error = 1; $errorMessage .= "- Please supply a header.<br>";}
if (trim($documentBody) == "") {$error = 1; $errorMessage .= "- Please enter text in the body of your document.<br>";}

//verify date and time
if ($startMonth < 1 || $startDay < 1 || $startYear < 1 || $startHour < 1 || $startHour > 12 || trim($startMinute) == "" || $startMinute < 0 || $startMinute > 59 || trim($start_AMPM) == "") {
	
	$error = 1; $errorMessage .= "- Please provide a valid start date and time.<br>";
	
}

if ($expireMonth < 1 || $expireDay < 1 || $expireYear < 1 || $expireHour < 1 || $expireHour > 12 || trim($expireMinute) == "" || $expireMinute < 0 || $expireMinute > 59 || trim($expire_AMPM) == "") {
	
	$error = 1; $errorMessage .= "- Please provide a valid expiration date and time.<br>";
	
}

if (($startMonth > 0 && $startDay > 0 && $startYear > 0 && $startHour > 0 && trim($startMinute) != "" && trim($start_AMPM) != "") && ($expireMonth > 0 && $expireDay > 0 && $expireYear > 0 && $expireHour > 0 && trim($expireMinute) != "" && trim($expire_AMPM) != "") && strtotime("$startMonth/$startDay/$startYear $startHour:$startMinute $start_AMPM") > strtotime("$expireMonth/$expireDay/$expireYear $expireHour:$expireMinute $expire_AMPM")) {
	
	$error = 1; $errorMessage .= "- Event expiration date and time must occur after the start date and time.<br>";
	
}

//if an error has occurred, perform the following:
if ($error == 1) {
	
	//build main error container
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;

// if no error occurred, handle the data:	
} else {
	
	//define showComments if it's empty
	if (trim($showComments) == "") {$showComments = "0";}
	
	$start_hour = date("H", strtotime("$startHour $start_AMPM"));
	$expire_hour = date("H", strtotime("$expireHour $expire_AMPM"));

	$start_date = "$startYear-$startMonth-$startDay $start_hour:$startMinute:00";
	$expire_date = "$expireYear-$expireMonth-$expireDay $expire_hour:$expireMinute:00";
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	//if this is a new event, do an insert
	if (trim($id) == "") {

		$matchRows = mysql_result(mysql_query("SELECT COUNT(1) AS NumRows FROM events WHERE startDate = '{$start_date}' AND title = '{$title}'"), 0, "NumRows");

		if ($matchRows > 0) {
			
			header('Content-type: application/javascript');
			print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- An event with this title already exists on this date and time.</div>');";
			print "$('#message_box').show();";
			exit;
			
		}
		
		//populate the database
		$result = mysql_query("INSERT INTO events (usernameCreated, category, subcategory, subject, dateCreated, publishState, customHeader, title, startDate, expireDate, body, summaryImage, summary, summaryLinkText, showComments) VALUES ('{$_SESSION['username']}', '{$category}', '{$subcategory}', '{$subject}', '{$time}', 'Unpublished', '{$customHeader}', '{$title}', '{$start_date}', '{$expire_date}', '{$documentBody}', '{$summaryImage}', '{$summary}', '{$summaryLinkText}', '{$showComments}')");
		
		//grab the id for this new event
		$id = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
		
		if (!$result) {
			
			//build main error container
			header('Content-type: application/javascript');
			print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.</div>');";
			print "$('#message_box').show();";
			exit;
			
		} else {
			
			//grab current version body
			$result = mysql_query("SELECT body FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'event' ORDER BY version DESC LIMIT 1");
			$row = mysql_fetch_object($result);
			
			//check if last version is the same as submitted version. if not, create versioning
			if ($documentBody != sanitize_string($row->body)) {
				
				//create versioning
				$result = mysql_query("INSERT INTO documentVersioning (parentId, documentType, version, dateCreated, title, body, usernameCreated) VALUES ('{$id}', 'event', '1', '{$time}', '{$title}', '{$documentBody}', '{$_SESSION['username']}')");

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

					include("assets/core/config/notifications/add_event/notification.php");
					
					$to = $row->email;
					
					$messageEmail = "<html>";
					$messageEmail .= "<body>";
					$messageEmail .= $message;
					$messageEmail .= "</body>";
					$messageEmail .= "</html>";
					
					$headers = "MIME-Version: 1.0\r\n"; 
					$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
					$headers .= "From: " . $config->readValue('siteEmailAddress') . "\r\n";
					$headers .= "Reply-To: " . $config->readValue('siteEmailAddress') . "\r\n";
					
					mail($to, $subject, $messageEmail, $headers);
					
				}				
				
			}
			
			//jump to the event
			header('Content-type: application/javascript');
			print "window.location = '/events/id/$id';";
			exit;
						
		}
		
	//if this is an update to an existing event, do an update	
	} else {
		
		//set document editing tracker
		$result = mysql_query("DELETE FROM documentEditTracking WHERE documentType = 'event' AND id = '{$id}' AND username = '{$_SESSION['username']}'");
		
		$result = mysql_query("SELECT publishState FROM events WHERE id = '{$id}'");
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
		$result = mysql_query("UPDATE events SET usernameUpdated = '{$_SESSION['username']}', category = '{$category}', subcategory = '{$subcategory}', subject = '{$subject}', dateUpdated = '{$time}', customHeader = '{$customHeader}', title = '{$title}', startDate = '{$start_date}', expireDate = '{$expire_date}', body = '{$documentBody}', summaryImage = '{$summaryImage}', summary = '{$summary}', summaryLinkText = '{$summaryLinkText}', showComments = '{$showComments}' WHERE id = '{$id}'");
		
		if (!$result) {

			//build main error container
			header('Content-type: application/javascript');
			print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- System error. Unable to save your document.</div>');";
			print "$('#message_box').show();";
			exit;

		} else {
			
			//clear the editing history for this user
			mysql_query("DELETE FROM documentEditTracking WHERE documentType = 'event' AND id = '{$id}' AND username = '{$_SESSION['username']}'");
			
			//grab current version body
			$result = mysql_query("SELECT body FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'event' ORDER BY version DESC LIMIT 1");
			$row = mysql_fetch_object($result);
			
			//check if last version is the same as submitted version. if not, create versioning
			if ($documentBody !== sanitize_string($row->body)) {
				
				//create versioning
				$result = mysql_query("SELECT parentId FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'event'");
				$versionNumber = mysql_num_rows($result) + 1;
				
				$result = mysql_query("INSERT INTO documentVersioning (parentId, documentType, version, dateCreated, title, body, usernameCreated) VALUES ('{$id}', 'event', '{$versionNumber}', '{$time}', '{$title}', '{$documentBody}', '{$_SESSION['username']}')");
				
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
						
			//jump to the event
			header('Content-type: application/javascript');
			print "window.location = '/events/id/$id';";
			exit;
			
		}
		
	}
	
}
	
?>