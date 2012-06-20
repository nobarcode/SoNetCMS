<?php

if ($_SESSION['username'] == "") {
	
	header("location: /signIn.php?jb=$jb&sr=1");
	exit;
	
}

?>