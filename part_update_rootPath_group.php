<?php

if (trim($groupId) != "") {
	
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {

		//if the user is not an admin, validate that the user is allowed to edit the requested group
		$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

		if (mysql_num_rows($result) > 0) {

			$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));

			//set this new user's file manager session variables	
			$_SESSION['rootPath'] = "$script_directory/cms_groups/" . $groupId;

		}

	}
	
}

?>