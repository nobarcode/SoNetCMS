<?php

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4) {
	
	exit;
	
}

?>