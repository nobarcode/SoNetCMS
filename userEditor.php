<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");
include("part_update_rootPath_user.php");
include("assets/core/config/part_profile_variables.php");
include("assets/core/config/part_user_levels.php");

$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

//grab the allowed profile fields
$pieces = explode(",", $config->readValue('profileFieldsEnabled'));

foreach($pieces as $value) {
	
	$profileField[$value] = 'true';
	
}

$filterTypeOptions[] = "Username";
$filterTypeOptions[] = "E-mail Address";

$filterTypeOptions[] = "Last Login";
$filterTypeOptions[] = "Last IP Address";

//check if config.properties file requires approvals on new sign ups. if it does, the show the "Status" option in the filter drop down
if ($config->readValue('requireSignUpApproval') == 'true') {
	
	$filterTypeOptions[] = "Status";
	
}

$filterOrderOptions[] = 'Ascending';
$filterOrderOptions[] = 'Descending';

$showFilterTypeOptions= showOptions($filterTypeOptions, "");
$showFilterOrderOptions= showOptions($filterOrderOptions, "");

$country = showOptions($countryOptions, $row->country);
$race = showOptions($raceOptions, $row->race);
$gender = showOptions($genderOptions, $row->gender);
$heightFeet = showOptions($heightFeetOptions, $row->heightFeet);
$heightInches = showOptions($heightInchesOptions, $row->heightInches);
$bodyType = showOptions($bodyTypeOptions, $row->bodyType);
$orientation = showOptions($orientationOptions, $row->orientation);
$religion = showOptions($religionOptions, $row->religion);
$smoke = showOptions($smokeOptions, $row->smoke);
$drink = showOptions($drinkOptions, $row->drink);
$hereFor = showCheckboxOptions($hereForOptions, $row->hereFor);
$userLevel= showUserLevel($userLevelOptions, "");

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>User Manager</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/dateSelectCalendar.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/userEditor.js"></script>

<script language="javascript">
userEditorViewLock = 0;
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/userEditor.css");
@import url("/assets/core/resources/css/admin/dateSelectCalendar.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title">User Manager</div>
		<div id="editor_query_options">
			<form id="query_filter">
				<select id="filterType" name="filterType">$showFilterTypeOptions</select> <input type="text" id="filterValue" name="filterValue"> <select id="filterOrder" name="filterOrder">$showFilterOrderOptions</select> <input type="submit" value="Apply">
			</form>
		</div>
		<div id="users_list"></div>
		<div id="editor_options">
			<a class="button" href="javascript:showAddUser();" onclick="this.blur();"><span>Add User</span></a>
		</div>
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="add_user_container" style="display:none;">
			<div>
				<form id="add_user" method="get" action="ajaxAddUser.php">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Username:</td><td width="100%"><input type="text" id="username" name="username" size="32"></td></tr>
					<tr valign="center"><td nowrap>Password:</td><td width="100%"><input type="password" id="password" name="password" size="32"></td></tr>
					<tr valign="center"><td nowrap>Confirm Password:</td><td width="100%"><input type="password" id="confirmPassword" name="confirmPassword" size="32"></td></tr>
					<tr valign="center"><td nowrap>E-mail:</td><td width="100%"><input type="text" id="email" name="email" size="32"> <input type="checkbox" id="allowEmailNotifications" name="allowEmailNotifications" value="1"> Allow E-mail Notifications</td></tr>
					<tr valign="center"><td nowrap>Image:</td><td width="100%"><input style="width:450px;" type="text" id="imageUrlNew" name="imageUrlNew"><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'imageUrlNew');" value="Browse"></td></tr>
					<tr valign="center"><td nowrap>Name:</td><td width="100%"><input type="text" id="name" name="name" size="32"> <input type="checkbox" id="showName" name="showName" value="1"> Display Name</td></tr>
					<tr valign="center"><td nowrap>Company:</td><td width="100%"><input type="text" id="company" name="company" size="32"></td></tr>
					<tr valign="center"><td nowrap>Profession:</td><td width="100%"><input type="text" id="profession" name="profession" size="32"></td></tr>
					<tr valign="center"><td nowrap>Date of Birth:</td><td width="100%"><input type="text" id="birthMonth" name="birthMonth" size="2"> <input type="text" id="birthDay" name="birthDay" size="2"> <input type="text" id="birthYear" name="birthYear" size="4"> <span id="date_selector_add" class="date_selector">mm/dd/yyyy</span> <input type="checkbox" id="showAge" name="showAge" value="1"> Display Age</td></tr>
