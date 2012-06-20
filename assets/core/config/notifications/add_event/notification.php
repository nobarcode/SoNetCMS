<?php

$subject = "New " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " Event - Ready for Review";
$message = "Hello " . htmlentities($row->name) . ",<br><br>A new event has been created by " . $_SESSION['username'] . " and is pending review. Please login to review, edit, publish, or delete the event:<br><br><a href=\"http://" . $_SERVER['HTTP_HOST'] . "/events/id/$id\">/events/id/$id</a>";

?>