<?php

$subject = "Pending " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " membership request";
$message = "Hello $row->name,<br><br>The following user has a membership request pending:<br><br>$username<br><br>You can approve pending requests by accessing the <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/userEditor.php\">User Manager</a>; section in the <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/controlPanel.php\">Control Panel</a>.";

?>