EOF;
					if ($profileField[race] == 'true') {print "<tr valign=\"center\"><td nowrap>Race:</td><td width=\"100%\"><select name=\"race\"><option value=\"\"></option>$race</select></td></tr>";}
					if ($profileField[gender] == 'true') {print "<tr valign=\"center\"><td nowrap>Gender:</td><td width=\"100%\"><select name=\"gender\"><option value=\"\"></option>$gender</select></td></tr>";}
					if ($profileField[height] == 'true') {print "<tr valign=\"center\"><td nowrap>Height:</td><td width=\"100%\"><select name=\"heightFeet\"><option value=\"\"></option>$heightFeet</select> <select name=\"heightInches\"><option value=\"\"></option>$heightInches</select></td></tr>";}
					if ($profileField[bodytype] == 'true') {print "<tr valign=\"center\"><td nowrap>Body Type:</td><td width=\"100%\"><select name=\"bodyType\"><option value=\"\"></option>$bodyType</select></td></tr>";}
					if ($profileField[orientation] == 'true') {print "<tr valign=\"center\"><td nowrap>Orientation:</td><td width=\"100%\"><select name=\"orientation\"><option value=\"\"></option>$orientation</select></td></tr>";}
					if ($profileField[religion] == 'true') {print "<tr valign=\"center\"><td nowrap>Religion:</td><td width=\"100%\"><select name=\"religion\"><option value=\"\"></option>$religion</select></td></tr>";}
					if ($profileField[vices] == 'true') {print "<tr valign=\"center\"><td nowrap>Smoke?</td><td width=\"100%\"><select name=\"smoke\"><option value=\"\"></option>$smoke</select></td></tr>";}
					if ($profileField[vices] == 'true') {print "<tr valign=\"center\"><td nowrap>Drink?</td><td width=\"100%\"><select name=\"drink\"><option value=\"\"></option>$drink</select></td></tr>";}
					if ($profileField[herefor] == 'true') {print "<tr valign=\"top\"><td nowrap>Here For:</td><td width=\"100%\">$hereFor</td></tr>";}
print <<< EOF
					<tr valign="center"><td nowrap>City:</td><td width="100%"><input type="text" id="city" name="city" size="32"></td></tr>
					<tr valign="center"><td nowrap>State:</td><td width="100%"><input type="text" id="state" name="state" size="32"></td></tr>
					<tr valign="center"><td nowrap>Zip:</td><td width="100%"><input type="text" id="zip" name="zip" size="32"></td></tr>
					<tr valign="center"><td nowrap>Country:</td><td width="100%"><select name="country"><option value=""></option>$country</select></td></tr>
					<tr valign="top"><td nowrap>About You:</td><td width="100%"><textarea id="profileSummary" name="profileSummary" rows="8" style="width:99%"></textarea></td></tr>
					<tr valign="top"><td nowrap>Interests:</td><td width="100%"><textarea id="interests" name="interests" rows="8" style="width:99%"></textarea></td></tr>
					<tr valign="center"><td nowrap>User Level:</td><td width="100%"><select id="level" name="level">$userLevel</select></td></tr>
					<tr valign="center"><td colspan="2"><input type="submit" id="submit" value="Save"></td></tr>
				</table>
				</form>
			</div>	
		</div>
		<div id="calendar_container" style="display:none;"></div>
	</div>
</body>
</html>
EOF;

function showOptions($options, $selected) {
	
	for($x = 0; $x < count($options); $x++) {
		
		if ($selected == $options[$x]) {
			
			$return .= "<option value=\"" . $options[$x] . "\" selected>" . htmlentities($options[$x]) . "</option>";
			
		} else {
			
			$return .= "<option value=\"" . $options[$x] . "\">" . htmlentities($options[$x]) . "</option>";
			
		}
		
	}
	
	return($return);
		
}

function showCheckboxOptions($options, $selected) {
	
	for($x = 0; $x < count($options); $x++) {
		
		if ($x < count($options)-1) {
			
			$separator = " class=\"looking_for_options\"";
			
		} else {
			
			$separator = "";
			
		}
		
		if (preg_match("/<" . $x . ">/is", $selected)) {
			
			$return .= "<div$separator><input type=\"checkbox\" name=\"hereFor[]\" value=\"<$x>\" checked> " . htmlentities($options[$x]) . "</div>";
			
		} else {
			
			$return .= "<div$separator><input type=\"checkbox\" name=\"hereFor[]\" value=\"<$x>\"> " . htmlentities($options[$x]) . "</div>";
			
		}
		
	}
	
	return($return);
		
}

function showUserLevel($userLevelOptions, $level) {
	
	for($x = 0; $x < count($userLevelOptions); $x++) {
		
		if ($level == $x) {
			
			$showUserLevel .= "<option value=\"$x\" selected>" . $userLevelOptions[$x] . "</option>";
			
		} else {
			
			$showUserLevel .= "<option value=\"$x\">" . $userLevelOptions[$x] . "</option>";
			
		}
		
	}
	
	return($showUserLevel);
		
}

?>