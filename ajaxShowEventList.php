<?php

include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("connectDatabase.inc");

$container = sanitize_string($_REQUEST['container']);
$month = sanitize_string($_REQUEST['month']);
$day = sanitize_string($_REQUEST['day']);
$year = sanitize_string($_REQUEST['year']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);

changeDirection($container, $month, $day, $year, $s, $d);

function changeDirection($container, $month, $day, $year, $s, $d) {
	
	$selectedDateStart = "$year-$month-$day 00:00:00";
	$selectedDateEnd = "$year-$month-$day 23:59:59";
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}	
	
	$result = mysql_query("SELECT startDate FROM eventCalendar WHERE (startDate >= '{$selectedDateStart}' AND startDate <= '{$selectedDateEnd}') OR (startDate <= '{$selectedDateEnd}' AND expireDate >= '{$selectedDateStart}') OR (startDate >= '{$selectedDateStart}' AND expireDate <= '{$selectedDateEnd}')");
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

	$result = mysql_query("SELECT *, DATE_FORMAT(startDate, '%M %d, %Y %h:%i %p') AS newStartDate, DATE_FORMAT(expireDate, '%M %d, %Y %h:%i %p') AS newExpireDate FROM eventCalendar WHERE (startDate >= '{$selectedDateStart}' AND startDate <= '{$selectedDateEnd}') OR (startDate <= '{$selectedDateEnd}' AND expireDate >= '{$selectedDateStart}') OR (startDate >= '{$selectedDateStart}' AND expireDate <= '{$selectedDateEnd}') ORDER BY startDate ASC, title ASC LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);

	if ($count < 1 && $totalRows > 0 && $s > 0) {

		changeDirection($container, $month, $day, $year, $s, 'b');

	} else {
		
		$from_start = date("F jS, Y", strtotime("$month/$day/$year"));
		
		if ($count > 0) {

			print "<div class=\"event_contents\">";
			print "<div class=\"event_list_header\">Events occurring on $from_start:</div>";
			
			while ($row = mysql_fetch_object($result)) {

				$title = htmlentities($row->title);

				$escapeTitle = preg_replace('/\\\/', '\\\\\\', $title);
				$escapeTitle = preg_replace('/\'/', '\\\'', $escapeTitle);
				
				if (trim($row->summary) !== "") {
					
					$summary = htmlentities($row->summary);
					$summary = preg_replace("/\\n/", "<br>", $summary);
					$showSummary = "<br><br>$summary";
					
				} else {
					
					$showSummary = "";
					
				}
				
				print "<div class=\"event_container\">";
				print "$title<br>$row->newStartDate - $row->newExpireDate";
				print "<br><br><a href=\"javascript:editEvent('$row->startDate', '$escapeTitle');\">Edit</a>";
				
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {
					
					print " | <a href=\"javascript:deleteEvent('$container', '$row->startDate', '$escapeTitle', $month, $day, $year, $s);\" onClick=\"return confirm('Are you sure you want to delete this event?')\">Delete</a>";
					
				}
				
				print "</div>";

			}
			
			print "<div id=\"event_list_navigation\">";
			print "	<div class=\"totals\">$totalRows Events Found</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateEventList('$container', $month, $day, $year, $s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateEventList('$container', $month, $day, $year, $s, 'n');\" title=\"Next Results\">Next</a></div></div>";
			print "</div>";
			
			print "</div>";
			
		} else {
			
			print "<div class=\"event_contents\">There are no events occurring on $from_start.</div>";
			
		}
		
	}
	
}

?>

