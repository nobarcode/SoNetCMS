<?php

$notificationText = " has posted the following comment in <a href=\"$messageURL\">$row->title</a>:<br><br><div style=\"font-style:italic; color:#8a8a8a;\">" . preg_replace("/\\n/", "<br>", htmlentities(unsanitize_string($body))) . "</div><br><a href=\"$messageURL\">Click here</a> to read the article and its comments.";

?>