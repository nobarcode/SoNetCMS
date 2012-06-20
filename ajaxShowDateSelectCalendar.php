<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$monthFieldName = sanitize_string($_REQUEST['monthFieldName']);
$dayFieldName = sanitize_string($_REQUEST['dayFieldName']);
$yearFieldName = sanitize_string($_REQUEST['yearFieldName']);

$getMonth = sanitize_string($_REQUEST['getMonth']);
$getYear = sanitize_string($_REQUEST['getYear']);


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

$prevMonth = $getMonth - 1;
$nextMonth = $getMonth + 1;

$prevYear = $getYear - 1;
$nextYear = $getYear + 1;


print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"calendar_header_main\">";
print "</tr>";
print "<tr><td colspan=\"7\"><div class=\"close_calendar\" onClick=\"hideSelectorCalendar()\">close</div></td></tr>";
print "</tr>";
print "<tr>";	
print "<td class=\"calendar_controls_link\" onClick=\"regenerateSelectorCalendar($getMonth, $prevYear, '$monthFieldName', '$dayFieldName', '$yearFieldName');\">&#171;</td><td class=\"calendar_controls_link\" onClick=\"regenerateSelectorCalendar($prevMonth, $getYear, '$monthFieldName', '$dayFieldName', '$yearFieldName');\">&#139;</td><td class=\"calendar_controls_link\" onClick=\"regenerateSelectorCalendar('', '', '$monthFieldName', '$dayFieldName', '$yearFieldName');\">x</td><td class=\"calendar_controls_link\" onClick=\"regenerateSelectorCalendar($nextMonth, $getYear, '$monthFieldName', '$dayFieldName', '$yearFieldName');\">&#155;</td><td class=\"calendar_controls_link\" onClick=\"regenerateSelectorCalendar($getMonth, $nextYear, '$monthFieldName', '$dayFieldName', '$yearFieldName');\">&#187;</td>";
print "</tr>";
print "<tr>";
print "<td class=\"calendar_header_main_links\" colspan=\"7\">" . $selectedDate['month'] . " " .  $selectedDate['year'] . "</td>";
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
		
	} else {
		
		$class = ' class="day"';
		
	}
	
	print "<td$class onClick=\"selectDate('$monthFieldName', '$dayFieldName', '$yearFieldName', " . $selectedDate['mon'] . ", " . $actday . ", " . $selectedDate['year'] . ");\">$actday</td>";
	
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
			
		} else {
			
			$class = ' class="day"';
			
		}
		
		print "<td$class onClick=\"selectDate('$monthFieldName', '$dayFieldName', '$yearFieldName', " . $selectedDate['mon'] . ", " . $actday . ", " . $selectedDate['year'] . ");\">$actday</td>";
		
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
			
		} else {
			
			$class = ' class="day"';
			
		}
		
		if ($actday <= $lastDay['mday']) {
			
			print "<td$class onClick=\"selectDate('$monthFieldName', '$dayFieldName', '$yearFieldName', " . $selectedDate['mon'] . ", " . $actday . ", " . $selectedDate['year'] . ");\">$actday</td>";
			
		} else {
			
			print "<td class=\"blank_day\">&nbsp;</td>";
			
		}
		
	}
	
	print "</tr>";
	
}

print "</table>";

?>