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
$filterType = sanitize_string($_REQUEST['filterType']);
$filterValue = sanitize_string($_REQUEST['filterValue']);
$filterOrder = sanitize_string($_REQUEST['filterOrder']);

changeDirection($s, $d, $filterType, $filterValue, $filterOrder);

function changeDirection($s, $d, $filterType, $filterValue, $filterOrder) {
	
	$max_per_page = 25;
	
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/config.properties');
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	//setup filtering
	switch ($filterType) {
		
		case "Username":
			
			$queryFilter = " AND username LIKE '%$filterValue%'";
			$queryOrder = "ORDER BY username";
			
		break;
		
		case "E-mail Address":
			
			$queryFilter = " AND email LIKE '%$filterValue%'";
			$queryOrder = "ORDER BY email";
			
		break;
		
		case "Status":
			
			$queryFilter = " AND status LIKE '%$filterValue%'";
			$queryOrder = "ORDER BY status";
			
		break;
		
		case "Last Login":
			
			$queryFilter = " AND DATE_FORMAT(lastLogin, '%m/%d/%Y %h:%i %p') LIKE '%$filterValue%'";
			$queryOrder = "ORDER BY lastLogin";
			
		break;
		
		case "Last IP Address":
			
			$queryFilter = " AND lastIpAddress LIKE '%$filterValue%'";
			$queryOrder = "ORDER BY lastIpAddress";
			
		break;
		
	}
	
	if ($filterOrder == "Descending") {
		
		$queryOrder .= " DESC";
		
	} else {
		
		$queryOrder .= " ASC";
		
	}
	
	$result = mysql_query("SELECT username FROM users WHERE 1 = 1$queryFilter");
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
	
	$result = mysql_query("SELECT *, DATE_FORMAT(lastLogin, '%m/%d/%Y %h:%i %p') AS newlastLogin FROM users WHERE 1= 1$queryFilter $queryOrder LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, '', $filterType, $filterValue, $filterOrder);

	} else {
		
		print "<form id=\"multipleUsersAction\">";
		
		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		print "<tr>";
		
		while ($row = mysql_fetch_object($result)) {
			
			if ($row->status == 'pending') {
				
				$status = "Pending";
				
			} else {
				
				$status = "Approved";
				
			}
			
			//check if config.properties file requires approvals on new sign ups. if it does create the Member Status toggle option
			if ($config->readValue('requireSignUpApproval') == 'true') {
				
				$showStatusOption = "<div class=\"status\"><a href=\"javascript:changeUserStatus('$row->username');\" onClick=\"return confirm('Are you sure you want to change this user\\'s status?');\"><span id=\"user_status_$row->username\">$status</span></a></div>";
				$showApproveMultiple = "<div class=\"approve_multiple\"><a href=\"javascript:approveMultipleUsers('$s');\" onClick=\"return confirm('Are you sure you want to approve the selected users?');\">Approve</a></div>";
				
			}
			
			print "<tr id=\"user_$row->username\" class=\"user_container_row\">";
			print "<td class=\"user_container\" width=\"10\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"multipleId[]\" name=\"multipleId[]\" value=\"$row->username\"></td>";
			print "<td class=\"user_container\"><a href=\"javascript:initEditUser('$row->username', '$s');\">$row->username</a></td>";
			print "<td class=\"user_container\"><a href=\"mailto:" . htmlentities($row->email) . "\">$row->email</a></td>";
			print "<td class=\"user_container\">$row->newlastLogin</td>";
			print "<td class=\"user_container\">$row->lastIpAddress</td>";
			print "<td class=\"user_container\"><div class=\"toolbar\">";
			
			if ($row->level != 1 || ($row->level == 1 && $row->status == "pending")) {
				
				print "$showStatusOption";
				
			}
			
			print "<div class=\"groups\"><a href=\"javascript:initEditUserGroups('$row->username');\">Groups</a></div>";
			
			if ($row->level != 1) {
				
				print "<div class=\"impersonate\"><a href=\"impersonateUser.php?username=$row->username\">Impersonate</a></div>";
				
			}
			
			print "<div class=\"delete\"><a href=\"javascript:deleteUser('$row->username', '$s');\" onClick=\"return confirm('Are you sure you want to delete this user?');\">Delete</a></div>";
			print "</div></td>";
			print "</tr>";
			
		}
		
		print "<tr>";
		print "<td class=\"user_list_options\" width=\"10\"><div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#multipleUsersAction :checkbox').attr('checked', this.checked);\"></div></td><td colspan=\"6\" class=\"user_list_options\"><div class=\"select_all\">Select All</div>$showApproveMultiple<div class=\"delete_selected\"><a href=\"javascript:deleteMultipleUsers('$s');\" onclick=\"return confirm('Are you sure you want to delete the selected users?');\">Delete</a></div></td>";
		print "</tr>";
		
		print "</table>";
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Users</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
		print "</form>";
		
	}
	
}

?>