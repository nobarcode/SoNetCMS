<?php

$subject = $_SESSION['username'] . " has posted a new conversation on " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . "!";
$notificationText = "Hello " . htmlentities($row->name) . ",<br><br>Your friend ". $_SESSION['username'] . " has posted a new conversation:<br><br><a href=\"http://" . $_SERVER['HTTP_HOST'] . "/showGroupConversation.php?parentId=$conversationId&findId=$conversationPostId\">" . htmlentities($title) . "</a>";

?>