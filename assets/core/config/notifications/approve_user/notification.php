<?php

$subject = "Your " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " access request has been approved.";
$message = "Hello " . htmlentities($row->name) . ",<br><br>Your access request for " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " has been approved. Please use the username and password you provided when you signed up to login.";

?>