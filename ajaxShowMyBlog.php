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
$year = sanitize_string($_REQUEST['year']);
$dateOrder = sanitize_string($_REQUEST['dateOrder']);
$titleOrder = sanitize_string($_REQUEST['titleOrder']);
$statusOrder = sanitize_string($_REQUEST['statusOrder']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

changeDirection($s, $d, $month, $year, $dateOrder, $titleOrder, $statusOrder, $orderBy, $change);

function changeDirection($s, $d, $month, $year, $dateOrder, $titleOrder, $statusOrder, $orderBy, $change) {
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	if (trim($month) != "") {
		
		$monthQuery = " AND EXTRACT(MONTH FROM dateCreated) = '$month'";
		
	}
	
	if (trim($year) != "") {
		
		$yearQuery = " AND EXTRACT(YEAR FROM dateCreated) = '$year'";
		
	}
	
	if ($orderBy == $change) {
		
		if ($change == "date") {if ($dateOrder == "desc") {$dateOrder = "asc";} else {$dateOrder = "desc";} $changeDate = "date";}
		if ($change == "title") {if ($titleOrder == "desc") {$titleOrder = "asc";} else {$titleOrder = "desc";} $changeTitle = "title";}
		if ($change == "status") {if ($statusOrder == "desc") {$statusOrder = "asc";} else {$statusOrder = "desc";} $changeStatus = "status";}
		
	}
	
	if ($orderBy == "date") {$orderBySQL = "dateCreated"; $directionSQL = strtoupper($dateOrder); $changeDate = "date";}
	if ($orderBy == "title") {$orderBySQL = "title"; $directionSQL = strtoupper($titleOrder); $changeTitle = "title";}
	if ($orderBy == "status") {$orderBySQL = "publishState"; $directionSQL = strtoupper($statusOrder); $changeStatus = "status";}
	
	$result = mysql_query("SELECT id FROM blogs WHERE usernameCreated = '{$_SESSION['username']}'$monthQuery$yearQuery");
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

	$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i %p') AS newDateCreated FROM blogs WHERE usernameCreated = '{$_SESSION['username']}'$monthQuery$yearQuery ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($s, '', $month, $year, $dateOrder, $titleOrder, $statusOrder, $orderBy, $change);
		
	} else {
		
		print "<form id=\"multipleBlogsAction\">";
		print "<div class=\"blog_main_header_options\">";
		print "<div class=\"blog_header_checkbox\"></div>";
		print "<div class=\"blog_header_date\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$statusOrder', 'date', '$changeDate');\">Date</a></div>";
		print "<div class=\"blog_header_title\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$statusOrder', 'title', '$changeTitle');\">Title</a></div>";
		print "<div class=\"blog_header_status\"><a href=\"javascript:regenerateList('$s', '', '$dateOrder', '$titleOrder', '$statusOrder', 'status', '$changeStatus');\">Status</a></div>";
		print "<div class=\"blog_header_options\"></div>";
		print "</div>";
		
		while ($row = mysql_fetch_object($result)) {
			
			$title = htmlentities($row->title);
			$showStatus = htmlentities($row->publishState);
			
			$showPublishOptions = "<a href=\"javascript:togglePublishState('$row->id');\" onclick=\"return confirm('Are you sure you want to change the publishing status of this document?');\"><span id=\"publish_state_$row->id\">";
			
			if ($row->publishState == "Published") {
				
				$showPublishOptions .= "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_published.gif\" border=\"0\"> $showStatus</span></a>";
				
			} else {
				
				$showPublishOptions .= "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_unpublished.gif\" border=\"0\"> $showStatus</span></a>";
				
			}
			
			print "<div id=\"blog_$row->id\" class=\"blog_container\">";
			print "<div class=\"blog_header_checkbox\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"multipleId[]\" name=\"multipleId[]\" value=\"$row->id\"></div>";
			print "<div class=\"blog_header_date\"><a href=\"/blogs/id/$row->id\" target=\"_blank\"><img src=\"/assets/core/resources/images/tiny_icon_preview.gif\" border=\"0\"> $row->newDateCreated</a></div>";
			print "<div class=\"blog_header_title\"><a href=\"showMyBlogEditor.php?id=$row->id\"><img src=\"/assets/core/resources/images/tiny_icon_edit.gif\" border=\"0\"> $title</a></div>";
			print "<div class=\"blog_header_status\">$showPublishOptions</div>";
			print "<div class=\"blog_header_options\"><a href=\"javascript:deleteBlog('$row->id', '$s', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onClick=\"return confirm('Are you sure you want to delete this blog?');\"><img src=\"/assets/core/resources/images/tiny_icon_delete.gif\" border=\"0\"> Delete</a></div>";
			print "</div>";

		}
		
		if (mysql_num_rows($result) == 0) {
			
			print "<div class=\"blog_container\">";
			print "There were no blogs posted during the selected date range.";
			print "</div>";
			
		}
		
		print "<div class=\"blog_list_options\">";
		print "<div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#multipleBlogsAction :checkbox').attr('checked', this.checked);\"></div><div class=\"select_all\">Select All</div><div class=\"toggle_selected_state\"><a href=\"javascript:toggleMultipleBlogStatus('$s', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to change the published status of the selected blogs?');\">Switch Status</a></div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleBlogs('$s', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete the selected blogs?');\">Delete</a></div>";
		print "</div>";
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Blogs</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList('$s', 'b', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList('$s', 'n', '$dateOrder', '$titleOrder', '$statusOrder', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
		print "<input type=\"hidden\" id=\"s\" name=\"s\" value=\"$s\">";
		print "<input type=\"hidden\" id=\"dateOrder\" name=\"dateOrder\" value=\"$dateOrder\">";
		print "<input type=\"hidden\" id=\"titleOrder\" name=\"titleOrder\" value=\"$titleOrder\">";
		print "<input type=\"hidden\" id=\"statusOrder\" name=\"statusOrder\" value=\"$statusOrder\">";
		print "<input type=\"hidden\" id=\"orderBy\" name=\"orderBy\" value=\"$orderBy\">";
		print "</form>";		
		
	}
	
}

?>