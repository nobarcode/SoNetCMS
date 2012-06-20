<?php

if (is_file("../_index.php")) {
	
	unlink("../index.php");
	unlink("../sonet.sql");
	unlink("../zipcodes.sql");
	rename("../_index.php", "../index.php");
	header("location: /signIn.php");
	
}

?>