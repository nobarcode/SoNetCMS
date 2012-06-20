<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//create month selection for event list
$month_option[] = "January";
$month_option[] = "February";
$month_option[] = "March";
$month_option[] = "April";
$month_option[] = "May";
$month_option[] = "June";
$month_option[] = "July";
$month_option[] = "August";
$month_option[] = "September";
$month_option[] = "October";
$month_option[] = "November";
$month_option[] = "December";

//create month and year selection for event list -- default to latest event month and year
$result = mysql_query("SELECT EXTRACT(YEAR FROM startDate) AS year FROM events WHERE 1 = 1 ORDER BY startDate ASC LIMIT 1");
$row = mysql_fetch_object($result);
if (trim($row->year) == "") {
	
	$startYear = date("Y", time());
	
} else {
	
	$startYear = $row->year;
	
}

$result = mysql_query("SELECT EXTRACT(MONTH FROM startDate) AS month, EXTRACT(YEAR FROM startDate) AS year FROM events WHERE 1 = 1 ORDER BY expireDate DESC LIMIT 1");
$row = mysql_fetch_object($result);
if (trim($row->year) == "") {
	
	$endYear = date("Y", time());
	
} else {
	
	$endYear = $row->year;
	
}

for($x = 0; $x < count($month_option); $x++) {
	
	$y = $x + 1;
	
	if ($row->month == $y) {
		
		$showMonthOptions .= "<option value=\"$y\" selected>" . $month_option[$x] . "</option>";
		
	} else {
		
		
		$showMonthOptions .= "<option value=\"$y\">" . $month_option[$x] . "</option>";
		
	}
	
}

for($x = $startYear; $x <= $endYear; $x++) {
	
	if ($endYear == $x) {
	
		$showYearOptions .= "<option value=\"$x\" selected>" . $x . "</option>";
	
	} else {
	
		$showYearOptions .= "<option value=\"$x\">" . $x . "</option>";
		
	}
	
}

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Event Manager</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/eventEditorList.js"></script>

<script language="javascript">
category = '$javascript_loader_category';
subcategory = '$javascript_loader_subcategory';
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/eventEditorList.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title">Event Editor</div>
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div class="editor_query_options">
			<form id="event_list_options">
				<select id="month" name="month" onChange="regenerateList('', '', 'desc', 'desc', 'desc', 'date', '');">
				<option value="">All</option>
				$showMonthOptions
				</select>
				<select id="year" name="year" onChange="regenerateList('', '', 'desc', 'desc', 'desc', 'date', '');">
				<option value="">All</option>
				$showYearOptions
				</select>
			</form>
		</div>
		<div id="event_list"></div>
		<div id="editor_options">
			<a class="button" href="eventEditor.php" onclick="this.blur();"><span>Add Event</span></a>
		</div>
	</div>
</body>
</html>
EOF;

?>