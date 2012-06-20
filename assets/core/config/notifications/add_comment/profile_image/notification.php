<?php

$messageBody = " has posted the following comment in <a href=\"/showProfile.php?username=$parentId\">$parentId's</a> <a href=\"$messageURL\">image gallery:</a><br><br><div style=\"font-style:italic; color:#8a8a8a;\">" . preg_replace("/\\n/", "<br>", htmlentities(unsanitize_string($body))) . "</div><br><a href=\"$messageURL\">Click here</a> to view the entire gallery and read all the comments.";

?>