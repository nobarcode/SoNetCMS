<?php

$subject = "New " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " Announcement - Ready for Review";
$notificationText = "Hello " . htmlentities($row->name) . ",<br><br>A new announcement has been created by " . $_SESSION['username'] . " and is pending review. Please <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/announcementEditor.php\">click here</a> to login and review, edit, publish, or delete the announcement.";

?>