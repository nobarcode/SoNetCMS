<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);

if (trim($groupId) == "") {exit;}

//validate group
$result = mysql_query("SELECT id FROM groups WHERE id = '{$groupId}'");

if (mysql_num_rows($result) == 0) {

	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
		
		print "<b>Resource ID #$groupId Not Found!</b><br><br>The requested resource is not available. There are two possible reasons for this error:<ol><li>The requested resource ID has not yet been assigned to anything.<li>The resource associated to the requested ID has been deleted.</ol>";
		
	}
	
	exit;

}

//validate group and requesting user access rights
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3) {
	
	//if the user is not an admin, validate that the user is allowed to access the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}

}

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
$result = mysql_query("SELECT EXTRACT(YEAR FROM startDate) AS year FROM events WHERE groupId = '{$groupId}' ORDER BY startDate ASC LIMIT 1");
$row = mysql_fetch_object($result);
if (trim($row->year) == "") {
	
	$startYear = date("Y", time());
	
} else {
	
	$startYear = $row->year;
	
}

$result = mysql_query("SELECT EXTRACT(MONTH FROM startDate) AS month, EXTRACT(YEAR FROM startDate) AS year FROM events WHERE groupId = '{$groupId}' ORDER BY expireDate DESC LIMIT 1");
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

//load group name
$result = mysql_query("SELECT name FROM groups WHERE id = '{$groupId}'");
$row = mysql_fetch_object($result);
$groupName = htmlentities($row->name);

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/groupEventEditorList.css");
@import url("/assets/core/resources/css/main/groupAdminOptions.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/groupEventEditorList.js"></script>
<script language="javascript">

groupId = '$groupId';

</script>

EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("part_group_admin_options.php");

print <<< EOF
			<div class="subheader_title">$groupName Event Editor</div>
			<form id="event_list_options" class="event_list_options">
				<select id="month" name="month" onChange="regenerateList('', '', 'desc', 'desc', 'desc', 'date', '');">
				<option value="">All</option>
				$showMonthOptions
				</select>
				<select id="year" name="year" onChange="regenerateList('', '', 'desc', 'desc', 'desc', 'date', '');">
				<option value="">All</option>
				$showYearOptions
				</select>
			</form>
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div id="event_list"></div>
			<div id="editor_options">
				<a class="button" href="groupEventEditor.php?groupId=$groupId" onclick="this.blur();"><span>Add Event</span></a>
			</div>
EOF;

$site_container->showSiteContainerBottom();

?>