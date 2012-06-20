<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);

if (trim($groupId) == "") {
	
	exit;
	
}

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	exit;
	
}

changeDirection($groupId, $s, $d);

function changeDirection($groupId, $s, $d) {
	
	$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	
	$max_per_page = 10;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}' AND status = 'approved'");
	$totalRows = mysql_num_rows($result);

	$showTotalPages = ceil($totalRows / $max_per_page);

	if ($d == "b") {

		$s -= $max_per_page;

		if ($s < 0) {

			$s = 0;

		}

	}

	if ($d == "n") {

		if ($s + $max_per_page < $totalRows) {

			$s += $max_per_page;

		}

	}

	if ($totalRows > 0) {

		$showCurrentPage = floor($s / $max_per_page) + 1;

	} else {

		$showCurrentPage = 0;

	}
	
	$result = mysql_query("SELECT groupsMembers.username, users.imageUrl FROM groupsMembers INNER JOIN users ON groupsMembers.username = users.username WHERE groupsMembers.parentId = '{$groupId}' AND groupsMembers.status = 'approved' ORDER BY groupsMembers.memberLevel ASC, groupsMembers.dateJoined ASC LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {

		$s -= $max_per_page;
		return changeDirection($groupId, $s, '');

	} else {
		
		$x = 0;
		$count_total_so_far = 0;
		
		while ($row = mysql_fetch_object($result)) {
			
			if(is_file($script_directory . $row->imageUrl)) {
				
				$showImage = "<img src=\"/file.php?load=$row->imageUrl&thumbs=true\" border=\"0\">";
				
			} else {
				
				$showImage = "<img style=\"margin-top:17px;\" src=\"/assets/core/resources/images/member_no_image_small.jpg\" border=\"0\">";
				
			}
			
			$x++;
			
			if ($x % 5 != 0) {
				
				print "<div class=\"member_container member_column_separator\">";
				
			} else {
				
				print "<div class=\"member_container\">";
				
			}
			
			print "<div class=\"member_image\"><a href=\"/showProfile.php?username=$row->username\">$showImage</a></div><div class=\"member_details\"><a href=\"/showProfile.php?username=$row->username\">$row->username</a></div>";
			print "</div>";
			
		}
		
		print "<div id=\"member_navigation\">";
		print "	<div class=\"totals\">$totalRows Members</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateMemberList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateMemberList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		//assign last_s the current value of page start value
		print "<script>last_s = $s;</script>";
		
	}
	
}

?>