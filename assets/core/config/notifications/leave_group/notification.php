<?php

$subject = "A member has left $row->groupName";
$message = "Hello " . htmlentities($row->name) . ",<br><br>A member has left " . htmlentities($row->groupName) . ".<br><br>To view the group's current members <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/manageGroupMembers.php?groupId=$groupId\">click here</a>.";

?>