<?php

if (trim($_SESSION['username']) != "" && trim($_SESSION['userLevel']) != "") {
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
		
		$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
		$_SESSION['sysRootPath'] = "$script_directory/assests";
		$_SESSION['wwwRootPath'] = "/cms_users/assets";
		
	} else {
		
		$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
		$_SESSION['sysRootPath'] = "$script_directory/cms_users/" . $_SESSION['username'];
		$_SESSION['wwwRootPath'] = "/cms_users/" . $_SESSION['username'];
		
	}
	
}

?>