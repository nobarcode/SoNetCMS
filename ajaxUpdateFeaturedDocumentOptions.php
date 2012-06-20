<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$startYear = sanitize_string($_REQUEST['startYear']);
$startMonth = sanitize_string($_REQUEST['startMonth']);
$startDay = sanitize_string($_REQUEST['startDay']);
$startHour = sanitize_string($_REQUEST['startHour']);
$startMinute = sanitize_string($_REQUEST['startMinute']);
$start_AMPM = sanitize_string($_REQUEST['start_AMPM']);
$expireYear = sanitize_string($_REQUEST['expireYear']);
$expireMonth = sanitize_string($_REQUEST['expireMonth']);
$expireDay = sanitize_string($_REQUEST['expireDay']);
$expireHour = sanitize_string($_REQUEST['expireHour']);
$expireMinute = sanitize_string($_REQUEST['expireMinute']);
$expire_AMPM = sanitize_string($_REQUEST['expire_AMPM']);

if (trim($id) == "") {exit;}

if (trim($startHour) != "") {$start_hour = date("H", strtotime("$startHour $start_AMPM"));}
if (trim($expireHour) != "") {$expire_hour = date("H", strtotime("$expireHour $expire_AMPM"));}

if (($startMonth > 0 || $startDay > 0 || $startYear > 0 || $startHour > 0 || trim($startMinute) != "") || ($expireMonth > 0 || $expireDay > 0 || $expireYear > 0 || $expireHour > 0 || trim($expireMinute) != "")) {
	
	if (trim($expire_AMPM) == "") {
		
		$error = 1; $errorMessage .= "- Please provide a valid start date and time.<br>";
		
	}
	
	if (trim($expire_AMPM) == "") {
		
		$error = 1; $errorMessage .= "- Please provide a valid expiration date and time.<br>";
		
	}
	
	//verify date and time
	if ($startMonth < 1 || $startDay < 1 || $startYear < 1 || $startHour < 1 || $startHour > 12 || trim($startMinute) == "" || $startMinute < 0 || $startMinute > 59 || trim($start_AMPM) == "") {
		
		$error = 1; $errorMessage .= "- Please provide a valid start date and time.<br>";
		
	}
	
	if ($expireMonth < 1 || $expireDay < 1 || $expireYear < 1 || $expireHour < 1 || $expireHour > 12 || trim($expireMinute) == "" || $expireMinute < 0 || $expireMinute > 59 || trim($expire_AMPM) == "") {
		
		$error = 1; $errorMessage .= "- Please provide a valid expiration date and time.<br>";
		
	}
	
	if (($startMonth > 0 && $startDay > 0 && $startYear > 0 && $startHour > 0 && trim($startMinute) != "" && trim($start_AMPM) != "") && ($expireMonth > 0 && $expireDay > 0 && $expireYear > 0 && $expireHour > 0 && trim($expireMinute) != "" && trim($expire_AMPM) != "") && strtotime("$startMonth/$startDay/$startYear $startHour:$startMinute $start_AMPM") > strtotime("$expireMonth/$expireDay/$expireYear $expireHour:$expireMinute $expire_AMPM")) {
		
		$error = 1; $errorMessage .= "- Expiration date and time must occur after the start date and time.<br>";
		
	}
	
}

if ($error != 1) {
	
	$start_date = "$startYear-$startMonth-$startDay $start_hour:$startMinute:00";
	$expire_date = "$expireYear-$expireMonth-$expireDay $expire_hour:$expireMinute:00";
	
	$result = mysql_query("UPDATE featuredDocuments SET dateStarts = '{$start_date}', dateExpires = '{$expire_date}' WHERE id = '{$id}'");
	
	if(!$result) {
		
		header('Content-type: application/javascript');
		print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>- Internal error. Please retry your request.</div>');";
		print "$('#message_box').show();";
		exit;
	
	}
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div>Featured document parameters updated successfully.</div>');";
	print "$('#message_box').show();";
	print "regenerateList();";
	print "cancelEditFeaturedDocumentOptions();";
	
} else {
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('<div><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>');";
	print "$('#message_box').show();";
	exit;
	
}

?>