<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$getMonth = sanitize_string($_REQUEST['getMonth']);
$getDay = sanitize_string($_REQUEST['getDay']);
$getYear = sanitize_string($_REQUEST['getYear']);
$hideGroupEvents = sanitize_string($_REQUEST['hideGroupEvents']);

//create user groups validation object
$userGroup = new CategoryUserGroupValidator();
$excludeCategories = $userGroup->viewCategoryExclusionList('events');

//get current date information 
$todaysDate = getdate();

//create limits and wrap-arounds
if (trim($getMonth) == "") {
	
	$getMonth = $todaysDate['mon'];
	
}

if (trim($getYear) == "") {
	
	 $getYear = $todaysDate['year'];
	
}

if ($getMonth > 12) {
	
	$getMonth = 1;
	$getYear++;
	
}

if ($getMonth < 1) {
	
	$getMonth = 12;
	$getYear--;
	
}

if ($getYear > 2037) {
	
	$getYear = $todaysDate['year'];
	
}

if ($getYear < 1920) {
	
	$getYear = $todaysDate['year'];
	
}

//get selected date information
$selectedDate = getdate(mktime(0,0,0,$getMonth,1,$getYear));

//grab the first and last day of the selected month
$firstDay = getdate(mktime(0,0,0,$selectedDate['mon'],1,$selectedDate['year']));
$lastDay  = getdate(mktime(0,0,0,$selectedDate['mon']+1,0,$selectedDate['year']));

//create a date for the query
$getDateStart = $selectedDate['year'] . "-" . $selectedDate['mon'] . "-" . $firstDay['mday'] . " 00:00:00";
$getDateEnd = $selectedDate['year'] . "-" . $selectedDate['mon'] . "-" . $lastDay['mday'] . " 23:59:59";

if ($hideGroupEvents == "1") {
	
	$hideGroupEventsSQL = " AND groupId IS NULL";
	
}

$result = mysql_query("SELECT events.groupId, EXTRACT(DAY FROM events.startDate) AS startDay FROM events WHERE (events.startDate >= '{$getDateStart}' AND events.startDate <= '{$getDateEnd}')$hideGroupEventsSQL$excludeCategories GROUP BY startDay");

while ($row = mysql_fetch_object($result)) {
	
	$event_day[$row->startDay] = countEvents($selectedDate, $row->startDay, $hideGroupEventsSQL, '');
	$event_groupId[$row->startDay] = $row->groupId;
	$event_publishState[$row->startDay] = $row->publishState;
	
}

$prevMonth = $getMonth - 1;
$nextMonth = $getMonth + 1;

$prevYear = $getYear - 1;
$nextYear = $getYear + 1;

print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"calendar_header_main\">";
print "<tr>";	
print "<td class=\"calendar_controls_link\" onClick=\"regenerateCalendar('$getMonth', '$prevYear');\">&#171;</td><td class=\"calendar_controls_link\" onClick=\"regenerateCalendar('$prevMonth', '$getYear');\">&#139;</td><td class=\"calendar_controls_link\" onClick=\"regenerateCalendar('', '');\">x</td><td class=\"calendar_controls_link\" onClick=\"regenerateCalendar('$nextMonth', '$getYear');\">&#155;</td><td class=\"calendar_controls_link\" onClick=\"regenerateCalendar('$getMonth', '$nextYear');\">&#187;</td>";
print "</tr>";
print "<tr>";
print "<td class=\"calendar_header_main_links\" colspan=\"7\"><a href=\"javascript:regenerateEventList(" . $selectedDate['mon'] . ", '', " . $selectedDate['year'] . ", '', '');\">" . $selectedDate['month'] . "</a> <a href=\"javascript:regenerateEventList('', '', " . $selectedDate['year'] . ", '', '');\">" .  $selectedDate['year'] . "</a></td>";
//print "<td class=\"calendar_header_main_links\" colspan=\"7\">" . $selectedDate['month'] . " " .  $selectedDate['year'] . "</td>";
print "</tr>";
print "</table>";

print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"calendar_header_days\">";
print "<tr>";
print "<td class=\"calendar_header_days_text\">Su</td><td class=\"calendar_header_days_text\">Mo</td><td class=\"calendar_header_days_text\">Tu</td><td class=\"calendar_header_days_text\">We</td><td class=\"calendar_header_days_text\">Th</td><td class=\"calendar_header_days_text\">Fr</td><td class=\"calendar_header_days_text\">Sa</td>";
print "</tr>";
print "<tr>";

for($i = 0; $i < $firstDay['wday']; $i++) {
	
	print "<td class=\"blank_day\">&nbsp;</td>";
	
}

$actday = 0;

