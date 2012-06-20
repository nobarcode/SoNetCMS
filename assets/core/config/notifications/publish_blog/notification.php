<?php

$subject = $_SESSION['username'] . " has published a new blog on " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . "!";
$message = "Hello " . htmlentities($row->name) . ",<br><br>Your friend " . $_SESSION['username'] . " has published a new blog on " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . ".<br><br><a href=\"http://" . $_SERVER['HTTP_HOST'] . "/blogs/id/$id\">Click here</a> to view " . $_SESSION['username'] . "'s blog.";

?>