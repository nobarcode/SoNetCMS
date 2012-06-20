<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$username = sanitize_string($_REQUEST['username']);

if (trim($_SESSION['username']) == "" || trim($username) == "") {
	
	exit;
	
}

changeDirection($s, $d, $username);

function changeDirection($s, $d, $username) {
	
	$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showProfile/friends_list.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	$result = mysql_query("SELECT friend FROM friends WHERE owner = '{$username}' AND status = 'approved' ORDER BY weight");
	$totalRows = mysql_num_rows($result);

	$showTotalPages = ceil($totalRows / $maxDisplay);

	if ($d == "b") {

		$s -= $maxDisplay;

		if ($s < 0) {

			$s = 0;

		}

	}

	if ($d == "n") {

		if ($s + $maxDisplay < $totalRows) {

			$s += $maxDisplay;

		}

	}

	if ($totalRows > 0) {

		$showCurrentPage = floor($s / $maxDisplay) + 1;

	} else {

		$showCurrentPage = 0;

	}
	
	$result = mysql_query("SELECT users.id AS friendId, friends.friend, friends.status, users.imageUrl FROM friends INNER JOIN users on friends.friend = users.username WHERE friends.owner = '{$username}' AND friends.status = 'approved' ORDER BY friends.weight ASC LIMIT $s, $maxDisplay");
	
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {

		$s -= $maxDisplay;
		return changeDirection($username, $s, '');

	} else {
		
		$x = 0;
		$count_total_so_far = 0;
		
		while ($row = mysql_fetch_object($result)) {
			
			if(is_file($script_directory . $row->imageUrl)) {
				
				$showImage = "<img src=\"file.php?load=$row->imageUrl&thumbs=true\" border=\"0\">";
				
			} else {
				
				$showImage = "<img style=\"margin-top:17px;\" src=\"/assets/core/resources/images/member_no_image_small.jpg\" border=\"0\">";
				
			}
			
			$x++;
			
			if ($x % 5 != 0) {
				
				print "<div id=\"friend_$row->friendId\" class=\"friend_container friend_column_separator\">";
				
			} else {
				
				print "<div id=\"friend_$row->friendId\" class=\"friend_container\">";
				
			}
			
			print "<div class=\"friend_image\"><a href=\"/showProfile.php?username=$row->friend\">$showImage</a></div><div class=\"friend_details\"><a href=\"/showProfile.php?username=$row->friend\">$row->friend</a></div>";
			print "</div>";
			
		}
		
		print "<div id=\"friend_navigation\">";
		print "	<div class=\"totals\">$totalRows Friends</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateFriendList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateFriendList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		//assign last_s the current value of page start value
		print "<script>last_s = $s;</script>";
		
	}
	
}

?>