for($i = $firstDay['wday']; $i <= 6; $i++) {
	
	$actday++;
	
	if ($actday == $todaysDate['mday'] && $todaysDate['mon'] == $selectedDate['mon'] && $todaysDate['year'] == $selectedDate['year']) {
		
		$class = ' class="actday"';
		
	} elseif (trim($event_day[$actday]) != "") {
		
		$class = ' class="event_day"';

	} else {

		$class = ' class="day"';

	}
	
	if (countEvents($selectedDate, $actday, $hideGroupEventsSQL, $excludeCategories) > 0) {
		
		$showEvents = "<div class=\"total_events\">" . countEvents($selectedDate, $actday, $hideGroupEventsSQL, $excludeCategories) . "</div>";
		
	}
	
	print "<td$class onClick=\"regenerateEventList('" . $selectedDate['mon'] . "', '" . $actday . "', '" . $selectedDate['year'] . "', '', '');\">$actday$showEvents</td>";
	
	//reset show event count
	$showEvents = "";
	
}

print "</tr>";

//get how many complete weeks are in the actual month

$fullWeeks = floor(($lastDay['mday'] - $actday) / 7);

for ($i = 0; $i < $fullWeeks; $i++) {
	
	print "<tr>";
	
	for ($j = 0; $j < 7; $j++) {
		
		$actday++;
		
		if ($actday == $todaysDate['mday'] && $todaysDate['mon'] == $selectedDate['mon'] && $todaysDate['year'] == $selectedDate['year']) {

			$class = ' class="actday"';

		} elseif (trim($event_day[$actday]) != "") {
			
			$class = ' class="event_day"';
			
		} else {
			
			$class = ' class="day"';
			
		}
		
		if (countEvents($selectedDate, $actday, $hideGroupEventsSQL, $excludeCategories) > 0) {
			
			$showEvents = "<div class=\"total_events\">" . countEvents($selectedDate, $actday, $hideGroupEventsSQL, $excludeCategories) . "</div>";
			
		}
		
		print "<td$class onClick=\"regenerateEventList('" . $selectedDate['mon'] . "', '" . $actday . "', '" . $selectedDate['year'] . "', '', '');\">$actday$showEvents</td>";

		//reset show event count
		$showEvents = "";
		
	}
	
	print "</tr>";
	
}

//display the remainder of the month

if ($actday < $lastDay['mday']) {
	
	print "<tr>";
	
	for ($i = 0; $i < 7; $i++) {
		
		$actday++;
		
		if ($actday == $todaysDate['mday'] && $todaysDate['mon'] == $selectedDate['mon'] && $todaysDate['year'] == $selectedDate['year']) {
			
			$class = ' class="actday"';
			
		} elseif (trim($event_day[$actday]) != "") {
			
			$class = ' class="event_day"';
			
		} else {
			
			$class = ' class="day"';
			
		}
		
		if (countEvents($selectedDate, $actday, $hideGroupEventsSQL, $excludeCategories) > 0) {
			
			$showEvents = "<div class=\"total_events\">" . countEvents($selectedDate, $actday, $hideGroupEventsSQL, $excludeCategories) . "</div>";
			
		}
		
		if ($actday <= $lastDay['mday']) {
			
			print "<td$class onClick=\"regenerateEventList('" . $selectedDate['mon'] . "', '" . $actday . "', '" . $selectedDate['year'] . "', '', '');\">$actday$showEvents</td>";
			
			//reset show event count
			$showEvents = "";
			
		} else {
			
			print "<td class=\"blank_day\">&nbsp;</td>";
			
		}
		
	}
	
	print "</tr>";
	
}

print "</table>";
print "</div>";

function countEvents($selectedDate, $actday, $hideGroupEventsSQL, $excludeCategories) {
	
	$count = 0;
	
	$getDateStart = $selectedDate['year'] . "-" . $selectedDate['mon'] . "-" . $actday . " 00:00:00";
	$getDateEnd = $selectedDate['year'] . "-" . $selectedDate['mon'] . "-" . $actday . " 23:59:59";
	
	//if the current user is not a site admin
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4) {
		
		$showUnpublished = " AND ((events.groupId IS NOT NULL AND events.publishState = 'Unpublished' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2') AND groupsMembers.status =  'approved') OR events.publishState = 'Published')";
		
	}
	
	$result = mysql_query("SELECT events.id FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE (events.startDate >= '{$getDateStart}' AND events.startDate <= '{$getDateEnd}')$hideGroupEventsSQL$excludeCategories AND ((events.groupId IS NOT NULL AND events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.groupId IS NULL OR (events.groupId IS NOT NULL AND events.private = '0')))$showUnpublished GROUP BY events.id");
	$totalEvents = mysql_num_rows($result);
	
	if ($totalEvents > 0) {
		
		$return = $totalEvents;
		
	} else {
		
		$return = "";
		
	}
	
	return($return);
	
}
	
?>