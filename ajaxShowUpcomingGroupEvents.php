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

//if this file is updated, the homepage function must also be updated.

//if either session or id are empty, exit
if (trim($groupId) == "") {
	
	exit;
	
}

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	exit;
	
}

print changeDirection($groupId, $s, $d);

function changeDirection($groupId, $s, $d) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showGroup/event_list.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	$showSummary = $config->readValue('displaySummary');
	$w = $config->readValue('maxImageSizeX');
	$h = $config->readValue('maxImageSizeY');
	
	//create user groups validation object
	$userGroup = new CategoryUserGroupValidator();
	$excludeCategories = $userGroup->viewCategoryExclusionList('events');
	
	$todaysDate = getdate();
	
	$month = $todaysDate['mon'];
	$day = $todaysDate['mday'];
	$year = $todaysDate['year'];
	
	$getDate = $todaysDate['year'] . "-" . $todaysDate['mon'] . "-" . $todaysDate['mday'] . " 00:00:00";
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	$result = mysql_query("SELECT startDate FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE events.groupId = '{$groupId}' AND events.startDate >= '{$getDate}' AND events.publishState = 'Published'$excludeCategories AND ((events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.private = '0')) GROUP BY events.id");
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

	$result = mysql_query("SELECT events.id, events.title, events.summary, events.summaryImage, DATE_FORMAT(startDate, '%M %d, %Y %h:%i %p') AS newStartDate, DATE_FORMAT(expireDate, '%M %d, %Y %h:%i %p') AS newExpireDate FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE events.groupId = '{$groupId}' AND events.startDate >= '{$getDate}'$showUnpublished$excludeCategories AND ((events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.private = '0')) GROUP BY events.id ORDER BY startDate ASC, title ASC LIMIT $s, $maxDisplay");
	$count = mysql_num_rows($result);

	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $maxDisplay;
		return changeDirection($groupId, $s, '');
		
	} else {
		
		if ($count > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				$x++;
				
				if ($x < $count) {
					
					$style = " event_item_row_separator";
					
				} else {
					
					$style = "";
					
				}
				
				$title = htmlentities($row->title);
				
				if (trim($row->summaryImage) != "") {
					
					$image = "							<div class=\"summary_image\">\n<a href=\"/events/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&w=$w&h=$h\"></a></div>\n";
					$imageOffsetClass = " image_offset";
					
				} else {
					
					$image = "";
					$imageOffsetClass = "";
					
				}
				
				$return .= "						<div class=\"event_item$style\">\n";
				$return .= "$image";
				$return .= "							<div class=\"details_container$imageOffsetClass\">\n";
				$return .= "								<div class=\"title\"><a href=\"/events/id/$row->id\">$title</a></div>\n";
				$return .= "								<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
				$return .= "									<tr><td class=\"start_date\">$row->newStartDate</td></tr><tr><td class=\"end_date\">$row->newExpireDate</td></tr>\n";
				$return .= "								</table>\n";
				
				if ($showSummary == "true") {
					
					$summary = preg_replace("/\\n/", "<br>", htmlentities($row->summary));
					
					$return .= "							<div class=\"summary\">\n";
					$return .= "								$summary\n";
					$return .= "							</div>\n";
					
				}
				
				$return .= "							</div>\n";
				$return .= "						</div>\n";
				
			}
			
			$return .= "<div id=\"event_list_navigation\">";
			$return .= "	<div class=\"totals\">$totalRows Events</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateEventList('$s', 'b');\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateEventList('$s', 'n');\">Next</a></div></div>";
			$return .= "</div>";
				
		} else {
			
			$return .= "							<div class=\"event_item\">No events are currently scheduled.</div>\n";
			
		}
		
	}
	
	return($return);
	
}

?>