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
include("assets/core/config/part_profile_variables.php");

//read config file and grab the allowed profile fields
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

$pieces = explode(",", $config->readValue('profileFieldsEnabled'));

foreach($pieces as $value) {
	
	$profileField[$value] = 'true';
	
}

$result = mysql_query("SELECT *, EXTRACT(YEAR FROM dateOfBirth) AS birthYear, EXTRACT(MONTH FROM dateOfBirth) AS birthMonth, EXTRACT(DAY FROM dateOfBirth) AS birthDay FROM users WHERE username = '{$_SESSION['username']}' LIMIT 1");

if (mysql_num_rows($result) == 0) {
	
	exit;
	
}

$row = mysql_fetch_object($result);
$username = $row->username;
$email = htmlentities($row->email);
$imageUrl = htmlentities($row->imageUrl);
$name = htmlentities($row->name);
$company = htmlentities($row->company);
$profession = htmlentities($row->profession);

if ($row->birthMonth > 0) {$birthMonth = $row->birthMonth;}
if ($row->birthDay > 0) {$birthDay = $row->birthDay;}
if ($row->birthYear > 0) {$birthYear = $row->birthYear;}

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
$city = htmlentities($row->city);
$state = htmlentities($row->state);
$zip = htmlentities($row->zip);
$country = showOptions($countryOptions, $row->country);
$profileSummary = htmlentities($row->profileSummary);
$interests = htmlentities($row->interests);

if ($row->showName == 1) {$showNameChecked = " checked";}
if ($row->showAge == 1) {$showAgeChecked = " checked";}
if ($row->allowEmailNotifications == 1) {$showAllowEmailNotifications = " checked";}
if ($row->commentsFromFriendsOnly == 1) {$showCommentsFromFriendsOnly = " checked";}

if (trim($row->imageUrl) != "") {
	
	$showImage = "<div id=\"profile_image\"><a href=\"showUserGallery.php?username=$username\"><img src=\"/file.php?load=$row->imageUrl&w=270\" border=\"0\"></a></div>";
	
} else {
	
	//this message had to be updated in ajaxUpdateProfile.php if it is changed here
	$showImage = "<div id=\"profile_image\"><div class=\"profile_image_note\">To add an image to your profile, click the <i>Account</i> tab and upload an image (or choose from an existing image, if you've already uploaded images) by clicking <i>Browse</i> at the end of the <i>Image</i> field.</div></div>";
	
}

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/profileEditor.css");
@import url("/assets/core/resources/css/main/dateSelectCalendar.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/dateSelectCalendar.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/profileEditor.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div class="subheader_title"><a href="/showProfile.php?username={$_SESSION['username']}">My Profile</a></div>
			<div id="default_profile_photo">
				$showImage
			</div>
			<div id="profile_info">
				<form id="profileEditorForm" name="profileEditorForm" action="ajaxUpdateProfile.php" method="post" enctype="multipart/form-data">
				<div id="tabs">
					<ul id="profile_tabs">
						<li><a href="#tab_account">Account</a></li>
						<li><a href="#tab_personal">Details</a></li>
						<li><a href="#tab_location">Location</a></li>
						<li><a href="#tab_about">About</a></li>
					</ul>
					<div id="tab_account" class="ui-tabs-hide">
					<table border="0" cellspacing="0" cellpadding="2" class="tab_panel_table">
					<tr valign="center"><td nowrap>Username:</td><td width="100%">$username</td></tr>
					<tr valign="center"><td class="password" nowrap>Password:</td><td class="password" width="100%"><input type="password" id="password" name="password" size="16"> To change your current password, enter a new password here.</td></tr>
					<tr valign="center"><td class="password" nowrap>Confirm:</td><td class="password" width="100%"><input type="password" id="confirmPassword" name="confirmPassword" size="16"> When changing your password, re-enter your new password here.</td></tr>
					<tr valign="center"><td nowrap>E-mail:</td><td width="100%"><input type="text" id="email" name="email" size="32" value="$email"> <input type="checkbox" id="allowEmailNotifications" name="allowEmailNotifications" value="1"$showAllowEmailNotifications> Allow E-mail Notifications</td></tr>
					<tr valign="center"><td nowrap>Image:</td><td width="100%"><input style="width:450px;" type="text" id="imageUrl" name="imageUrl" value="$imageUrl"><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'imageUrl');" value="Browse"></td></tr>
					<tr valign="center"><td nowrap>Comments:</td><td width="100%"><input type="checkbox" id="commentsFromFriendsOnly" name="commentsFromFriendsOnly" value="1"$showCommentsFromFriendsOnly> Only friends can comment on my profile</td></tr>
					</table>
					</div>
					
					<div id="tab_personal" class="ui-tabs-hide">
					<table border="0" cellspacing="0" cellpadding="2" class="tab_panel_table">
					<tr valign="center"><td nowrap>Name:</td><td width="100%"><input type="text" id="name" name="name" size="32" value="$name"> <input type="checkbox" id="showName" name="showName" value="1"$showNameChecked> Display Name</td></tr>
					<tr valign="center"><td nowrap>Company:</td><td width="100%"><input type="text" id="company" name="company" size="32" value="$company"></td></tr>
					<tr valign="center"><td nowrap>Profession:</td><td width="100%"><input type="text" id="profession" name="profession" size="32" value="$profession"></td></tr>
					<tr valign="center"><td nowrap>Date of Birth:</td><td width="100%"><input type="text" id="birthMonth" name="birthMonth" size="2" value="$birthMonth"> <input type="text" id="birthDay" name="birthDay" size="2" value="$birthDay"> <input type="text" id="birthYear" name="birthYear" size="4" value="$birthYear"> <span id="date_selector" class="date_selector">mm/dd/yyyy</span> <input type="checkbox" id="showAge" name="showAge" value="1"$showAgeChecked> Display Age</td></tr>
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
					</table>
					</div>
					
					<div id="tab_location" class="ui-tabs-hide">
					<table border="0" cellspacing="0" cellpadding="2" class="tab_panel_table">
					<tr valign="center"><td nowrap>City:</td><td width="100%"><input type="text" id="city" name="city" size="32" value="$city"></td></tr>
					<tr valign="center"><td nowrap>State:</td><td width="100%"><input type="text" id="state" name="state" size="32" value="$state"></td></tr>
					<tr valign="center"><td nowrap>Zip:</td><td width="100%"><input type="text" id="zip" name="zip" size="32" value="$zip"></td></tr>
					<tr valign="center"><td nowrap>Country:</td><td width="100%"><select name="country"><option value=""></option>$country</select></td></tr>
					</table>
					</div>
					
					<div id="tab_about" class="ui-tabs-hide">
					<table border="0" cellspacing="0" cellpadding="2" class="tab_panel_table">
					<tr valign="top"><td nowrap>About:</td><td width="100%"><textarea id="profileSummary" name="profileSummary" rows="8" style="width:99%">$profileSummary</textarea></td></tr>
					<tr valign="top"><td nowrap>Interests:</td><td width="100%"><textarea id="interests" name="interests" rows="8" style="width:99%">$interests</textarea></td></tr>
					</table>
					</div>
				</div>
				
				<div id="submit_container">
					<input type="submit" id="submit" value="Save">
				</div>
				
				</form>
			</div>
			<div id="calendar_container" style="display:none;"></div>
EOF;

$site_container->showSiteContainerBottom();

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

?>