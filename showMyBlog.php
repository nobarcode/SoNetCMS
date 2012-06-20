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

//create month selection for blog list
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

//create start year for blog list selection
$result = mysql_query("SELECT EXTRACT(YEAR FROM dateCreated) AS year FROM blogs WHERE usernameCreated = '{$_SESSION['username']}' ORDER BY dateCreated ASC LIMIT 1");
$row = mysql_fetch_object($result);
if (trim($row->year) == "") {
	
	$startYear = date("Y", time());
	
} else {
	
	$startYear = $row->year;
	
}

//default to the current month
$currentMonth = date("m", time());

//set the end year to the current year
$endYear = date("Y", time());

for($x = 0; $x < count($month_option); $x++) {
	
	$y = $x + 1;
	
	if ($currentMonth == $y) {
		
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

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showMyBlog.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showMyBlog.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div class="subheader_title">My Blogs</div>
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<form id="blog_list_options" class="blog_list_options">
				<select id="month" name="month" onChange="regenerateList('', '', 'desc', 'desc', 'desc', 'date', '');">
				<option value="">All</option>
				$showMonthOptions
				</select>
				<select id="year" name="year" onChange="regenerateList('', '', 'desc', 'desc', 'desc', 'date', '');">
				<option value="">All</option>
				$showYearOptions
				</select>
			</form>
			<div id="blog_list"></div>
			<div id="editor_options">
				<a class="button" href="showMyBlogEditor.php" onclick="this.blur();"><span>Add Blog</span></a>
			</div>
EOF;

$site_container->showSiteContainerBottom();

?>