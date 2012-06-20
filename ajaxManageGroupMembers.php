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
$usernameOrder = sanitize_string($_REQUEST['usernameOrder']);
$dateOrder = sanitize_string($_REQUEST['dateOrder']);
$levelOrder = sanitize_string($_REQUEST['levelOrder']);
$statusOrder = sanitize_string($_REQUEST['statusOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

if (trim($_SESSION['username']) == "") {exit;}
if (trim($groupId) == "") {exit;}

//validate group
$result = mysql_query("SELECT id FROM groups WHERE id = '{$groupId}'");

if (mysql_num_rows($result) == 0) {

	exit;

}

//if the user is not an admin require that they be an owner
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	$requireOwner = " AND groupsMembers.username = '". $_SESSION['username'] . "' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2')";
	
}

$result = mysql_query("SELECT groups.id FROM groupsMembers INNER JOIN groups ON groups.id = groupsMembers.parentId WHERE groupsMembers.parentId = '{$groupId}'");
$row = mysql_fetch_object($result);
$groupId = $row->id;

changeDirection($s, $d, $groupId, $usernameOrder, $dateOrder, $levelOrder, $statusOrder, $orderBy, $change);

function changeDirection($s, $d, $groupId, $usernameOrder, $dateOrder, $levelOrder, $statusOrder, $orderBy, $change) {
	
	$memberLevel[1] = "Owner";
	$memberLevel[2] = "Administrator";
	$memberLevel[3] = "Member";
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if ($orderBy == $change) {
		
		if ($change == "username") {if ($usernameOrder == "desc") {$usernameOrder = "asc";} else {$usernameOrder = "desc";} $changeUsername = "username";}
		if ($change == "date") {if ($dateOrder == "desc") {$dateOrder = "asc";} else {$dateOrder = "desc";} $changeDate = "date";}
		if ($change == "level") {if ($levelOrder == "desc") {$levelOrder = "asc";} else {$levelOrder = "desc";} $changeLevel = "level";}
		if ($change == "status") {if ($statusOrder == "desc") {$statusOrder = "asc";} else {$statusOrder = "desc";} $changeStatus = "status";}
		
	}
	
	if ($orderBy == "username") {$orderBySQL = "username"; $directionSQL = strtoupper($usernameOrder); $changeUsername = "username";}
	if ($orderBy == "date") {$orderBySQL = "dateJoined"; $directionSQL = strtoupper($dateOrder); $changeDate = "date";}
	if ($orderBy == "level") {$orderBySQL = "memberLevel"; $directionSQL = strtoupper($levelOrder); $changeLevel = "level";}
	if ($orderBy == "status") {$orderBySQL = "status"; $directionSQL = strtoupper($statusOrder); $changeStatus = "status";}
	
	$result = mysql_query("SELECT username FROM groupsMembers WHERE parentId = '{$groupId}'");
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

	$result = mysql_query("SELECT *, DATE_FORMAT(dateJoined, '%M %d, %Y %h:%i %p') AS newDateJoined FROM groupsMembers WHERE parentId = '{$groupId}' ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, '', $groupId, $usernameOrder, $dateOrder, $levelOrder, $statusOrder, $orderBy, $change);
		
	} else {
		
		print "<form id=\"multipleMembersAction\">";
		print "<div class=\"member_main_header_options\">";
		print "<div class=\"member_header_checkbox\"></div>";
		print "<div class=\"member_header_username\"><a href=\"javascript:regenerateList('$s', '', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', 'username', '$changeUsername');\">Username</a></div>";
		print "<div class=\"member_header_date\"><a href=\"javascript:regenerateList('$s', '', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', 'date', '$changeDate');\">Member Since</a></div>";
		print "<div class=\"member_header_level\"><a href=\"javascript:regenerateList('$s', '', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', 'level', '$changeLevel');\">Member Level</a></div>";
		print "<div class=\"member_header_status\"><a href=\"javascript:regenerateList('$s', '', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', 'status', '$changeStatus');\">Status</a></div>";
		print "<div class=\"member_header_options\"></div>";
		print "</div>";
		
		//reset row counter
		$x = 0;
		
		while ($row = mysql_fetch_object($result)) {
			
			if ($row->status == "approved") {
				
				$showStatusOptions = "Approved";
				
			} else {
				
				$showStatusOptions = "Pending";
				
			}
			
			$showMemberLevel = $memberLevel[$row->memberLevel];
			
			print "<div id=\"member_$row->username\" class=\"member_container\">";
			print "<div class=\"member_header_checkbox\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"multipleId[]\" name=\"multipleId[]\" value=\"$row->username\"></div>";
			print "<div class=\"member_header_username\"><a href=\"/showProfile.php?username=$row->username\">$row->username</a></div>";
			print "<div class=\"member_header_date\">$row->newDateJoined</div>";
			print "<div class=\"member_header_level\"><a href=\"javascript:toggleMemberLevel('$row->username', '$s', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to change this member\'s level?');\">$showMemberLevel</a></div>";
			print "<div class=\"member_header_status\"><a href=\"javascript:toggleMemberStatus('$row->username', '$s', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to change the status of this member?');\">$showStatusOptions</a></div>";
			print "<div class=\"member_header_options\"><a href=\"javascript:deleteMember('$row->username', '$s', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to delete this member?');\">Delete</a></div>";
			print "</div>";

		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<div class=\"member_container\">";
			print "This group does not have any members.";
			print "</div>";
			
		}
		
		print "<div class=\"member_list_options\">";
		print "<div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#multipleMembersAction :checkbox').attr('checked', this.checked);\"></div><div class=\"select_all\">Select All</div><div class=\"approve_selected\"><a href=\"javascript:approveMultipleMembers('$s', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to approve the selected members?');\">Approve</a></div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleMembers('$s', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete the selected members?');\">Delete</a></div>";
		print "</div>";
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Members</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n', '$usernameOrder', '$dateOrder', '$levelOrder', '$statusOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		print "<input type=\"hidden\" id=\"groupId\" name=\"groupId\" value=\"$groupId\">";
		print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
		print "<input type=\"hidden\" id=\"usernameOrder\" name=\"usernameOrder\" value=\"$usernameOrder\">";
		print "<input type=\"hidden\" id=\"dateOrder\" name=\"dateOrder\" value=\"$dateOrder\">";
		print "<input type=\"hidden\" id=\"memberLevelOrder\" name=\"levelOrder\" value=\"$levelOrder\">";
		print "<input type=\"hidden\" id=\"statusOrder\" name=\"statusOrder\" value=\"$statusOrder\">";
		print "<input type=\"hidden\" id=\"orderBy\" name=\"orderBy\" value=\"$orderBy\">";
		print "</form>";		
		
	}
	
}

?>