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

$username = sanitize_string($_REQUEST['username']);

$result = mysql_query("SELECT users.username, users.imageUrl, users.name, users.email, users.company, users.profession, users.city, users.state, users.zip, users.country, FLOOR(DATEDIFF(CURDATE(), users.dateOfBirth) / 365.25) AS age, users.race, users.gender, users.heightFeet, users.heightInches, users.bodyType, users.orientation, users.religion, users.smoke, users.drink, users.hereFor, users.profileSummary, users.interests, users.showName, users.showAge, users.allowEmailNotifications, users.commentsFromFriendsOnly, DATE_FORMAT(users.dateCreated, '%M %d, %Y %h:%i %p') AS newMemberSince, users.level, users.status, DATE_FORMAT(users.lastLogin, '%M %d, %Y %h:%i %p') AS newLastLogin, users.lastIpAddress, users.lastActive, users.lastAccess FROM users WHERE users.username = '{$username}' AND status != 'pending' LIMIT 1");
	
if (mysql_num_rows($result) == 0) {
	
	$_SESSION['status'] = array('not found', '', $username);
	header("Location: /status.php?type=profile");
	exit;
	
}
	
$row = mysql_fetch_object($result);

function showPersonalDetails($row, $title, $imageSizeX, $imageSizeY) {
	
	include("assets/core/config/part_profile_variables.php");
	
	//read config file and grab the allowed profile fields
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/config.properties');
	
	$pieces = explode(",", $config->readValue('profileFieldsEnabled'));
	
	foreach($pieces as $value) {
		
		$profileField[$value] = 'true';
		
	}
	
	$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	
	if(is_file($script_directory . $row->imageUrl)) {
		
		$showProfilePhoto .= "		<div id=\"default_profile_photo\">\n";
		$showProfilePhoto .= "			<img src=\"/file.php?load=$row->imageUrl&w=$imageSizeX&h=$imageSizeY\" border=\"0\">\n";
		$showProfilePhoto .= "		</div>\n";
		
	}
	
	$memberSince = $row->newMemberSince;
	$lastLogin = strtotime($row->lastLogin);
	$name = htmlentities($row->name);
	$city = htmlentities($row->city);
	$state = htmlentities($row->state);
	$zip = htmlentities($row->zip);
	$country = htmlentities($row->country);
	
	if ($row->age > 0) {
		
		$age = $row->age;
		
	} else {
	
		$age = "---";
		
	}
	
	$race = htmlentities($row->race);
	$gender = htmlentities($row->gender);
	$heightFeet = htmlentities($row->heightFeet);
	$heightInches = htmlentities($row->heightInches);
	$bodyType = htmlentities($row->bodyType);
	$orientation = htmlentities($row->orientation);
	$religion = htmlentities($row->religion);
	$smoke = htmlentities($row->smoke);
	$drink = htmlentities($row->drink);
	
	for($x = 0; $x < count($hereForOptions); $x++) {
		
		if (preg_match("/<" . $x . ">/is", $row->hereFor)) {
			
			$hereFor .= htmlentities($hereForOptions[$x]) . "<br />";
			
		} 
		
	}
	
	$profession = htmlentities($row->profession);
	$company = htmlentities($row->company);
	$showName = $row->showName;
	$showAge = $row->showAge;
	$galleryImages = userGalleryCount($row->username);
	
	//check to see if the user has had any activity (regsitered using the part_sesssion.php file) within the last 20 minutes
	$lastActiveTime = time() - (60 * 20);
	
	if (strtotime($row->lastActive) >= $lastActiveTime) {
		
		$onlineMessage = " <div class=\"active_message\">online</div>";
		
	} else {
		
		$onlineMessage = "<div class=\"inactive_message\">offline</div>";
		
	}
	
	$returnURL = urlencode("showProfile.php?username=$row->username");
	
	$return .="	<div id=\"profile_header\">\n";
	$return .="		<div class=\"header\"><div class=\"username\">$row->username</div>$onlineMessage</div>\n";
	$return .="	</div>\n";
	$return .="\n";	
	$return .="	<div id=\"personal_details_container\">\n";
	$return .="$showProfilePhoto\n";
	$return .="		<div id=\"personal_details\">\n";
	$return .="			<div class=\"personal_details_left\">\n";
	$return .="				<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n";
	
	if ($memberSince != "0000-00-00 00:00") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Member Since</td><td><div class=\"profile_data\">$memberSince</div></td></tr>";}
	if ($lastLogin != "0000-00-00 00:00") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Last Login</td><td><div class=\"profile_data\">$lastLogin</div></td></tr>";}
	if($showName == 1) {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Name</td><td><div class=\"profile_data\">$name</div></td></tr>\n";}
	if($showAge == 1) {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Age</td><td><div class=\"profile_data\">$age</div></td></tr>\n";}
	if ($profileField[race] == 'true' && trim($race) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Race</td><td><div class=\"profile_data\">$race</div></td></tr>";}
	if ($profileField[gender] == 'true' && trim($gender) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Gender</td><td><div class=\"profile_data\">$gender</div></td></tr>";}
	if (trim($city) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">City</td><td><div class=\"profile_data\">$city</div></td>";}
	if (trim($state) != "") {$return .= "		<tr class=\"profile_data_row\"><td class=\"profile_data_label\">State</td><td><div class=\"profile_data\">$state</div></td>";}
	if (trim($zip) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Zip Code</td><td><div class=\"profile_data\">$zip</div></td>";}
	if (trim($country) != "") {$return .= "		<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Country</td><td><div class=\"profile_data\">$country</div></td>";}

	$return .="				</table>\n";
	$return .="			</div>\n";
	$return .="			<div class=\"personal_details_right\">\n";
	$return .="				<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n";
	
	if ($profileField[height] == 'true' && ($heightFeet != 0 || $heightInches != 0)) {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Height</td><td><div class=\"profile_data\">$heightFeet' $heightInches\"</div></td></tr>";}
	if ($profileField[bodytype] == 'true' && trim($bodyType) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Body Type</td><td><div class=\"profile_data\">$bodyType</div></td></tr>";}
	if ($profileField[orientation] == 'true' && trim($orientation) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Orientation</td><td><div class=\"profile_data\">$orientation</div></td></tr>";}
	if ($profileField[religion] == 'true' && trim($religion) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Religion</td><td><div class=\"profile_data\">$religion</div></td></tr>";}
	if ($profileField[vices] == 'true' && trim($smoke) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Smoke?</td><td><div class=\"profile_data\">$smoke</div></td></tr>";}
	if ($profileField[vices] == 'true' && trim($drink) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Drink?</td><td><div class=\"profile_data\">$drink</div></td></tr>";}
	if ($profileField[herefor] == 'true' && trim($hereFor) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\" valign=\"top\">Looking For</td><td><div class=\"profile_data\">$hereFor</div></td></tr>";}
	if (trim($profession) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Profession</td><td><div class=\"profile_data\">$profession</div></td></tr>";}
	if (trim($company) != "") {$return .= "			<tr class=\"profile_data_row\"><td class=\"profile_data_label\">Company</td><td><div class=\"profile_data\">$company</div></td></tr>";}
	
	$return .="				</table>\n";
	$return .="			</div>\n";
	$return .="		</div>\n";
	$return .="	</div>\n";
	$return .="\n";
	$return .="\n";
	$return .="	<div id=\"profile_options_container\">\n";
	$return .="		<div class=\"options\"><div class=\"images\">$galleryImages</div><div class=\"message\"><a href=\"composeMessage.php?toUser=$row->username&returnURL=$returnURL\">Send Message</a></div><div class=\"friend\"><a href=\"javascript:addFriend();\">Add to Friends</a></div></div>\n";
	$return .="	</div>\n";
	$return .="	<div id=\"message_box\" style=\"display:none;\" onClick=\"$(this).hide();\"></div>\n";
	
	return($return);
	
}

