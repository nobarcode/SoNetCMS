<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
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
$private = sanitize_string($_REQUEST['private']);

if (trim($groupId) == "") {exit;}

//validate group and requesting user access rights
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4) {

	//if the user is not an admin, validate that the user is allowed to access the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}

}

$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);

//build error messages if any of the following fields are empty
if (trim($category) == "") {$error = 1; $errorMessage .= "- Please select a category.<br>";}
if (!$userGroup->allowEditing()) {$error = 1; $errorMessage .= "- Invalid category selection.<br>";}
if (trim($title) == "") {$error = 1; $errorMessage .= "- Please enter a title.<br>";}
if (trim($summary) == "") {$error = 1; $errorMessage .= "- Please enter a document summary.<br>";}
if (trim($summaryLinkText) == "") {$error = 1; $errorMessage .= "- Please enter summary link text.<br>";}
if (trim($customHeader) == "") {$error = 1; $errorMessage .= "- Please supply a header.<br>";}
if (trim($documentBody) == "") {$error = 1; $errorMessage .= "- Please enter text in the body of your document.<br>";}
if (trim($documentBody) !="" && (preg_match("/[a-z0-9_$]+\((.*?)\)/i", $documentBody) || preg_match("/<script*/i", $documentBody))) {$error = 1; $errorMessage .="- Javascript references are not allowed.<br>";}

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
	$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('$showErrorMessage');";
	print "$('#message_box').show();";
	exit;

// if no error occurred, handle the data:	
} else {
	
	//define showComments if it's empty
	if (trim($showComments) == "") {$showComments = "0";}
	if (trim($private) == "") {$private = "0";}
	
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
			print "$('#message_box').html('<b>There was an error processing your request, please check the following:</b><br>- An event with this title already exists on this date and time.');";
			print "$('#message_box').show();";
			exit;
			
		}
		
		//populate the database
		$result = mysql_query("INSERT INTO events (groupId, usernameCreated, category, subcategory, subject, dateCreated, publishState, customHeader, title, startDate, expireDate, body, summaryImage, summary, summaryLinkText, showComments, private) VALUES ('{$groupId}', '{$_SESSION['username']}', '{$category}', '{$subcategory}', '{$subject}', '{$time}', 'Unpublished', '{$customHeader}', '{$title}', '{$start_date}', '{$expire_date}', '{$documentBody}', '{$summaryImage}', '{$summary}', '{$summaryLinkText}', '{$showComments}', '{$private}')");
		
		//grab the id for this new event
		$id = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
		
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
			
			//jump to the event
			header('Content-type: application/javascript');
			print "window.location = '/events/id/$id';";
			exit;
			
		}
		
	//if this is an update to an existing event, do an update	
	} else {
		
		//populate the database
		$result = mysql_query("UPDATE events SET usernameUpdated = '{$_SESSION['username']}', category = '{$category}', subcategory = '{$subcategory}', subject = '{$subject}', dateUpdated = '{$time}', customHeader = '{$customHeader}', title = '{$title}', startDate = '{$start_date}', expireDate = '{$expire_date}', body = '{$documentBody}', summaryImage = '{$summaryImage}', summary = '{$summary}', summaryLinkText = '{$summaryLinkText}', showComments = '{$showComments}', private = '{$private}' WHERE groupId = '{$groupId}' AND id = '{$id}'");
		
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
						
			//jump to the event
			header('Content-type: application/javascript');
			print "window.location = '/events/id/$id';";
			exit;
			
		}
		
	}
	
}
	
?>