<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

changeDirection($s, $d);

function changeDirection($s, $d) {
	
	$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	$result = mysql_query("SELECT owner FROM friends WHERE friend = '{$_SESSION['username']}' AND status = 'pending' ORDER BY weight");
	$totalRows = mysql_num_rows($result);

	$showTotalPages = ceil($totalRows / ($max_per_page + 6));

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
	
	//add on an extra row to allow sorting between pages
	$per_page = $max_per_page + 6;
	
	$result = mysql_query("SELECT friends.owner, friends.status, users.imageUrl FROM friends INNER JOIN users on friends.owner = users.username WHERE friends.friend = '{$_SESSION['username']}' AND friends.status = 'pending' ORDER BY friends.weight");
	
	//get the total count. minus the extra row
	$count = mysql_num_rows($result) - 6;
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {

		$s -= $max_per_page;
		return changeDirection($s, '');

	} else {
		
		print "<div id=\"friends_list\">";
		
		while ($row = mysql_fetch_object($result)) {
			
			if(is_file($script_directory . $row->imageUrl)) {
				
				$showImage = "<img src=\"file.php?load=$row->imageUrl&thumbs=true\" border=\"0\">";
				
			} else {
				
				$showImage = "<img style=\"margin-top:17px;\" src=\"/assets/core/resources/images/member_no_image_small.jpg\" border=\"0\">";
				
			}
			
			$x++;
			
			if ($x % 5 != 0) {
				
				print "<div id=\"friend_$row->owner\" class=\"friend_container column_separator\">";
				
			} else {
				
				print "<div id=\"friend_$row->owner\" class=\"friend_container\">";
				
			}
			
			print "<div class=\"image\"><a href=\"/showProfile.php?username=$row->owner\">$showImage</a></div><div class=\"friend_details\"><a href=\"/showProfile.php?username=$row->owner\">$row->owner</a></div><div class=\"friend_options\"><div class=\"approve\"><a href=\"javascript:approveFriend('$row->owner');\" onClick=\"return confirm('Are you sure you want to approve this friend request?');\">Approve</a></div><div class=\"decline\"><a href=\"javascript:declineFriend('$row->owner');\" onClick=\"return confirm('Are you sure you want to decline this friend request?');\">Decline</a></div></div>";
			print "</div>";
			
		}
		
		print "</div>\n";		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Requests</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		//assign last_s the current value of page start value
		print "<script>last_s = $s;</script>";
		
	}
	
}

?>