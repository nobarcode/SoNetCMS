<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$documentType = sanitize_string($_REQUEST['documentType']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);

changeDirection($id, $documentType, $s, $d);

function changeDirection($id, $documentType, $s, $d) {
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}	
	
	$result = mysql_query("SELECT version, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i:%s %p') AS newDateCreated, usernameCreated FROM documentVersioning WHERE parentId = '{$id}' AND documentType = '{$documentType}' ORDER BY version DESC");
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
	
	
	$result = mysql_query("SELECT version, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i:%s %p') AS newDateCreated, usernameCreated FROM documentVersioning WHERE parentId = '{$id}' AND documentType = '{$documentType}' ORDER BY version DESC LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
	
	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($id, $documentType, $s, '');
		
	} else {
		
		if ($count > 0) {

			print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			print "<tr class=\"version_header\">\n";
			print "<td>Version</td><td>Date</td><td>Author</td>\n";
			print "</tr>\n";
			
			while ($row = mysql_fetch_object($result)) {

				print "<tr class=\"version\"><td>$row->version</td><td>$row->newDateCreated</td><td>$row->usernameCreated</td><td><div class=\"version_options\"><div class=\"load\"><a href=\"javascript:changeVersion('$row->version');\">Load</a></div>";
				
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {
					
					print "<div class=\"delete\"><a href=\"javascript:deleteVersion('$row->version');\" onclick=\"return confirm('Are you sure you want to delete the selected version?');\">Delete</a></div>";
					
				}
				
				print "</div></td></tr>";

			}
			
			print "</table>";
			
			print "<div id=\"version_list_navigation\">";
			print "	<div class=\"totals\">$totalRows Versions Found</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateVersionList('$id', '$s', 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateVersionList('$id', '$s', 'n');\" title=\"Next Results\">Next</a></div></div>";
			print "</div>";
			
		} else {
			
			print "No versions available.";
			
		}
		
	}
	
}

?>

