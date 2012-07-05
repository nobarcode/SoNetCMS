<?php

$subject = "A member has left $row->groupName";
$notificationText = "Hello $row->name,<br><br>A member has left $row->groupName<br><br>To view the group's current members <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/manageGroupMembers.php?groupId=$groupId\">click here.</a>";

?>