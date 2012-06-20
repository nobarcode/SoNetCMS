<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//read config file and determine if group finder requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('findGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	include("part_session_check.php");
	
}

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/groupSearch.css");
@import url("/assets/core/resources/css/main/dateSelectCalendar.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/dateSelectCalendar.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/groupSearch.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div class="subheader_title">Find Groups</div>
			<div class="search_options_container">
				<form id="group_search" method="get" action="ajaxGroupSearch.php">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Name:</td><td width="100%"><input type="text" id="name" name="name" size="32" value="$name"></td></tr>
					<tr valign="center"><td nowrap>Owner:</td><td width="100%"><input type="text" id="owner" name="owner" size="32" value="$owner"></td></tr>
					<tr valign="center"><td nowrap>Established:</td><td width="100%"><input type="text" id="minDateEstMonth" name="minDateEstMonth" size="2" value=""> <input type="text" id="minDateEstDay" name="minDateEstDay" size="2" value=""> <input type="text" id="minDateEstYear" name="minDateEstYear" size="4" value=""> <span id="min_date_est_selector" class="date_selector">mm/dd/yyyy</span> to <input type="text" id="maxDateEstMonth" name="maxDateEstMonth" size="2" value=""> <input type="text" id="maxDateEstDay" name="maxDateEstDay" size="2" value=""> <input type="text" id="maxDateEstYear" name="maxDateEstYear" size="4" value=""> <span id="max_date_est_selector" class="date_selector">mm/dd/yyyy</span></td></tr>
					<tr valign="center"><td nowrap>Members:</td><td width="100%"><input type="text" id="minMembers" name="minMembers" size="4" value="$minMembers"> to <input type="text" id="maxMembers" name="maxMembers" size="4" value="$maxMembers"></td></tr>
					<tr valign="center"><td nowrap>About:</td><td width="100%"><input type="text" id="about" name="about" size="32" value="$about"></td></tr>
					<tr valign="center"><td nowrap>Order By:</td><td width="100%"><input type="radio" name="orderBy" value="name" checked> Name <input type="radio" name="orderBy" value="newest"> Newest <input type="radio" name="orderBy" value="oldest"> Oldest</td></tr>
				</table>
				<div class="form_buttons"><input type="submit" id="submit" value="Search"></div>
				</form>
			</div>
			<div id="group_list"></div>
			<div id="calendar_container" style="display:none;"></div>
EOF;

$site_container->showSiteContainerBottom();

?>