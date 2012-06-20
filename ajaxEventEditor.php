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
$month = sanitize_string($_REQUEST['month']);
$year = sanitize_string($_REQUEST['year']);
$dateOrder = sanitize_string($_REQUEST['dateOrder']);
$titleOrder = sanitize_string($_REQUEST['titleOrder']);
$statusOrder = sanitize_string($_REQUEST['statusOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

changeDirection($s, $d, $month, $year, $dateOrder, $titleOrder, $statusOrder, $orderBy, $change);

function changeDirection($s, $d, $month, $year, $dateOrder, $titleOrder, $statusOrder, $orderBy, $change) {
	
	//create user groups validation object
	$userGroup = new CategoryUserGroupValidator();
	$excludeCategories = $userGroup->editCategoryExclusionList('events');
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if (trim($month) != "") {
		
		$monthQuery = " AND EXTRACT(MONTH FROM startDate) = '$month'";
		
	}
	
	if (trim($year) != "") {
		
		$yearQuery = " AND EXTRACT(YEAR FROM startDate) = '$year'";
		
	}
	
	if ($orderBy == $change) {
		
		if ($change == "date") {if ($dateOrder == "desc") {$dateOrder = "asc";} else {$dateOrder = "desc";} $changeDate = "date";}
		if ($change == "title") {if ($titleOrder == "desc") {$titleOrder = "asc";} else {$titleOrder = "desc";} $changeTitle = "title";}
		if ($change == "status") {if ($statusOrder == "desc") {$statusOrder = "asc";} else {$statusOrder = "desc";} $changeStatus = "status";}
		
	}
	
	if ($orderBy == "date") {$orderBySQL = "startDate"; $directionSQL = strtoupper($dateOrder); $changeDate = "date";}
	if ($orderBy == "title") {$orderBySQL = "title"; $directionSQL = strtoupper($titleOrder); $changeTitle = "title";}
	if ($orderBy == "status") {$orderBySQL = "publishState"; $directionSQL = strtoupper($statusOrder); $changeStatus = "status";}
	
	$result = mysql_query("SELECT id FROM events WHERE groupId IS NULL$monthQuery$yearQuery$excludeCategories");
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

	$result = mysql_query("SELECT events.id, events.groupId, events.title, events.publishState, DATE_FORMAT(events.startDate, '%m/%d/%Y %h:%i %p') AS newStartDate, DATE_FORMAT(events.expireDate, '%m/%d/%Y %h:%i %p') AS newExpireDate, groupsMembers.username FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId AND groupsMembers.username = '{$_SESSION['username']}' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2') AND groupsMembers.status = 'approved' WHERE groupId IS NULL$monthQuery$yearQuery$excludeCategories ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
	
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, '', $month, $year, $dateOrder, $titleOrder, $statusOrder, $orderBy, $change);

	} else {
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			print "<form id=\"multipleEventsAction\">";
			
		}
		
		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		print "<tr>";
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			$colspan = " colspan=\"5\"";
			
			print "<td class=\"document_list_header\" width=\"10\"><div class=\"document_header_checkbox\"></div></td>";
			
		} else {
			
			$colspan = " colspan=\"4\"";
			
		}
		
		print "<td class=\"document_list_header\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$statusOrder', 'date', '$changeDate');\">Date</a></td>";
		print "<td class=\"document_list_header\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$statusOrder', 'title', '$changeTitle');\">Title</a></td>";
		print "<td class=\"document_list_header\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$statusOrder', 'status', '$changeStatus');\">Status</a></td>";
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			print "<td class=\"document_list_header\">&nbsp</td>";
			
		}
		
		print "</tr>";
		
		while ($row = mysql_fetch_object($result)) {
			
			$showTitle = htmlentities($row->title);
			$showStatus = htmlentities($row->publishState);
			
			if ($row->private == 1) {
				
				$showPrivate = "[private] ";
				
			} else {
				
				$showPrivate = "";
				
			}
			
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
			
			//define which document editor options to display
			if ($row->publishState == "Published") {
				
				//validate user access level for published content
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
					
					$showDocumentEditorOptions = "<a href=\"eventEditor.php?id=$row->id\"><img src=\"/assets/core/resources/images/tiny_icon_edit.gif\" border=\"0\"> $showPrivate$showTitle</a>";
					
				} else {
					
					$showDocumentEditorOptions = "<span title=\"$showTitle\">$showPrivate$showTitle</span>";
					
				}
				
			} else {
				
				//display the editor for unpublished content
				$showDocumentEditorOptions = "<a href=\"eventEditor.php?id=$row->id\"><img src=\"/assets/core/resources/images/tiny_icon_edit.gif\" border=\"0\"> $showPrivate$showTitle</a>";
				
			}
			
			print "<tr class=\"document_container_row\">";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
					
					print "<td class=\"document_container\" width=\"10\"><div class=\"event_header_checkbox\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"multipleId[]\" name=\"multipleId[]\" value=\"$row->id\"></div></td>";
					
			}
				
			print "<td class=\"document_container\"><a href=\"/events/id/$row->id\" target=\"_blank\"><img src=\"/assets/core/resources/images/tiny_icon_preview.gif\" border=\"0\"> $row->newStartDate - $row->newExpireDate</a></td>";
			print "<td class=\"document_container\">$showDocumentEditorOptions</td>";
			print "<td class=\"document_container\">$showPublishOptions</td>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print "<td class=\"document_container\"><a href=\"javascript:deleteEvent('$row->id', '$s', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to delete this event?');\"><img src=\"/assets/core/resources/images/tiny_icon_delete.gif\" border=\"0\"> Delete</a></td>";
				
			}
			
			print "</tr>";

		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<tr class=\"document_container_row\">";
			print "<td$colspan class=\"document_container\">There are no events scheduled during the selected date.</td>";
			print "</tr>";
			
		}
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			print "<tr>";
			print "<td class=\"document_list_options\" width=\"10\"><div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#multipleEventsAction :checkbox').attr('checked', this.checked);\"></div></td><td$colspan class=\"document_list_options\"><div class=\"select_all\">Select All</div><div class=\"toggle_selected_state\"><a href=\"javascript:toggleMultipleEventsStatus('$s', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to change the published status of the selected events?');\">Switch Status</a></div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleEvents('$s', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete the selected events?');\">Delete</a></div></td>";
			print "</tr>";
			
		}
		
		print "</table>";
		
		print "<div id=\"document_navigation\">";
		print "	<div class=\"totals\">$totalRows Events</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
			print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
			print "<input type=\"hidden\" id=\"dateOrder\" name=\"dateOrder\" value=\"$dateOrder\">";
			print "<input type=\"hidden\" id=\"titleOrder\" name=\"titleOrder\" value=\"$titleOrder\">";
			print "<input type=\"hidden\" id=\"statusOrder\" name=\"statusOrder\" value=\"$statusOrder\">";
			print "<input type=\"hidden\" id=\"orderBy\" name=\"orderBy\" value=\"$orderBy\">";
			print "</form>";
			
		}
		
	}
	
}

?>