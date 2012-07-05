<?php

$subject = "Tranfer of Group Ownership: $row->groupName";
$notificationText = "Hello $row->name,<br><br>" . $_SESSION['username'] . " has transferred ownership of $row->groupName to $username<br><br>To view the group's current members <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/manageGroupMembers.php?id=$groupId\">click here.</a>";

?>