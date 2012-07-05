<?php

$subject = "Request to Join Group: $row->groupName";
$notificationText = "Hello " . htmlentities($row->name) . ",<br><br>Someone has requested to join " . htmlentities($row->groupName) . ".<br><br>To view the group's current members <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/manageGroupMembers.php?groupId=$groupId\">click here</a>.";

?>