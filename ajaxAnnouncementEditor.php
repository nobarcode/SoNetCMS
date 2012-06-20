<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$dateCreatedOrder = sanitize_string($_REQUEST['dateCreatedOrder']);
$dateExpiresOrder = sanitize_string($_REQUEST['dateExpiresOrder']);
$titleOrder = sanitize_string($_REQUEST['titleOrder']);
$statusOrder = sanitize_string($_REQUEST['statusOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
}

changeDirection($s, $d, $dateCreatedOrder, $dateExpiresOrder, $titleOrder, $statusOrder, $orderBy, $change);

function changeDirection($s, $d, $dateCreatedOrder, $dateExpiresOrder, $titleOrder, $statusOrder, $orderBy, $change) {
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if ($orderBy == $change) {
		
		if ($change == "dateCreated") {if ($dateCreatedOrder == "desc") {$dateCreatedOrder = "asc";} else {$dateCreatedOrder = "desc";} $changeDateCreated = "dateCreated";}
		if ($change == "dateExpires") {if ($dateExpiresOrder == "desc") {$dateExpiresOrder = "asc";} else {$dateExpiresOrder = "desc";} $changeDateExpires = "dateExpires";}
		if ($change == "title") {if ($titleOrder == "desc") {$titleOrder = "asc";} else {$titleOrder = "desc";} $changeTitle = "title";}
		if ($change == "status") {if ($statusOrder == "desc") {$statusOrder = "asc";} else {$statusOrder = "desc";} $changeStatus = "status";}
		
	}
	
	if ($orderBy == "dateCreated") {$orderBySQL = "dateCreated"; $directionSQL = strtoupper($dateCreatedOrder); $changeDateCreated = "dateCreated";}
	if ($orderBy == "dateExpires") {$orderBySQL = "dateExpires"; $directionSQL = strtoupper($dateExpiresOrder); $changeDateExpires = "dateExpires";}
	if ($orderBy == "title") {$orderBySQL = "title"; $directionSQL = strtoupper($titleOrder); $changeTitle = "title";}
	if ($orderBy == "status") {$orderBySQL = "publishState"; $directionSQL = strtoupper($statusOrder); $changeStatus = "status";}
	
	$result = mysql_query("SELECT id FROM announcements");
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

	$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i %p') AS newDateCreated, DATE_FORMAT(dateExpires, '%m/%d/%Y %h:%i %p') AS newDateExpires FROM announcements ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, '', $dateCreatedOrder, $dateExpiresOrder, $titleOrder, $statusOrder, $orderBy, $change);
		
	} else {
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			print "<form id=\"multipleAnnouncementsAction\">";
			
		}
		
		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		print "<tr>";
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			$colspan = " colspan=\"6\"";
			
			print "<td class=\"document_list_header\" width=\"10\"><div class=\"announcement_header_checkbox\"></div></td>";
			
		} else {
			
			$colspan = " colspan=\"5\"";
			
		}
		
		print "<td class=\"document_list_header\"><a href=\"javascript:regenerateList('$s', '', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', 'dateCreated', '$changeDateCreated');\">Date Created</a></td>";
		print "<td class=\"document_list_header\"><a href=\"javascript:regenerateList('$s', '', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', 'dateExpires', '$changeDateExpires');\">Expiration Date</a></td>";
		print "<td class=\"document_list_header\"><a href=\"javascript:regenerateList('$s', '', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', 'title', '$changeTitle');\">Title</a></td>";
		print "<td class=\"document_list_header\"><a href=\"javascript:regenerateList('$s', '', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', 'status', '$changeStatus');\">Status</a></td>";
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			print "<td class=\"document_list_header\"></td>";
			
		}
		
		print "</tr>";
		
		while ($row = mysql_fetch_object($result)) {
			
			$title = htmlentities($row->title);
			$showStatus = htmlentities($row->publishState);
			
			//define which publish options to display
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				$showPublishOptions = "<a href=\"javascript:togglePublishState('$row->id');\" onclick=\"return confirm('Are you sure you want to change the publishing status of this document?');\"><span id=\"publish_state_$row->id\">";
			
				if ($row->publishState == "Published") {
					
					$showPublishOptions .= "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_published.gif\" border=\"0\"> $showStatus</span></a>";
					
				} else {
					
					$showPublishOptions .= "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_unpublished.gif\" border=\"0\"> $showStatus</span></a>";
					
				}
				
			} else {
				
				$showPublishOptions = $showStatus;
				
			}
			
			print "<tr class=\"document_container_row\">";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print "<td class=\"document_container\" width=\"10\"><div class=\"announcement_header_checkbox\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"multipleId[]\" name=\"multipleId[]\" value=\"$row->id\"></div></td>";
				
			}
			
			print "<td class=\"document_container\">$row->newDateCreated</td>";
			print "<td class=\"document_container\">$row->newDateExpires</td>";
			print "<td class=\"document_container\"><a href=\"javascript:initEditAnnouncement('$row->id', '$s', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', '$orderBy');\"><img src=\"/assets/core/resources/images/tiny_icon_edit.gif\" border=\"0\"> $title</a></td>";
			print "<td class=\"document_container\">$showPublishOptions</td>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print "<td class=\"document_container\"><a href=\"javascript:deleteAnnouncement('$row->id', '$s', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to delete this announcement?');\"><img src=\"/assets/core/resources/images/tiny_icon_delete.gif\" border=\"0\"> Delete</a></td>";
				
			}
			
			print "</tr>";

		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<tr class=\"document_container_row\">";
			print "<td$colspan class=\"document_container\">There are no announcements available.</td>";
			print "</tr>";
			
		}
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			print "<tr>";
			print "<td class=\"document_list_options\" width=\"10\"><div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#multipleAnnouncementsAction :checkbox').attr('checked', this.checked);\"></div></td><td$colspan class=\"document_list_options\"><div class=\"select_all\">Select All</div><div class=\"toggle_selected_state\"><a href=\"javascript:toggleMultipleAnnouncementsStatus('$s', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to change the published status of the selected announcements?');\">Switch Status</a></div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleAnnouncements('$s', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete the selected announcements?');\">Delete</a></div></td>";
			print "</tr>";
			
		}
		
		print "</table>";
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Announcements</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n', '$dateCreatedOrder', '$dateExpiresOrder', '$titleOrder', '$statusOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
			print "<input type=\"hidden\" id=\"dateCreatedOrder\" name=\"dateCreatedOrder\" value=\"$dateCreatedOrder\">";
			print "<input type=\"hidden\" id=\"dateExpiresOrder\" name=\"dateExpiresOrder\" value=\"$dateExpiresOrder\">";
			print "<input type=\"hidden\" id=\"titleOrder\" name=\"titleOrder\" value=\"$titleOrder\">";
			print "<input type=\"hidden\" id=\"statusOrder\" name=\"statusOrder\" value=\"$statusOrder\">";
			print "<input type=\"hidden\" id=\"orderBy\" name=\"orderBy\" value=\"$orderBy\">";
			print "</form>";
			
		}
		
	}
	
}

?>