function userGalleryCount($username) {
	
	//check for user gallery images
	$result = mysql_query("SELECT parentId FROM imagesUsers WHERE parentId = '{$username}' AND inSeriesImage = 1");
	$galleryImages = mysql_num_rows($result);
	
	if ($galleryImages > 0) {
		
		$return = "<a href=\"/usergalleries/username/$username\">$galleryImages Images in Gallery</a>";
		
	} else {
		
		$return = "0 Images in Gallery";
		
	}
	
	return($return);
	
}

function showAboutMe($row, $title) {
	
	$profileSummary = htmlentities($row->profileSummary);
	$profileSummary = preg_replace("/\\n/", "<br>", $profileSummary);
	
	$return .= "<div id=\"about_me_container\">";
	$return .= "<div class=\"header\">$title</div>";
	
	if (trim($profileSummary) != "") {
		
		$return .= "		<div class=\"body\">$profileSummary</div>";
		
	} else {
		
		$return .= "		<div class=\"body\">$row->username hasn't added anything yet.</div>";
		
	}
	
	$return .= "	</div>";
	
	return($return);
	
}

function showInterests($row, $title) {
	
	$interests = splitter($row->interests);
	$return .= "\n";
	$return .= "	<div id=\"interests_container\">";
	$return .= "		<div class=\"header\">$title</div>";
		
	if (trim($interests) != "") {
		
		$return .= "		<div class=\"body\">$interests</div>";
		
	} else {
	
		$return .= "		<div class=\"body\">$row->username hasn't added anything yet.</div>";
	
	}
	
	$return .= "	</div>";
	
	return($return);
	
}


function showGroups($title) {
	
	$return .= "<div id=\"groups_container\">\n";
	$return .= "	<div class=\"header\">$title</div>\n";
	$return .= "	<div class=\"body\">\n";
	$return .= "		<div id=\"groups_list_container\"></div>\n";
	$return .= "	</div>\n";
	$return .= "</div>\n";
	
	return($return);
	
}

function showFriends($title) {
	
	$return .= "<div id=\"friends_container\">\n";
	$return .= "	<div class=\"header\">My Friends</div>\n";
	$return .= "	<div id=\"friends_list_container\"></div>\n";
	$return .= "</div>\n";
	
	return($return);
	
}

