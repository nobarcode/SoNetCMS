<?php

$subject = $_SESSION['username'] . " has posted a reply to a conversation on " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . "!";
$notificationText = "Hello " . htmlentities($row->name) . ",<br><br>Your friend ". $_SESSION['username'] . " has posted a reply to conversation. <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/showGroupConversation.php?parentId=$parentId&findId=$id\">Click here</a> to view the reply.";

?>