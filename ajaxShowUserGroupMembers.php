<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$usernameOrder = sanitize_string($_REQUEST['usernameOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

changeDirection($id, $s, $d, $usernameOrder, $orderBy, $change);

function changeDirection($id, $s, $d, $usernameOrder, $orderBy, $change) {
	
	$result = mysql_query("SELECT name FROM userGroups WHERE id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);
	$showUserGroup = htmlentities($row->name);
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if ($orderBy == $change) {
		
		if ($change == "username") {if ($usernameOrder == "desc") {$usernameOrder = "asc";} else {$usernameOrder = "desc";} $changeUsername = "username";}
		
	}
	
	if ($orderBy == "username") {$orderBySQL = "username"; $directionSQL = strtoupper($usernameOrder); $changeUsername = "username";}
	
	$result = mysql_query("SELECT username FROM userGroupsMembers WHERE groupId = '{$id}'");
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

	$result = mysql_query("SELECT username FROM userGroupsMembers WHERE groupId = '{$id}' ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		changeDirection($id, $s, $d, $usernameOrder, $orderBy, $change);

	} else {
		
		print "<div class=\"user_group_name\">$showUserGroup Members</div>";
		
		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		print "<tr>";
		print "<td class=\"user_group_list_header\"><a href=\"javascript:regenerateMemberList('$id', '$s', '', '$usernameOrder', 'username', '$changeUsername');\">Member</a></td>";
		print "<td class=\"user_group_list_header\"></td>";
		print "</tr>";
		
		while ($row = mysql_fetch_object($result)) {
			
			$username = htmlentities($row->username);
			
			$escapeUsername = preg_replace('/\\\/', '\\\\\\', $username);
			$escapeUsername = preg_replace('/\'/', '\\\'', $escapeUsername);
			
			print "<tr id=\"user_group_member_$row->id\" class=\"user_group_container_row\">";
			print "<td class=\"user_group_container\">$escapeUsername</td>";
			print "<td class=\"user_group_container\"><div class=\"toolbar\"><a href=\"javascript:deleteUserGroupMember('$row->username', '$id', '$s', '$usernameOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to delete this user group member?');\">Delete</a></div></td>";
			print "</tr>";
			
		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<tr class=\"user_group_container_row\">";
			print "<td colspan=\"2\" class=\"user_group_container\">There are no members in this group.</td>";
			print "</tr>";
			
		}
		
		print "</table>";
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Members</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateMemberList('$id', '$s', 'b', '$usernameOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateMemberList('$id', '$s', 'n', '$usernameOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		print "<div class=\"user_group_member_done\"><input type=\"button\" id=\"editor_cancel\" value=\"Done\"></div>";
		
	}
	
}

?>