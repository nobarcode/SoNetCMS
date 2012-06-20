<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");
include("assets/core/config/part_profile_variables.php");
include("assets/core/config/part_user_levels.php");

$username = sanitize_string($_REQUEST['username']);

$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

//grab the allowed profile fields
$pieces = explode(",", $config->readValue('profileFieldsEnabled'));

foreach($pieces as $value) {
	
	$profileField[$value] = 'true';
	
}

$result = mysql_query("SELECT *, EXTRACT(YEAR FROM dateOfBirth) AS birthYear, EXTRACT(MONTH FROM dateOfBirth) AS birthMonth, EXTRACT(DAY FROM dateOfBirth) AS birthDay, DATE_FORMAT(lastLogin, '%m/%d/%Y %h:%i %p') AS newLastLogin FROM users WHERE username = '{$username}' LIMIT 1");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	$row = mysql_fetch_object($result);
	$lastLogin = $row->newLastLogin;
	$lastIpAddress = htmlentities($row->lastIpAddress);
	$email = htmlentities($row->email);
	$imageUrl = htmlentities($row->imageUrl);
	$name = htmlentities($row->name);
	$company = htmlentities($row->company);
	$profession = htmlentities($row->profession);
	$birthMonth = $row->birthMonth;
	$birthDay = $row->birthDay;
	$birthYear = $row->birthYear;
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
	
	$userLevel = showUserLevel($userLevelOptions, $row->level);
	
	print "<form id=\"edit_user\" method=\"get\" action=\"ajaxUpdateUser.php\">\n";
	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">\n";
	if (trim($row->lastLogin) != 0) {print "<tr valign=\"center\"><td nowrap>Last Login:</td><td width=\"100%\">$lastLogin</td></tr>\n";}
	if (trim($row->lastIpAddress) != "") {print "<tr valign=\"center\"><td nowrap>Last IP Address:</td><td width=\"100%\">$lastIpAddress</td></tr>\n";}
	print "<tr valign=\"center\"><td nowrap>Username:</td><td width=\"100%\"><input type=\"text\" id=\"username\" name=\"username\" size=\"32\" value=\"$row->username\"></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Password:</td><td width=\"100%\"><input type=\"password\" id=\"password\" name=\"password\" size=\"32\"></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Confirm Password:</td><td width=\"100%\"><input type=\"password\" id=\"confirmPassword\" name=\"confirmPassword\" size=\"32\"></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>E-mail Address:</td><td width=\"100%\"><input type=\"text\" id=\"email\" name=\"email\" size=\"32\" value=\"$email\"> <input type=\"checkbox\" id=\"allowEmailNotifications\" name=\"allowEmailNotifications\" value=\"1\"$showAllowEmailNotifications> Allow E-mail Notifications</td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Image:</td><td width=\"100%\"><input style=\"width:600px;\" type=\"text\" id=\"imageUrlEdit\" name=\"imageUrlEdit\" value=\"$imageUrl\"><input style=\"margin-left:5px;\" type=\"button\" onclick=\"openFileManager('selectPath', 'imageUrlEdit');\" value=\"Browse\"></td></tr>";
	print "<tr valign=\"center\"><td nowrap>Name:</td><td><input type=\"text\" id=\"name\" name=\"name\" size=\"32\" value=\"$name\"> <input type=\"checkbox\" id=\"showName\" name=\"showName\" value=\"1\"$showNameChecked> Display Name</td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Company:</td><td width=\"100%\"><input type=\"text\" id=\"company\" name=\"company\" size=\"32\" value=\"$company\"></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Profession:</td><td width=\"100%\"><input type=\"text\" id=\"profession\" name=\"profession\" size=\"32\" value=\"$profession\"></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Date of Birth:</td><td width=\"100%\"><input type=\"text\" id=\"birthMonth\" name=\"birthMonth\" size=\"2\" value=\"$birthMonth\"> <input type=\"text\" id=\"birthDay\" name=\"birthDay\" size=\"2\" value=\"$birthDay\"> <input type=\"text\" id=\"birthYear\" name=\"birthYear\" size=\"4\" value=\"$birthYear\"> <span id=\"date_selector_edit\" class=\"date_selector\">mm/dd/yyyy</span> <input type=\"checkbox\" id=\"showAge\" name=\"showAge\" value=\"1\"$showAgeChecked> Display Age</td></tr>\n";
	
	if ($profileField[race] == 'true') {print "<tr valign=\"center\"><td nowrap>Race:</td><td width=\"100%\"><select name=\"race\"><option value=\"\"></option>$race</select></td></tr>\n";}
	if ($profileField[gender] == 'true') {print "<tr valign=\"center\"><td nowrap>Gender:</td><td width=\"100%\"><select name=\"gender\"><option value=\"\"></option>$gender</select></td></tr>\n";}
	if ($profileField[height] == 'true') {print "<tr valign=\"center\"><td nowrap>Height:</td><td width=\"100%\"><select name=\"heightFeet\"><option value=\"\"></option>$heightFeet</select> <select name=\"heightInches\"><option value=\"\"></option>$heightInches</select></td></tr>";}
	if ($profileField[bodytype] == 'true') {print "<tr valign=\"center\"><td nowrap>Body Type:</td><td width=\"100%\"><select name=\"bodyType\"><option value=\"\"></option>$bodyType</select></td></tr>";}
	if ($profileField[orientation] == 'true') {print "<tr valign=\"center\"><td nowrap>Orientation:</td><td width=\"100%\"><select name=\"orientation\"><option value=\"\"></option>$orientation</select></td></tr>";}
	if ($profileField[religion] == 'true') {print "<tr valign=\"center\"><td nowrap>Religion:</td><td width=\"100%\"><select name=\"religion\"><option value=\"\"></option>$religion</select></td></tr>";}
	if ($profileField[vices] == 'true') {print "<tr valign=\"center\"><td nowrap>Smoke?</td><td width=\"100%\"><select name=\"smoke\"><option value=\"\"></option>$smoke</select></td></tr>";}
	if ($profileField[vices] == 'true') {print "<tr valign=\"center\"><td nowrap>Drink?</td><td width=\"100%\"><select name=\"drink\"><option value=\"\"></option>$drink</select></td></tr>";}
	if ($profileField[herefor] == 'true') {print "<tr valign=\"top\"><td nowrap>Here For:</td><td width=\"100%\">$hereFor</td></tr>";}
	
	print "<tr valign=\"center\"><td nowrap>City:</td><td width=\"100%\"><input type=\"text\" id=\"city\" name=\"city\" size=\"32\" value=\"$city\"></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>State:</td><td width=\"100%\"><input type=\"text\" id=\"state\" name=\"state\" size=\"32\" value=\"$state\"></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Zip Code:</td><td width=\"100%\"><input type=\"text\" id=\"zip\" name=\"zip\" size=\"32\" value=\"$zip\"></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Country:</td><td width=\"100%\"><select name=\"country\"><option value=\"\"></option>$country</select></td></tr>\n";
	print "<tr valign=\"top\"><td nowrap>About You:</td><td width=\"100%\"><textarea id=\"profileSummary\" name=\"profileSummary\" rows=\"8\" style=\"width:99%\">$profileSummary</textarea></td></tr>\n";
	print "<tr valign=\"top\"><td nowrap>Interests:</td><td width=\"100%\"><textarea id=\"interests\" name=\"interests\" rows=\"8\" style=\"width:99%\">$interests</textarea></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>User Level:</td><td width=\"100%\"><select id=\"level\" name=\"level\">$userLevel</select></td></tr>\n";
	print "<tr valign=\"center\"><td colspan=\"2\"><input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"editor_cancel\" value=\"Cancel\"></td></tr>\n";
	print "</table>\n";
	print "<input type=\"hidden\" id=\"oldUsername\" name=\"oldUsername\" value=\"$username\">";
	print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
	print "</form>\n";
	
}

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