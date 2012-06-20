<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$commentFilter = sanitize_string($_REQUEST['commentFilter']);
$parentId = sanitize_string($_REQUEST['parentId']);
$type = sanitize_string($_REQUEST['type']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);

if ($commentFilter == "dateOldest") {
	
	$orderBy = "commentsUserProfiles.dateCreated ASC";
	
} elseif ($commentFilter == "dateNewest") {
	
	$orderBy = "commentsUserProfiles.dateCreated DESC";
	
} elseif ($commentFilter == "scoreHighest") {
	
	$orderBy = "voteScore DESC, documentVotes.voteYes DESC";
	
} elseif ($commentFilter == "scoreLowest") {
	
	$orderBy = "voteScore ASC, documentVotes.voteNo DESC";
	
} else {
	
	$orderBy = "commentsUserProfiles.dateCreated ASC";
	
}

changeDirection($parentId, $type, $orderBy, $s, $d);

function changeDirection($parentId, $type, $orderBy, $s, $d) {
	
	//read config file for this portlet
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/widgets/showProfile/user_comments.properties');
	
	$maxDisplay = $config->readValue('maxDisplay');
	
	$result = mysql_query("SELECT id FROM commentsUserProfiles WHERE parentId = '{$parentId}'");
	$totalRows = mysql_num_rows($result);
	
	if (trim($findCommentId) != "" && trim($s) == "") {
		
		$findRow = 0;
		
		while ($row = mysql_fetch_object($result)) {
			
			if ($row->id == $findCommentId) {
				
				if ($findRow > $maxDisplay) {
					
					$s = floor($findRow / $maxDisplay) * $maxDisplay;
					
				} else {
					
					$s = 0;
					
				}
				
				break;
				
			} else {
				
				$findRow++;
				
			}
			
		}
		
	}
	
	if (is_string($s) && $s == 'last') {
		
		if ($totalRows > $maxDisplay) {
			
			$s = floor($totalRows / $maxDisplay) * $maxDisplay;
			
		} else {
			
			$s = 0;
			
		}
		
		$d = '';
		
	} elseif (trim($s) == "") {

		$s = 0;

	}
	
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

	$result = mysql_query("SELECT commentsUserProfiles.id, commentsUserProfiles.dateCreated, commentsUserProfiles.username, commentsUserProfiles.body, IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes, IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo, IFNULL(ROUND(SUM(documentVotes.voteYes) / COUNT(documentVotes.parentId) * 100, 1),0) AS voteScore FROM commentsUserProfiles LEFT JOIN documentVotes ON documentVotes.parentId = commentsUserProfiles.id AND documentVotes.type = '{$type}' WHERE commentsUserProfiles.parentId = '{$parentId}' GROUP BY commentsUserProfiles.id ORDER BY $orderBy LIMIT $s, $maxDisplay");
	$count = mysql_num_rows($result);

	if ($count < 1 && $totalRows > 0 && $s > 0) {

		$s -= $maxDisplay;
		return changeDirection($parentId, $type, $orderBy, $s, '');

	} else {
		
		if ($count > 0) {
			
			$x = 0;
			
			print "<div class=\"comments_list\">";
			
			while ($row = mysql_fetch_object($result)) {
				
				$body = htmlentities($row->body);
				$body = preg_replace("/\\n/", "<br>", $body);
				
				$x++;
			
				if ($x < $count) {
					
					$style = " comment_row_separator";
					
				} else {
					
					$style = "";
					
				}
				
				print "<div id=\"comment_container_$row->id\" class=\"comment_container$style\">";
				print "<div class=\"comment_header\"><div style=\"float:left;\"><a href=\"/showProfile.php?username=$row->username\">$row->username</a> on " . date("m/d/Y g:i A", $row->dateCreated) . "</div>";
				
				print "<div class=\"comment_voting\">\n<table cellpadding=\"0\" cellspacing=\"0\"><tr valign=\"center\">";
				
				print "<td><div id=\"comment_score_$row->id\" class=\"vote_bar\">";
				
				if (trim($row->voteScore) != "") {

					if (trim($_SESSION['username']) != "") {
						
						print "<div class=\"votes\"><div class=\"yes\" onClick=\"vote('comment_score_$row->id', '$type', '$row->id', '1');\">$row->totalVoteYes</div><div class=\"no\" onClick=\"vote('comment_score_$row->id', '$type', '$row->id', '0');\">$row->totalVoteNo</div></div><div class=\"score\">$row->voteScore%</div>";
						
					} else {
						
						print "<div class=\"votes\"><div class=\"yes\">$row->totalVoteYes</div><div class=\"no\">$row->totalVoteNo</div></div><div class=\"score\">$row->voteScore%</div>";
						
					}

				} else {

					print "no votes";

				}
				
				print "</div></td></tr></table></div>\n";
				
				print "</div>";
				
				print "<div id=\"comment_$row->id\" class=\"comment_body\">$body</div>";
				
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->username || $_SESSION['username'] == $parentId) {
				
					print "<div class=\"comment_options\"><div class=\"edit\"><a href=\"javascript:initEditComment($row->id);\">Edit</a></div>";
					
					if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $parentId) {
						
						print "<div class=\"delete\"><a href=\"javascript:deleteComment($row->id);\" onClick=\"return confirm('Are you sure you want to delete this comment?');\">Delete</a></div>";
						
					}
					
					print "</div>";
					
				}
				
				print "</div>";
				
			}
			
			print "<div id=\"comments_list_navigation\">";
			print "	<div class=\"totals\">$totalRows Comments</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateCommentsList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateCommentsList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
			print "</div>";
			
		} else {
			
			print "<div class=\"comment_container\">$parentId doesn't have any comments yet.</div>";
			
		}
		
		//assign last_s the current value of page start value
		print "<script>last_s = $s;</script>";
		
	}
	
}

?>

