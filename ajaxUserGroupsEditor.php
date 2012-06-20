<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$nameOrder = sanitize_string($_REQUEST['nameOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

changeDirection($s, $d, $nameOrder, $orderBy, $change);

function changeDirection($s, $d, $nameOrder, $orderBy, $change) {
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if ($orderBy == $change) {
		
		if ($change == "name") {if ($nameOrder == "desc") {$nameOrder = "asc";} else {$nameOrder = "desc";} $changeName = "name";}
		
	}
	
	if ($orderBy == "name") {$orderBySQL = "name"; $directionSQL = strtoupper($nameOrder); $changeName = "name";}
	
	$result = mysql_query("SELECT id FROM userGroups WHERE 1 = 1");
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

	$result = mysql_query("SELECT * FROM userGroups WHERE 1 = 1 ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		changeDirection($s, $d, $nameOrder, $orderBy, $change);

	} else {
		
		print "<form id=\"multipleUserGroupsAction\">";
		
		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		print "<tr>";
		
		print "<td class=\"user_group_list_header\"><div class=\"user_group_header_checkbox\"></div></td>";
		print "<td class=\"user_group_list_header\"><a href=\"javascript:regenerateList('$s', '', '$nameOrder', 'name', '$changeName');\">Name</a></td>";
		print "<td class=\"user_group_list_header\"></td>";
		print "</tr>";
		
		while ($row = mysql_fetch_object($result)) {
			
			$name = htmlentities($row->name);
			
			$escapeName = preg_replace('/\\\/', '\\\\\\', $name);
			$escapeName = preg_replace('/\'/', '\\\'', $escapeName);
						
			print "<tr id=\"user_group_$row->id\" class=\"user_group_container_row\">";
			print "<td class=\"user_group_container\" width=\"10\"><div class=\"user_group_header_checkbox\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"deleteId[]\" name=\"deleteId[]\" value=\"$row->id\"></div>";
			print "<td class=\"user_group_container\"><a href=\"javascript:initEditUserGroup('$row->id', '$s', '$nameOrder', '$orderBy');\">$name</a></div>";
			print "<td class=\"user_group_container\"><div class=\"toolbar\"><div class=\"members\"><a href=\"javascript:initEditGroupMembers('$row->id');\">Members</a></div><div class=\"delete\"><a href=\"javascript:deleteUserGroup('$row->id', '$s', '$nameOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to delete this user group?');\">Delete</a></div></div>";
			print "</tr>";
			
		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<tr class=\"user_group_container_row\">";
			print "<td colspan=\"3\" class=\"user_group_container\">No user groups have been created yet.</td>";
			print "</tr>";
			
		}
		
		print "<tr>";
		print "<td class=\"user_group_list_options\" width=\"10\"><div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#multipleUserGroupsAction :checkbox').attr('checked', this.checked);\"></div></td><td colspan=\"2\" class=\"user_group_list_options\"><div class=\"select_all\">Select All</div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleUserGroups('$s', '$nameOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete the selected user groups?')\">Delete</a></td>";
		print "</tr>";
		
		print "</table>";
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows User Groups</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b', '$nameOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n', '$nameOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
		print "<input type=\"hidden\" id=\"nameOrder\" name=\"nameOrder\" value=\"$nameOrder\">";
		print "<input type=\"hidden\" id=\"orderBy\" name=\"orderBy\" value=\"$orderBy\">";
		print "</form>";
		
	}
	
}

?>