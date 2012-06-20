<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$month = sanitize_string($_REQUEST['month']);
$day = sanitize_string($_REQUEST['day']);
$year = sanitize_string($_REQUEST['year']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$hideGroupEvents = sanitize_string($_REQUEST['hideGroupEvents']);

changeDirection($month, $day, $year, $s, $d, $hideGroupEvents);

function changeDirection($month, $day, $year, $s, $d, $hideGroupEvents) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showEventCalendar/event_list.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	$showSummary = $config->readValue('displaySummary');
	$imageWidth = $config->readValue('maxImageSizeX');
	$imageHeight = $config->readValue('maxImageSizeY');
	
	//create user groups validation object
	$userGroup = new CategoryUserGroupValidator();
	$excludeCategories = $userGroup->viewCategoryExclusionList('events');
	
	$showMonth = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if (trim($year) != "" && trim($month) == "" && trim($day) == "") {
		
		$selectedDateStart = $year . "-01-01 00:00:00";
		$selectedDateEnd = $year . "-12-31 23:59:59";
		$dateFilter = " AND ((startDate >= '{$selectedDateStart}' AND startDate <= '{$selectedDateEnd}') OR (startDate <= '{$selectedDateEnd}' AND expireDate >= '{$selectedDateStart}') OR (startDate >= '{$selectedDateStart}' AND expireDate <= '{$selectedDateEnd}'))";
		$showSelectedDate = "$year";
		
	} elseif (trim($year) != "" && trim($month) != "" && trim($day) == "") {
				
		$lastday = date('t',strtotime('$month/$day/$year'));
		$lastday = sprintf("%02d", $lastday);
		$selectedDateStart = $year . "-" . $month . "-01 00:00:00";
		$selectedDateEnd = $year . "-" . $month . "-" . $lastday . " 23:59:59";
		$dateFilter = " AND ((startDate >= '{$selectedDateStart}' AND startDate <= '{$selectedDateEnd}') OR (startDate <= '{$selectedDateEnd}' AND expireDate >= '{$selectedDateStart}') OR (startDate >= '{$selectedDateStart}' AND expireDate <= '{$selectedDateEnd}'))";
		$showSelectedDate = $showMonth[$month - 1] . " $year";
		
	} else {
		
		$day = sprintf("%02d", $day);
		$selectedDateStart = $year . "-" . $month . "-" . $day . " 00:00:00";
		$selectedDateEnd = $year . "-" . $month . "-" . $day . " 23:59:59";
		$dateFilter = " AND ((startDate >= '{$selectedDateStart}' AND startDate <= '{$selectedDateEnd}') OR (startDate <= '{$selectedDateEnd}' AND expireDate >= '{$selectedDateStart}') OR (startDate >= '{$selectedDateStart}' AND expireDate <= '{$selectedDateEnd}'))";
		$showSelectedDate = $showMonth[$month - 1] . " $day, $year";
		
	}	
	
	if ($hideGroupEvents == "1") {
		
		$hideGroupEventsSQL = " AND events.groupId IS NULL";
		
	}
	
	//if the current user is not a site admin
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4) {
		
		$showUnpublished = " AND ((events.groupId IS NOT NULL AND events.publishState = 'Unpublished' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2') AND groupsMembers.status =  'approved') OR events.publishState = 'Published')";
		
	}
	
	$result = mysql_query("SELECT events.id FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE 1$dateFilter$hideGroupEventsSQL$excludeCategories AND ((events.groupId IS NOT NULL AND events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.groupId IS NULL OR (events.groupId IS NOT NULL AND events.private = '0')))$showUnpublished GROUP BY events.id");
	$totalRows = mysql_num_rows($result);

	$showTotalPages = ceil($totalRows / $maxDisplay);

	if ($d == "b") {

		$s -= $maxDisplay;

		if ($s < 0) {

			$s = 0;

		}

	}

	if ($d == "n") {

		if ($s + $maxDisplay < $totalRows) {

			$s += $maxDisplay;

		}

	}

	if ($totalRows > 0) {

		$showCurrentPage = floor($s / $maxDisplay) + 1;

	} else {

		$showCurrentPage = 0;

	}
	
	//do the query again to get the values
	$result = mysql_query("SELECT events.id, events.groupId, events.title, events.summary, events.summaryImage, DATE_FORMAT(events.startDate, '%M %d, %Y %h:%i %p') AS newStartDate, DATE_FORMAT(events.expireDate, '%M %d, %Y %h:%i %p') AS newExpireDate, groups.name FROM events LEFT JOIN groups ON events.groupId = groups.id LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE 1$dateFilter$hideGroupEventsSQL$excludeCategories AND ((events.groupId IS NOT NULL AND events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.groupId IS NULL OR (events.groupId IS NOT NULL AND events.private = '0')))$showUnpublished GROUP BY events.id ORDER BY events.startDate ASC, events.title ASC LIMIT $s, $maxDisplay");
	$count = mysql_num_rows($result);
	
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $maxDisplay;
		return changeDirection($month, $day, $year, $s, '', $hideGroupEvents);

	} else {
		
		$from_start = date("F jS, Y", strtotime("$month/$day/$year"));
		
		print "<div class=\"more_events\">";
		print "$totalRows Events On: $showSelectedDate";
		print "</div>";
		print "<div id=\"list\">";
		print "<div id=\"list_container\">";
		
		if ($count > 0) {
			
			
			
			$x = 0;
			
			while ($row = mysql_fetch_object($result)) {
				
				$x++;
				
				if ($x < $count) {
					
					$style = " event_item_row_separator";
					
				} else {
					
					$style = "";
					
				}
				
				if (trim($row->name) != "") {
					
					$showGroupName = "							<div class=\"group_name\"><a href=\"/groups/id/$row->groupId\">" . htmlentities($row->name) . "</a></div>\n";
					
				} else {
					
					$showGroupName = "";
					
				}
				
				if (trim($row->summaryImage) != "") {
					
					$image = "							<div class=\"summary_image\">\n<a href=\"/events/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&w=$imageWidth&h=$imageHeight\"></a></div>\n";
					$imageOffsetClass = " image_offset";
					
				} else {
					
					$image = "";
					$imageOffsetClass = "";
					
				}
				
				$title = htmlentities($row->title);
				
				print "						<div class=\"event_item$style\">\n";
				print "							<div class=\"details_container\">\n";
				print "								<div class=\"title\"><a href=\"/events/id/$row->id\">$title</a></div>\n";
				print "								<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
				print "									<tr><td class=\"start_date\">$row->newStartDate</td></tr><tr><td class=\"end_date\">$row->newExpireDate</td></tr>\n";
				print "								</table>\n";
				
				if ($showSummary == "true") {
					
					$summary = preg_replace("/\\n/", "<br>", htmlentities($row->summary));
					
					print $image;
					print "						<div class=\"summary$imageOffsetClass\">\n";
					print "							$summary\n";
					print "						</div>\n$showGroupName";
					
				}
				
				print "							</div>\n";					
				print "						</div>\n";
				
			}

			print "</div>";
			print "</div>";
			
			print "<div id=\"event_list_navigation\">";
			print "	<div class=\"totals\">Page: $showCurrentPage of $showTotalPages</div><div class=\"navigation\"><div class=\"previous\"><a href=\"javascript:regenerateEventList('$month', '$day', '$year', $s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateEventList('$month', '$day', '$year', $s, 'n');\" title=\"Next Results\">Next</a></div></div>";
			print "</div>";
			
		} else {
			
			print "<div class=\"event_contents\">No events are occurring on this date.</div>";
			print "</div>";
			print "</div>";
			
		}
		
	}
	
}

?>

