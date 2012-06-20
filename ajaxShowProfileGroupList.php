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
if (trim($username) == "") {
	
	exit;
}

changeDirection($s, $d, $username);

function changeDirection($s, $d, $username) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showProfile/group_list.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	$result = mysql_query("SELECT groupsMembers.parentId FROM groupsMembers INNER JOIN groups ON groups.id = groupsMembers.parentId WHERE groupsMembers.username = '{$username}'");
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

	$result = mysql_query("SELECT groups.id, groups.name, (SELECT COUNT(username) AS memberCount FROM groupsMembers WHERE parentId = groups.id) AS memberCount FROM groupsMembers INNER JOIN groups ON groups.id = groupsMembers.parentId WHERE groupsMembers.username = '{$username}' AND groupsMembers.status = 'approved' ORDER BY groups.name ASC LIMIT $s, $maxDisplay");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $maxDisplay;
		return changeDirection($s, '');
		
	} else {
		
		//row counter
		$x = 0;
		
		if (mysql_num_rows($result) == 0) {
			
			print "<div class=\"group_container\">";
			print "$username is not a member of any groups yet.";
			print "</div>";
			exit;
			
		}
		
		print "<div class=\"groups_list_left_column\">";
		print "<div class=\"group_header_name\"><b>Name</b></div>";
		
		while ($row = mysql_fetch_object($result)) {
			
			$showName = htmlentities($row->name);
			$showMemberLevel = $memberLevel[$row->memberLevel];
			
			if($x == round($maxDisplay / 2)) {
				
				print "</div>";
				print "<div class=\"groups_list_right_column\">";
				print "<div class=\"group_header_name\"><b>Name</b></div>";
				
			}
			
			print "<div class=\"group_name\"><a href=\"/groups/id/$row->id\">$showName</a></div>";
			
			$x++;
			
		}
		
		print "</div>";
		print "<div id=\"group_list_navigation\">";
		print "	<div class=\"totals\">$totalRows Groups</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateGroupList('$s', 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateGroupList('$s', 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
	}
	
}

?>