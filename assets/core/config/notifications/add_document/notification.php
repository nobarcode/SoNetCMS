<?php

$subject = "New " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " Document - Ready for Review";
$message = "Hello " . htmlentities($row->name) . ",<br><br>A new document has been created by " . $_SESSION['username'] . " and is pending review. Please login to review, edit, publish, or delete the document:<br><br><a href=\"http://" . $_SERVER['HTTP_HOST'] . "/documents/open/$shortcut\">$shortcut</a>";

?>