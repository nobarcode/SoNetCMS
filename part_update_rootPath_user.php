<?php

if (trim($_SESSION['username']) != "" && trim($_SESSION['userLevel']) != "") {
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
		
		$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
		$_SESSION['rootPath'] = "$script_directory/assets";
		
	} else {
		
		$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
		$_SESSION['rootPath'] = "$script_directory/cms_users/" . $_SESSION['username'];
		
	}
	
}

?>