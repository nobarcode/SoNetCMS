<?php

session_start();

if (trim($_SESSION['username']) != "") {
	
	mysql_query("UPDATE users SET lastActive = '" . date("Y-m-d H:i:s", time()) . "', signedOut = '" . date("Y-m-d H:i:s", time()) . "' WHERE username = '{$_SESSION['username']}'");
	
}

?>