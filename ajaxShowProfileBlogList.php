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

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

changeDirection($s, $d, $username);

function changeDirection($s, $d, $username) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showProfile/blog_list.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	$showSummary = $config->readValue('displaySummary');
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	$result = mysql_query("SELECT blogs.id FROM blogs INNER JOIN categories ON categories.category = blogs.category WHERE categories.hidden != '1' AND usernameCreated = '{$username}' AND publishState = 'Published'");
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

	$result = mysql_query("SELECT blogs.id, blogs.title, DATE_FORMAT(blogs.dateCreated, '%M %d, %Y %h:%i %p') AS newDateCreated FROM blogs INNER JOIN categories ON categories.category = blogs.category WHERE usernameCreated = '{$username}' AND publishState = 'Published' ORDER BY dateCreated DESC LIMIT $s, $maxDisplay");
	$count = mysql_num_rows($result);

	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $maxDisplay;
		return changeDirection($s, '', $username);
		
	} else {
		
		$x = 0;
		
		if ($totalRows > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				$x++;
			
				if ($x < $count) {
					
					$style = " blog_item_row_separator";
					
				} else {
					
					$style = "";
					
				}
				
				$title = htmlentities($row->title);
				
				print "	<div class=\"blog_item$style\">";
				print "		<div class=\"blog_item_content\">";
				print "			<div class=\"blog_item_title\"><a href=\"/blogs/id/$row->id\">$title</a></div>";
				print "			$row->newDateCreated";
				print "		</div>";
				print "	</div>";

			}
			
			print "<div id=\"blog_list_navigation\">";
			print "	<div class=\"totals\">$totalRows Blogs</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateBlogList('$s', 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateBlogList('$s', 'n');\" title=\"Next Results\">Next</a></div></div>";
			print "</div>";
			
		} else {
			
			print "	<div class=\"blog_item_content\">";
			print "		$username hasn't published any blogs yet.";
			print "	</div>";
			
		}
		
	}
	
}

?>