<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$elementId = "#" . sanitize_string($_REQUEST['elementId']);
$type = sanitize_string($_REQUEST['type']);
$id = sanitize_string($_REQUEST['id']);
$vote = sanitize_string($_REQUEST['vote']);

//exit if this is a private event and the user is not a member of the group
if ($type == "eventComment" && !accessPrivateGroupEvent($id)) {exit;}

if (trim($type) != "" && trim($id) != "" && trim($_SESSION['username']) != "" && ($vote == "0" || $vote == "1")) {
	
	//check if user has already voted
	$result = mysql_query("SELECT voteYes, voteNo FROM documentVotes WHERE type = '{$type}' AND parentId = '{$id}' AND username = '{$_SESSION['username']}'");
	
	$time = date("Y-m-d H:i:s", time());
	
	if (mysql_num_rows($result) == 0) {
		
		if ($vote == "1") {
			
			mysql_query("INSERT INTO documentVotes (type, parentId, username, dateVoted, voteYes) VALUES ('{$type}', '{$id}', '{$_SESSION['username']}', '{$time}', '1')");
			
		} else {
			
			mysql_query("INSERT INTO documentVotes (type, parentId, username, dateVoted, voteNo) VALUES ('{$type}', '{$id}', '{$_SESSION['username']}', '{$time}', '1')");
			
		}
		
	} else {
		
		$row = mysql_fetch_object($result);
		
		if ($row->voteYes == "1" && $vote == "0") {
			
			mysql_query("UPDATE documentVotes SET voteYes = '0', voteNo = '1', dateUpdated = '{$time}' WHERE type = '{$type}' AND parentId = '{$id}' AND username = '{$_SESSION['username']}'");
			
		} elseif ($row->voteNo == "1" && $vote == "1") {
			
			mysql_query("UPDATE documentVotes SET voteYes = '1', voteNo = '0', dateUpdated = '{$time}' WHERE type = '{$type}' AND parentId = '{$id}' AND username = '{$_SESSION['username']}'");
			
		}
		
	}
	
}

//display votes based on type and id
$result = mysql_query("SELECT COALESCE((SUM(voteYes)),0) AS totalVoteYes, COALESCE((SUM(voteNo)),0) AS totalVoteNo, ROUND(SUM(voteYes) / COUNT(parentId) * 100, 1) AS voteScore FROM documentVotes WHERE type = '{$type}' AND parentId = '{$id}'");
$total = mysql_num_rows($result);
$row = mysql_fetch_object($result);

header('Content-type: application/javascript');

if ($type == "documentComment" || $type == "blogComment" || $type == "eventComment" || $type == "userProfileComment" || $type == "documentImageComment" || $type == "userImageComment") {
	
	print "$('$elementId').html('<div class=\"votes\"><div class=\"yes\">$row->totalVoteYes</div><div class=\"no\">$row->totalVoteNo</div></div><div class=\"score\">$row->voteScore%</div>');";
	
	if (trim($_SESSION['username']) != "") {
		
		print "$('$elementId').html('<div class=\"votes\"><div class=\"yes\" onClick=\"vote(\'$elementId\', \'$type\', \'$id\', \'1\');\">$row->totalVoteYes</div><div class=\"no\" onClick=\"vote(\'$elementId\', \'$type\', \'$id\', \'0\');\">$row->totalVoteNo</div></div><div class=\"score\">$row->voteScore%</div>');";
		
	} else {
		
		print "$('$elementId').html('<div class=\"votes\"><div class=\"yes\">$row->totalVoteYes</div><div class=\"no\">$row->totalVoteNo</div></div><div class=\"score\">$row->voteScore%</div>');";
		
	}
	

} elseif ($type == "document" || $type == "blog") {
	
	if (trim($row->voteScore) != "") {
		
		print "$('$elementId').html('$row->voteScore%');";
		
	} else {
		
		print "$('$elementId').html('no votes');";
		
	}
	
	print "$('#total_up_votes').html('$row->totalVoteYes');";
	print "$('#total_down_votes').html('$row->totalVoteNo');";
	
}

function accessPrivateGroupEvent($id) {
	
	$result = mysql_query("SELECT events.groupId, events.private FROM commentsDocuments INNER JOIN events ON commentsDocuments.parentId = events.id WHERE commentsDocuments.id = '{$id}' AND commentsDocuments.type = 'eventComment' GROUP BY commentsDocuments.id");
	$row = mysql_fetch_object($result);
	$groupId = $row->groupId;
	$private = $row->private;
	
	if ($private == 1) {
		
		if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {

			//if the user is not an admin, validate that the user is allowed to delete the requested group
			$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND status = 'approved'");

			if (mysql_num_rows($result) == 0) {

				return false;

			}

		}
		
	}
	
	return true;
	
}

?>