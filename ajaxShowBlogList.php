<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$month = sanitize_string($_REQUEST['month']);
$day = sanitize_string($_REQUEST['day']);
$year = sanitize_string($_REQUEST['year']);
$usernameCreated = sanitize_string($_REQUEST['usernameCreated']);

changeDirection($s, $d, $month, $day, $year, $usernameCreated);

function changeDirection($s, $d, $month, $day, $year, $usernameCreated) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showBlog/blog_list.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	$showSummary = $config->readValue('displaySummary');
	//$imageWidth = $config->readValue('maxImageSizeX');
	//$imageHeight = $config->readValue('maxImageSizeY');
	
	$userGroup = new CategoryUserGroupValidator();
	$excludeCategories = $userGroup->viewCategoryExclusionList('blogs');
	
	$showMonth = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if (trim($year) != "" && trim($month) == "" && trim($day) == "") {
		
		$dateFilter = "AND (EXTRACT(YEAR FROM dateCreated) = '{$year}')";
		$showSelectedDate = "$year";
		
	} elseif (trim($year) != "" && trim($month) != "" && trim($day) == "") {
		
		$dateFilter = "AND (EXTRACT(YEAR FROM dateCreated) = '{$year}' AND EXTRACT(MONTH FROM dateCreated) = '{$month}')";
		$showSelectedDate = $showMonth[$month - 1] . " $year";
		
	} else {
		
		$day = sprintf("%02d", $day);
		$selectedDateStart = $year . "-" . $month . "-" . $day . " 00:00:00";
		$selectedDateEnd = $year . "-" . $month . "-" . $day . " 23:59:59";
		$dateFilter = "AND (blogs.dateCreated >= '{$selectedDateStart}' AND blogs.dateCreated <= '{$selectedDateEnd}')";
		$showSelectedDate = $showMonth[$month - 1] . " $day, $year";
		
	}
	
	//if the current user is not a site admin
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4) {
		
		$showUnpublished = " AND ((blogs.publishState = 'Unpublished' AND blogs.usernameCreated = '{$_SESSION['username']}') OR blogs.publishState = 'Published')";
		
	}
	
	$result = mysql_query("SELECT blogs.id FROM blogs INNER JOIN categories ON categories.category = blogs.category WHERE 1 $dateFilter AND blogs.usernameCreated = '$usernameCreated'$showUnpublished$excludeCategories");
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

	$result = mysql_query("SELECT blogs.id, blogs.title, DATE_FORMAT(blogs.dateCreated, '%M %d, %Y %h:%i %p') AS newDateCreated, blogs.summary FROM blogs INNER JOIN categories ON categories.category = blogs.category WHERE 1 $dateFilter AND blogs.usernameCreated = '$usernameCreated'$showUnpublished$excludeCategories ORDER BY blogs.dateCreated DESC LIMIT $s, $maxDisplay");
	$count = mysql_num_rows($result);

	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $maxDisplay;
		return changeDirection($s, '', $month, $day, $year, $usernameCreated);
		
	} else {
		
		print "<div class=\"more_blogs\">";
		print "$totalRows Blogs On: $showSelectedDate";
		print "</div>";
		print "<div id=\"list\">";
		print "<div id=\"list_container\">";
		
		if ($count > 0) {
			
			$x = 0;
			
			while ($row = mysql_fetch_object($result)) {
				
				$x++;
			
				if ($x < $count) {
					
					$style = " blog_item_row_separator";
					
				} else {
					
					$style = "";
					
				}
				
				$title = htmlentities($row->title);
			
				print "	<div class=\"blog_item$style\">\n";
				print "		<div class=\"title\"><a href=\"/blogs/id/$row->id\">$title</a></div>\n";
				print "		<div class=\"date\">$row->newDateCreated</div>\n";
				
				if ($showSummary == "true") {
					
					$summary = preg_replace("/\\n/", "<br>", htmlentities($row->summary));
					
					print "		<div class=\"summary\">\n";
					print "			$summary\n";
					print "		</div>\n";
					
				}
				
				print "	</div>";
			
			}
			
			print "</div>";
			print "</div>";
			
			print "<div id=\"blog_list_navigation\">";
			print "	<div class=\"totals\">Page: $showCurrentPage of $showTotalPages</div><div class=\"navigation\"><div class=\"previous\"><a href=\"javascript:regenerateBlogList('$month', '$day', '$year', '$s', '$b');\">Newer</a></div><div class=\"next\"><a href=\"javascript:regenerateBlogList('$month', '$day', '$year', '$s', 'n');\">Older</a></div></div>";
			print "</div>";
			
		} else {
			
			print "<div class=\"blog_item_content\">No blogs published on this date.</div>";
			print "</div>";
			print "</div>";
			
		}
		
	}
	
}

?>