<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$fromUserOrder = sanitize_string($_REQUEST['fromUserOrder']);
$subjectOrder = sanitize_string($_REQUEST['subjectOrder']);
$dateSentOrder = sanitize_string($_REQUEST['dateSentOrder']);
$statusOrder = sanitize_string($_REQUEST['statusOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
}

changeDirection($s, $d, $fromUserOrder, $subjectOrder, $dateSentOrder, $statusOrder, $orderBy, $change);

function changeDirection($s, $d, $fromUserOrder, $subjectOrder, $dateSentOrder, $statusOrder, $orderBy, $change) {
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if ($orderBy == $change) {
		
		if ($change == "fromUser") {if ($fromUserOrder == "desc") {$fromUserOrder = "asc";} else {$fromUserOrder = "desc";} $changeFromUser = "fromUser";}
		if ($change == "subject") {if ($subjectOrder == "desc") {$subjectOrder = "asc";} else {$subjectOrder = "desc";} $changeSubject = "subject";}
		if ($change == "dateSent") {if ($dateSentOrder == "desc") {$dateSentOrder = "asc";} else {$dateSentOrder = "desc";} $changeDateSent = "dateSent";}
		if ($change == "status") {if ($statusOrder == "desc") {$statusOrder = "asc";} else {$statusOrder = "desc";} $changeStatus = "status";}
		
	}
	
	if ($orderBy == "fromUser") {$orderBySQL = "fromUser"; $directionSQL = strtoupper($fromUserOrder); $changeFromUser = "fromUser";}
	if ($orderBy == "subject") {$orderBySQL = "subject"; $directionSQL = strtoupper($subjectOrder); $changeSubject = "subject";}
	if ($orderBy == "dateSent") {$orderBySQL = "dateSent"; $directionSQL = strtoupper($dateSentOrder); $changeDateSent = "dateSent";}
	if ($orderBy == "status") {$orderBySQL = "status"; $directionSQL = strtoupper($statusOrder); $secondaryOrderBySQL = ", dateSent DESC"; $changeStatus = "status";}
	
	$result = mysql_query("SELECT id FROM messages WHERE toUser = '{$_SESSION['username']}'");
	$totalRows = mysql_num_rows($result);

	$showTotalPages = ceil($totalRows / $max_per_page);

	if ($d == "b") {

		$s -= $max_per_page;

		if ($s < 0) {

			$s = 0;

		}

	}

	if ($d == "n") {

		if ($s + $max_per_page < $totalRows) {

			$s += $max_per_page;

		}

	}

	if ($totalRows > 0) {

		$showCurrentPage = floor($s / $max_per_page) + 1;

	} else {

		$showCurrentPage = 0;

	}

	$result = mysql_query("SELECT * FROM messages WHERE toUser = '{$_SESSION['username']}' ORDER BY $orderBySQL $directionSQL$secondaryOrderBySQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, '', $fromUserOrder, $subjectOrder, $dateSentOrder, $statusOrder, $orderBy, $change);

	} else {
		
		print "<form id=\"deleteMultipleMessages\">";
		print "<div class=\"message_main_header_options\">";
		print "<div class=\"message_header_checkbox\"></div>";
		print "<div class=\"message_header_from\"><a href=\"javascript:regenerateList('$s', '', '$fromUserOrder', '$subjectOrder', '$dateSentOrder', '$statusOrder', 'fromUser', '$changeFromUser');\">From</a></div>";
		print "<div class=\"message_header_subject\"><a href=\"javascript:regenerateList('$s', '', '$fromUserOrder', '$subjectOrder', '$dateSentOrder', '$statusOrder', 'subject', '$changeSubject');\">Subject</a></div>";
		print "<div class=\"message_header_date\"><a href=\"javascript:regenerateList('$s', '', '$fromUserOrder', '$subjectOrder', '$dateSentOrder', '$statusOrder', 'dateSent', '$changeDateSent');\">Date</a></div>";
		print "<div class=\"message_header_status\"><a href=\"javascript:regenerateList('$s', '', '$fromUserOrder', '$subjectOrder', '$dateSentOrder', '$statusOrder', 'status', '$changeStatus');\">Status</a></div>";
		print "</div>";
		
		while ($row = mysql_fetch_object($result)) {
			
			$dateSent = date("F j, Y g:i A", $row->dateSent);
			$subject = htmlentities($row->subject);
			
			if ($row->status == "unread") {
				
				$statusClass = "message_container_unread";
				$showStatus = "Unread";
				
			} else {
				
				$statusClass = "message_container_read";
				$showStatus = "Read";
				
			}
			
			print "<div id=\"message_$row->id\" class=\"$statusClass\">";
			print "<div class=\"message_header_checkbox\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"deleteId[]\" name=\"deleteId[]\" value=\"$row->id\"></div>";
			print "<div class=\"message_header_from\"><a href=\"javascript:showMessage('$row->id');\">$row->fromUser</a></div>";
			print "<div class=\"message_header_subject\"><a href=\"javascript:showMessage('$row->id');\">$subject</a></div>";
			print "<div class=\"message_header_date\"><a href=\"javascript:showMessage('$row->id');\">$dateSent</a></div>";
			print "<div class=\"message_header_status\"><span id=\"status_$row->id\">$showStatus</span></div>";
			print "</div>";

		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<div class=\"message_container_read\">";
			print "There are no messages in your inbox.";
			print "</div>";
			
		}
		
		print "<div class=\"message_list_options\">";
		print "<div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#deleteMultipleMessages :checkbox').attr('checked', this.checked);\"></div><div class=\"select_all\">Select All</div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleMessages('$s', '$fromUserOrder', '$subjectOrder', '$dateSentOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete the selected messages?');\">Delete</a></div>";
		print "</div>";
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Messages</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b', '$fromUserOrder', '$subjectOrder', '$dateSentOrder', '$statusOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n', '$fromUserOrder', '$subjectOrder', '$dateSentOrder', '$statusOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
		print "<input type=\"hidden\" id=\"fromUserOrder\" name=\"fromUserOrder\" value=\"$fromUserOrder\">";
		print "<input type=\"hidden\" id=\"subjectOrder\" name=\"subjectOrder\" value=\"$subjectOrder\">";
		print "<input type=\"hidden\" id=\"dateSentOrder\" name=\"dateSentOrder\" value=\"$dateSentOrder\">";
		print "<input type=\"hidden\" id=\"statusOrder\" name=\"statusOrder\" value=\"$statusOrder\">";
		print "<input type=\"hidden\" id=\"orderBy\" name=\"orderBy\" value=\"$orderBy\">";
		print "</form>";
		
	}
	
}

?>