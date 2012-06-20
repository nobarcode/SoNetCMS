<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_config_reader.php");

//clear lastActive
$result = mysql_query("UPDATE users SET lastActive = 0, signedOut = '" . date("Y-m-d H:i:s", time()) . "' WHERE username = '{$_SESSION['username']}'");

//clear document editing tracker
$result = mysql_query("DELETE FROM documentEditTracking WHERE username = '{$_SESSION['username']}'");

$_SESSION['username'] = "";
$_SESSION['userLevel'] = "";

session_destroy();

header("location: ./");

?>