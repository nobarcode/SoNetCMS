<?php

$notificationText = " has posted the following comment on <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/showProfile.php?username=$row->author\">$row->author's</a> blog in <a href=\"$messageURL\">$row->title</a>:<br><br><div style=\"font-style:italic; color:#8a8a8a;\">" . preg_replace("/\\n/", "<br>",htmlentities(unsanitize_string($body))) . "</div><br><a href=\"$messageURL\">Click here</a> to read the blog and its comments.";

?>