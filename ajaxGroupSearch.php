<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$name = sanitize_string($_REQUEST['name']);
$owner = sanitize_string($_REQUEST['owner']);
$minDateEstYear = sanitize_string($_REQUEST['minDateEstYear']);
$minDateEstMonth = sanitize_string($_REQUEST['minDateEstMonth']);
$minDateEstDay = sanitize_string($_REQUEST['minDateEstDay']);
$maxDateEstYear = sanitize_string($_REQUEST['maxDateEstYear']);
$maxDateEstMonth = sanitize_string($_REQUEST['maxDateEstMonth']);
$maxDateEstDay = sanitize_string($_REQUEST['maxDateEstDay']);
$minMembers = sanitize_string($_REQUEST['minMembers']);
$maxMembers = sanitize_string($_REQUEST['maxMembers']);
$about = sanitize_string($_REQUEST['about']);
$orderBy = sanitize_string($_REQUEST['orderBy']);

//read config file and determine if group finder requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('findGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	exit;
	
}

changeDirection($s, $d, $name, $owner, $minDateEstYear, $minDateEstMonth, $minDateEstDay, $maxDateEstYear, $maxDateEstMonth, $maxDateEstDay, $minMembers, $maxMembers, $about, $orderBy);

function changeDirection($s, $d, $name, $owner, $minDateEstYear, $minDateEstMonth, $minDateEstDay, $maxDateEstYear, $maxDateEstMonth, $maxDateEstDay, $minMembers, $maxMembers, $about, $orderBy) {
	
	//validate and define date variables
	if (trim($minDateEstYear) != "" || trim($minDateEstMonth) != "" || trim($minDateEstDay) != "") {
		
		$minDateEstablished = $minDateEstYear . '-' . $minDateEstMonth . '-' . $minDateEstDay . ' 00:00:00';
		
	}
	
	if (trim($maxDateEstYear) != "" || trim($maxDateEstMonth) != "" || trim($maxDateEstDay) != "") {
		
		$maxDateEstablished = $maxDateEstYear . '-' . $maxDateEstMonth . '-' . $maxDateEstDay . ' 23:59:59';
		
	}
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	//setup the query based on supplied user input
	
	//the default if no user input is supplied
	$searchQuery .= " WHERE 1=1";
	
	if (trim($name) != "") {
		
		$searchQuery .= " AND name LIKE '%$name%'";
		
	}
	
	if (trim($owner) != "") {
		
		$searchQuery .= " AND (groupsMembers.username = '$owner' AND groupsMembers.memberLevel = '1')";
		
	}
	
	if (trim($minDateEstablished) != "") {
		
		$searchQuery .= " AND dateCreated >= '$minDateEstablished'";
		
	}
	
	if (trim($maxDateEstablished) != "") {
		
		$searchQuery .= " AND dateCreated <= '$maxDateEstablished'";
		
	}
	
	if (trim($minMembers) != "" || trim($maxMembers) != "") {
		
		$searchQuery .= "  AND (SELECT COUNT(parentId) AS totalMembers FROM groupsMembers WHERE groupsMembers.parentId = groups.id AND groupsMembers.status = 'approved' AND totalMembers >= '$minMembers' AND totalMembers <= '$maxMembers')";
		
	}
	
	if (trim($about) != "") {
		
		$searchQuery .= " AND summary LIKE '%$about%'";
		
	}
	
	//order type
	if ($orderBy == "name") {
		
		$queryOrder = " ORDER BY groups.name ASC";
		
	} elseif ($orderBy == "newest") {
		
		$queryOrder = " ORDER BY groups.dateCreated DESC";
		
	} elseif ($orderBy == "oldest") {
		
		$queryOrder = " ORDER BY groups.dateCreated ASC";
		
	}
	
	$result = mysql_query("SELECT *, (SELECT COUNT(parentId) FROM groupsMembers WHERE groupsMembers.parentId = groups.id AND groupsMembers.status = 'approved') AS totalMembers FROM groups INNER JOIN groupsMembers ON groupsMembers.parentId = groups.id AND memberLevel = '1'$searchQuery");
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

	$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y') AS newDateCreated, (SELECT COUNT(parentId) FROM groupsMembers WHERE groupsMembers.parentId = groups.id AND groupsMembers.status = 'approved') AS totalMembers FROM groups INNER JOIN groupsMembers ON groupsMembers.parentId = groups.id AND memberLevel = '1'$searchQuery$queryOrder");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, $d, $name, $owner, $minDateEstYear, $minDateEstMonth, $minDateEstDay, $maxDateEstYear, $maxDateEstMonth, $maxDateEstDay, $minMembers, $maxMembers, $about, $orderBy);
		
	} else {
		
			if ($count > 0) {
			
			$x = 0;
			
			while ($row = mysql_fetch_object($result)) {
				
				$x++;
				
				if ($x < $count) {
					
					$style = " group_row_separator";
					
				} else {
					
					$style = "";
					
				}
				
				if (trim($row->summaryImage) != "") {
	
					$summaryImage = "			<div class=\"summary_image\"><a href=\"/groups/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&w=100&h=100\" border=\"0\"></a></div>";
	
				} else {
	
					$summaryImage = "			<div class=\"summary_image\"><a href=\"/groups/id/$row->id\"><img src=\"/assets/core/resources/images/group_no_image_small.jpg\" border=\"0\"></a></div>";
	
				}
				
				$showName = htmlentities($row->name);
				$showOwner = htmlentities($row->username);
				
				print "<div class=\"group_container$style\">";
				print "$summaryImage";
				print "		<div class=\"group_info\">";
				print "			<div class=\"group_name\"><a href=\"/groups/id/$row->id\">$showName</a></div>";
				print "			<div class=\"group_stats\">";
				print "				<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
				print "					<tr valign=\"top\"><td class=\"stats_header\">Owner</td><td class=\"stats_data\"><span id=\"owner_container\">$showOwner</span></td></tr>";
				print "					<tr valign=\"top\"><td class=\"stats_header\">Established</td><td class=\"stats_data\">$row->newDateCreated</td></tr>";
				print "					<tr valign=\"top\"><td class=\"stats_header\">Members</td><td class=\"stats_data\"><span id=\"total_members_container\">$row->totalMembers</span></td></tr>";
				print "				</table>";
				print "			</div>";
				print "		</div>";
				print "</div>";
				
			}
			
		} else {
			
			print "<div class=\"group_container\">";
			print "	No groups found. Please refine your search and try again.";
			print "</div>";
			
		}
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Groups Found</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
	}
	
}

?>