function showBlog($title) {
	
	$return .= "<div id=\"blog_container\">\n";
	$return .= "	<div class=\"header\">My Blog</div>\n";
	$return .= "	<div class=\"body\">\n";
	$return .= "		<div id=\"blog_list_container\"></div>\n";
	$return .= "	</div>\n";
	$return .= "</div>\n";
	
	return($return);
	
}

function showComments($row, $title) {
	
	$return .= "<div id=\"comments_container\">\n";
	$return .= "	<div class=\"header\">\n";
	$return .= "		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n";
	$return .= "			<tr>\n";
	$return .= "				<td>\n";
	$return .= "					<div class=\"comments_section_header\">Comments</div>\n";
	$return .= "				</td>\n";
	$return .= "				<td align=\"right\">\n";
	$return .= "					<form id=\"comment_filter_form\" style=\"margin:0px; padding:0px;\" name=\"comment_filter_form\">\n";
	$return .= "						<select id=\"commentFilter\" name=\"commentFilter\" onChange=\"regenerateCommentsList();\">\n";
	$return .= "							<option value=\"dateOldest\">Date Posted</option>\n";
	$return .= "							<option value=\"dateNewest\">Latest Posts</option>\n";
	$return .= "							<option value=\"scoreHighest\">Voted Highest</option>\n";
	$return .= "							<option value=\"scoreLowest\">Voted Lowest</option>\n";
	$return .= "						</select>\n";
	$return .= "					</form>\n";
	$return .= "				</td>\n";
	$return .= "			</tr>\n";
	$return .= "		</table>\n";
	$return .= "	</div>\n";
	$return .= "	<div class=\"body\">\n";
	$return .= "		<div id=\"comments_list\"></div>\n";
	$return .= "	</div>\n";
	
	$return .= showAddComment($row);
	
	return($return);
	
}

function showAddComment($row) {
	
	//if commentsFromFriendsOnly is enabled and commentor is not a friend, return empty
	if ($row->commentsFromFriendsOnly == '1') {
		
		$result = mysql_query("SELECT owner FROM friends WHERE owner = '{$row->username}' AND friend = '{$_SESSION['username']}' AND status = 'approved'");
		
		if(mysql_num_rows($result) == 0) {
			
			return;
			
		}
		
	}
	
	$return .= "	<div id=\"add_comment_navigation\">\n";
	$return .= "		<a class=\"button\" href=\"javascript:showAddComment();\" onclick=\"this.blur();\"><span>Add Comment</span></a>\n";
	$return .= "	</div>\n";
	$return .= "</div>\n";
	$return .= "\n";
	$return .= "<div class=\"clear_right\"></div>\n";
	$return .= "\n";
	$return .= "<div id=\"comment_message_box\" style=\"display:none;\" onClick=\"$(this).hide();\"></div>\n";
	$return .= "\n";
	$return .= "<div class=\"clear_right\"></div>\n";
	$return .= "\n";
	$return .= "<div id=\"add_comment_container\" style=\"display:none;\">\n";
	$return .= "	<div>\n";
	$return .= "		<div class=\"header\">Add Comment</div>\n";
	$return .= "		<div class=\"body\">\n";
	$return .= "			<form id=\"add_comment\" name=\"add_comment\">\n";
	$return .= "				<textarea id=\"body\" class=\"comment_body_text_area\" name=\"body\" rows=\"8\"></textarea>\n";
	$return .= "				<input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"cancel\" value=\"Cancel\" onClick=\"showAddComment();\">\n";
	$return .= "				<input type=\"hidden\" id=\"parentId\" name=\"parentId\" value=\"$row->username\">\n";
	$return .= "				<input type=\"hidden\" id=\"type\" name=\"type\" value=\"userProfileComment\">\n";
	$return .= "			</form>\n";
	$return .= "		</div>\n";
	$return .= "	</div>\n";
	$return .= "</div>\n";
	
	return($return);
	
}

function splitter($data) {
	
	if (trim($data) != "") {
		
		$data = preg_replace("/\r/", "", $data);
		$keywords = preg_split("/[,;]|\n\s*/", $data);

		for ($x = 0; $x < count($keywords); $x++) {

			$return .= "<a href=\"profileSearch.php?interests=" . urlencode($keywords[$x]) . "\">" . htmlentities($keywords[$x]) . "</a>";

			if ($x < count($keywords)-1) {

				$return .= ", ";

			}

		}
		
	} else {
		
		$return = "";
		
	}
	
	return($return);
	
}

$returnURL = "showProfile.php?username=$row->username";

//update last access date/time
$time = date("Y-m-d H:i:s", time());
mysql_query("UPDATE users SET lastAccess = '{$time}' WHERE username = '{$username}'");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showProfile.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showProfile.js"></script>
<script language="javascript">
username = '$username';
</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("assets/core/layout/showprofile/layout_showprofile.php");

$site_container->showSiteContainerBottom();

?>