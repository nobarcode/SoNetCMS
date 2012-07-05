<?php

$subject = preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " Password Reset";
$notificationText = "Your " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " password has been reset to: $password<br><br>Please login and change your temporary password as soon as possible.";

?>