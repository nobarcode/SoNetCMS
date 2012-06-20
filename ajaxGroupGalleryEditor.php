<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$groupId = sanitize_string($_REQUEST['groupId']);

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

changeDirection($s, $d, $groupId);

function changeDirection($s, $d, $groupId) {
	
	$urlUsername = urlencode($_SESSION['username']);
	$max_per_page = 25;
	
	if (trim($s) == "") {
		
		$s = 0;
		
	}
	
	$result = mysql_query("SELECT * FROM imagesGroups WHERE parentId = '{$groupId}' ORDER BY weight");
	$totalRows = mysql_num_rows($result);
	
	$showTotalPages = ceil($totalRows / ($max_per_page + 5));
	
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
	$per_page = $max_per_page + 5;
	
	$result = mysql_query("SELECT * FROM imagesGroups WHERE parentId = '{$groupId}' ORDER BY weight LIMIT $s, $per_page");
	
	//get the total count. minus the extra row
	$count = mysql_num_rows($result) - 5;
	
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, '', $id);
		
	} else {
		
		print "<div id=\"images_list\">";
		
		while ($row = mysql_fetch_object($result)) {
			
			if ($row->inSeriesImage == 0) {$series = "<span id=\"in_series_text_$row->id\"><span class=\"add_to_series\"><a href=\"javascript:toggleInSeries('$row->id');\">Add to Gallery</a></span></span>";} else {$series = "<span id=\"in_series_text_$row->id\"><span class=\"in_series\"><a href=\"javascript:toggleInSeries('$row->id');\">In Gallery</a></span></span>";}
			
			$x++;
			
			if ($x % 5 != 0) {
				
				print "<div id=\"image_$row->id\" class=\"images_container column_separator\">";
				
			} else {
				
				print "<div id=\"image_$row->id\" class=\"images_container\">";
				
			}
			
			print "<div class=\"image\"><img src=\"file.php?load=$row->imageUrl&thumbs=true\" border=\"0\"></div><div class=\"gallery_options\">$series</div><div class=\"edit_options\"><div class=\"edit\"><a href=\"javascript:editImage('$row->id');\">Edit</a></div><div class=\"delete\"><a href=\"javascript:deleteImage('$row->id');\" onClick=\"return confirm('Are you sure you want to delete this image?');\">Delete</a></div></div>";
			print "</div>";
			
		}
		
		print "</div>\n";
		
		print "<div id=\"gallery_navigation\">";
		print "	<div class=\"totals\">$totalRows Images</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		//assign last_s the current value of page start value
		print "<script>last_s = $s;</script>";
		
	}
	
}

?>