<?php

//Connect to the SQL server:
$dbLink = mysql_connect("[servername]", "[dbusername]", "[dbpassword]") or die("ERROR: Unable to connect to database server!<br>");

mysql_select_db("[database]") or die("ERROR: Requested database not found!<br>");

?>