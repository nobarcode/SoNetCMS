<?php

$subject = "Your " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " $groupName membership request has been approved!";
$message = "Hello " . htmlentities($row->name) . ",<br><br>Your <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/groups/id/$groupId\">" . htmlentities($groupName) . "</a> membership request has been approved!";

?>