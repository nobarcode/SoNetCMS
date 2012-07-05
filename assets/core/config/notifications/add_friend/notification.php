<?php

$subject = "You have a new " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " friend request!";
$notificationText = "Hello " . htmlentities($row->name) . ",<br><br>You have a new friend request pending!<br><br>To view your pending requests <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/showMyPendingFriends.php\">click here</a>.";

?>