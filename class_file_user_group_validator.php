<?php

class FileUserGroupValidator {
	
	var $path;
	var $resultArray;
	var $groupsAssigned;
	
	//method to load the supplied files's user groups
	function loadFileUserGroups($path) {
		
		unset($this->resultArray);
		
		$this->path = $path;
		
		//if groups are assigned to this file, see if the current user is in any of the groups
		$result = mysql_query("SELECT * FROM fileManager WHERE fsPath = '{$this->path}' AND groupId != ''");

		if (mysql_num_rows($result) > 0) {

			$result = mysql_query("SELECT fileManager.owner, userGroups.id, userGroups.restrictViewing, userGroups.allowEditing FROM fileManager INNER JOIN userGroupsMembers ON userGroupsMembers.groupId = fileManager.groupId AND userGroupsMembers.username = '{$_SESSION['username']}' INNER JOIN userGroups ON userGroups.id = userGroupsMembers.groupId WHERE fileManager.fsPath = '{$this->path}'");
			while(($this->resultArray[] = mysql_fetch_assoc($result)) || array_pop($this->resultArray));
			$this->groupsAssigned = true;
			
		} else {
			
			$this->groupsAssigned = false;
			
		}
		
	}
	
	//
	function allowRead() {
		
		//master account exception
		if ($_SESSION['userLevel'] == 1) {
			
			return(true);
			
		}
		
		//if there are no groups assigned to this file return true (allow view)	
		if ($this->groupsAssigned === false) {
			
			return(true);
			
		} else {
			
			//if groups are assigned to this file, see if any of the groups restrict viewing, if not, return true
			$result = mysql_query("SELECT * FROM fileManager INNER JOIN userGroups ON userGroups.id = fileManager.groupId WHERE fileManager.fsPath = '{$this->path}' AND userGroups.restrictViewing = '1'");

			if (mysql_num_rows($result) == 0) {

				return(true);

			} else {
				
				//if groups are assigned to this file, check if any of the groups this user is a part of are set to restrict viewing, if so, return true
				for ($x = 0; $x < count($this->resultArray); $x++) {

					if ($this->resultArray[$x]['restrictViewing'] == '1') {

						return(true);

					}

				}
				
			}
			
		}
		
		//returns false if the user is not in any of the assigned groups
		return(false);
		
	}
	
	//
	function allowEditing() {
		
		//master account exception
		if ($_SESSION['userLevel'] == 1) {
			
			return(true);
			
		}
		
		//if there are no groups assigned to this file return true (allow edit)	
		if ($this->groupsAssigned === false) {
			
			return(true);
			
		} else {
			
			//if groups are assigned to this file, check if any of the groups this user is a part of allow editing, if so, return true
			for ($x = 0; $x < count($this->resultArray); $x++) {
				
				if ($this->resultArray[$x]['allowEditing'] == '1') {
					
					return(true);
					
				}
				
			}
			
		}
		
		//returns false if the user is not in any of the assigned groups
		return(false);
		
	}
	
}

?>