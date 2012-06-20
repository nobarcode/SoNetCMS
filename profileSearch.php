<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");
include("assets/core/config/part_profile_variables.php");

$username = sanitize_string($_REQUEST['username']);
$email = sanitize_string($_REQUEST['email']);
$name = sanitize_string($_REQUEST['name']);
$city = sanitize_string($_REQUEST['city']);
$state = sanitize_string($_REQUEST['state']);
$zip = sanitize_string($_REQUEST['zip']);
$country = sanitize_string($_REQUEST['country']);
$minAge = sanitize_string($_REQUEST['minAge']);
$maxAge = sanitize_string($_REQUEST['maxAge']);
$race = sanitize_string($_REQUEST['race']);
$gender = sanitize_string($_REQUEST['gender']);
$heightFeet = sanitize_string($_REQUEST['heightFeet']);
$heightInches = sanitize_string($_REQUEST['heightInches']);
$bodyType = sanitize_string($_REQUEST['bodyType']);
$orientation = sanitize_string($_REQUEST['orientation']);
$religion = sanitize_string($_REQUEST['religion']);
$smoke = sanitize_string($_REQUEST['smoke']);
$drink = sanitize_string($_REQUEST['drink']);
$hereFor = sanitize_string($_REQUEST['hereFor']);
$profession = sanitize_string($_REQUEST['profession']);
$company = sanitize_string($_REQUEST['company']);
$about = sanitize_string($_REQUEST['about']);
$interests = sanitize_string($_REQUEST['interests']);

//read config file and determine if people finder requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('findPeopleAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	include("part_session_check.php");
	
}

//grab the allowed profile fields
$pieces = explode(",", $config->readValue('profileFieldsEnabled'));

foreach($pieces as $value) {
	
	$profileField[$value] = 'true';
	
}

$username = htmlentities($username);
$email = htmlentities($email);
$name = htmlentities($name);
$city = htmlentities($city);
$state = htmlentities($state);
$zip = htmlentities($zip);
$country = showOptions($countryOptions, $country);
$minAge = htmlentities($minAge);
$maxAge = htmlentities($maxAge);
$race = showOptions($raceOptions, $race);
$gender = showOptions($genderOptions, $gender);
$heightFeet = showOptions($heightFeetOptions, $heightFeet);
$heightInches = showOptions($heightInchesOptions, $heightInches);
$bodyType = showOptions($bodyTypeOptions, $bodyType);
$orientation = showOptions($orientationOptions, $orientation);
$religion = showOptions($religionOptions, $religion);
$smoke = showOptions($smokeOptions, $smoke);
$drink = showOptions($drinkOptions, $drink);
$hereFor = showCheckboxOptions($hereForOptions, $hereFor);
$profession = htmlentities($profession);
$company = htmlentities($company);
$about = htmlentities($about);
$interests = htmlentities($interests);

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/profileSearch.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/profileSearch.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div class="subheader_title">Find People</div>
EOF;

include("assets/core/layout/profilesearch/layout_profilesearch.php");

print <<< EOF
			<div id="users_list"></div>
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
			
			$separator = " class=\"form_options\"";
			
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