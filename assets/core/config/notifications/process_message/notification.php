<?php

$subject = $_SESSION['username'] . " has sent you a message on " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . "!";
$message = "Hello " . htmlentities($row->name) . ",<br><br>Your friend ". $_SESSION['username'] . " has sent you a message. <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/showMyMessages.php\">Click here</a> to view your messages.";

?>