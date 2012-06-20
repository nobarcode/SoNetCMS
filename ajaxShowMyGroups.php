<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$nameOrder = sanitize_string($_REQUEST['nameOrder']);
$membersOrder = sanitize_string($_REQUEST['membersOrder']);
$typeOrder = sanitize_string($_REQUEST['typeOrder']);
$levelOrder = sanitize_string($_REQUEST['levelOrder']);
$statusOrder = sanitize_string($_REQUEST['statusOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
}

changeDirection($s, $d, $nameOrder, $membersOrder, $typeOrder, $levelOrder, $statusOrder, $orderBy, $change);

function changeDirection($s, $d, $nameOrder, $membersOrder, $typeOrder, $levelOrder, $statusOrder, $orderBy, $change) {
	
	$memberLevel[1] = "Owner";
	$memberLevel[2] = "Administrator";
	$memberLevel[3] = "Member";
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if ($orderBy == $change) {
		
		if ($change == "name") {if ($nameOrder == "desc") {$nameOrder = "asc";} else {$nameOrder = "desc";} $changeName = "name";}
		if ($change == "members") {if ($membersOrder == "desc") {$membersOrder = "asc";} else {$membersOrder = "desc";} $changeMembers = "members";}
		if ($change == "type") {if ($typeOrder == "desc") {$typeOrder = "asc";} else {$typeOrder = "desc";} $changeType = "type";}
		if ($change == "level") {if ($levelOrder == "desc") {$levelOrder = "asc";} else {$levelOrder = "desc";} $changeLevel = "level";}
		if ($change == "status") {if ($statusOrder == "desc") {$statusOrder = "asc";} else {$statusOrder = "desc";} $changeStatus = "status";}
		
	}
	
	if ($orderBy == "name") {$orderBySQL = "groups.name"; $directionSQL = strtoupper($nameOrder); $changeName = "name";}
	if ($orderBy == "members") {$orderBySQL = "memberCount"; $directionSQL = strtoupper($membersOrder); $changeMembers = "members";}
	if ($orderBy == "type") {$orderBySQL = "groups.exclusiveRequired"; $directionSQL = strtoupper($typeOrder); $changeType = "type";}
	if ($orderBy == "level") {$orderBySQL = "groupsMembers.memberLevel"; $directionSQL = strtoupper($levelOrder); $changeLevel = "level";}
	if ($orderBy == "status") {$orderBySQL = "groupsMembers.status"; $directionSQL = strtoupper($statusOrder); $changeStatus = "status";}
	
	$result = mysql_query("SELECT groupsMembers.parentId FROM groupsMembers INNER JOIN groups ON groups.id = groupsMembers.parentId WHERE groupsMembers.username = '{$_SESSION['username']}'");
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

	$result = mysql_query("SELECT groups.id, groups.name, groups.exclusiveRequired, groupsMembers.memberLevel, groupsMembers.status, (SELECT COUNT(username) AS memberCount FROM groupsMembers WHERE parentId = groups.id) AS memberCount FROM groupsMembers INNER JOIN groups ON groups.id = groupsMembers.parentId WHERE groupsMembers.username = '{$_SESSION['username']}' ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, '', $nameOrder, $membersOrder, $typeOrder, $levelOrder, $statusOrder, $orderBy, $change);
		
	} else {
		
		print "<form id=\"deleteMultipleGroups\">";
		print "<div class=\"group_main_header_options\">";
		print "<div class=\"group_header_checkbox\"></div>";
		print "<div class=\"group_header_name\"><a href=\"javascript:regenerateList('$s', '', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', 'name', '$changeName');\">Group Name</a></div>";
		print "<div class=\"group_header_members\"><a href=\"javascript:regenerateList('$s', '', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', 'members', '$changeMembers');\">Members</a></div>";
		print "<div class=\"group_header_type\"><a href=\"javascript:regenerateList('$s', '', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', 'type', '$changeLevel');\">Group Type</a></div>";
		print "<div class=\"group_header_level\"><a href=\"javascript:regenerateList('$s', '', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', 'level', '$changeLevel');\">My Level</a></div>";
		print "<div class=\"group_header_status\"><a href=\"javascript:regenerateList('$s', '', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', 'status', '$changeStatus');\">My Status</a></div>";
		print "<div class=\"group_header_options\"></div>";
		print "</div>";
		
		//reset row counter
		$x = 0;
		
		while ($row = mysql_fetch_object($result)) {
			
			$showName = htmlentities($row->name);
			
			if ($row->exclusiveRequired == "1") {
				
				$showGroupType = "Exclusive";
				
			} else {
				
				$showGroupType = "Standard";
				
			}
			
			if ($row->status == "approved") {
				
				$showStatusOptions = "Approved";
				
			} else {
				
				$showStatusOptions = "Pending";
				
			}
			
			if ($row->memberLevel == "1") {
				
				$showOptions = "<div class=\"edit\"><a href=\"showMyGroupEditor.php?groupId=$row->id\">Edit</a></div><div class=\"leave\"><a href=\"javascript:leaveGroup('$row->id', '$s', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to leave this group?');\">Leave</a></div><div class=\"delete\"><a href=\"javascript:deleteGroup('$row->id', '$s', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to delete this group?');\">Delete</a></div>";
				$showDeleteCheckbox = "<input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"deleteId[]\" name=\"deleteId[]\" value=\"$row->id\">";
				
			} else {
				
				$showOptions = "<div class=\"leave\"><a href=\"javascript:leaveGroup('$row->id', '$s', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to leave this group?');\">Leave</a></div>";
				$showDeleteCheckbox = "&nbsp;";
				
				
			}
			
			$showMemberLevel = $memberLevel[$row->memberLevel];
			
			print "<div id=\"group_$row->id\" class=\"group_container\">";
			print "<div class=\"group_header_checkbox\">$showDeleteCheckbox</div>";
			print "<div class=\"group_header_name\"><a href=\"/groups/id/$row->id\">$showName</a></div>";
			print "<div class=\"group_header_members\">$row->memberCount</div>";
			print "<div class=\"group_header_type\">$showGroupType</div>";
			print "<div class=\"group_header_level\">$showMemberLevel</div>";
			print "<div class=\"group_header_status\">$showStatusOptions</div>";
			print "<div class=\"group_header_options\">$showOptions</div>";
			print "</div>";

		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<div class=\"group_container\">";
			print "You are not currently a member of a group. <a href=\"groupSearch.php\">Click here</a> to browse available groups.";
			print "</div>";
			
		}
		
		print "<div class=\"group_list_options\">";
		print "<div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#deleteMultipleGroups :checkbox').attr('checked', this.checked);\"></div><div class=\"select_all\">Select All</div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleGroups('$s', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', '$orderBy', '');\" onclick=\"return confirm('Are you sure you want to delete the selected groups?');\">Delete</a></div>";
		print "</div>";
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Groups</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n', '$nameOrder', '$membersOrder', '$typeOrder', '$levelOrder', '$statusOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
	}
	
}

?>