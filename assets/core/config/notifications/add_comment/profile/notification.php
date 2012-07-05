<?php

$notificationText = " has posted the following comment on <a href=\"$messageURL\">" . unsanitize_string($parentId) . "'s profile</a>:<br><br><div style=\"font-style:italic; color:#8a8a8a;\">" . preg_replace("/\\n/", "<br>", htmlentities(unsanitize_string($body))) . "</div>";

?>