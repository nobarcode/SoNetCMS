<?php

class CategoryUserGroupValidator {
	
	var $category;
	var $resultArray;
	var $groupsAssigned;
	
	//method to load the supplied category's user groups
	function loadCategoryUserGroups($category) {
		
		unset($this->resultArray);
		
		$this->category = $category;
		
		//if groups are assigned to this category, see if the current user is in any of the groups
		$result = mysql_query("SELECT * FROM categoriesUserGroups WHERE category = '{$this->category}'");

		if (mysql_num_rows($result) > 0) {

			$result = mysql_query("SELECT categoriesUserGroups.category, userGroups.id, userGroups.restrictViewing, userGroups.allowEditing FROM categoriesUserGroups INNER JOIN userGroupsMembers ON userGroupsMembers.groupId = categoriesUserGroups.groupId AND userGroupsMembers.username = '{$_SESSION['username']}' INNER JOIN userGroups ON userGroups.id = userGroupsMembers.groupId WHERE categoriesUserGroups.category = '{$this->category}'");
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
		
		//if there are no groups assigned to this category return true (allow view)	
		if ($this->groupsAssigned === false) {
			
			return(true);
			
		} else {
			
			//if groups are assigned to this category, see if any of the groups restrict viewing, if not, return true
			$result = mysql_query("SELECT * FROM categoriesUserGroups INNER JOIN userGroups ON userGroups.id = categoriesUserGroups.groupId WHERE categoriesUserGroups.category = '{$this->category}' AND userGroups.restrictViewing = '1'");

			if (mysql_num_rows($result) == 0) {

				return(true);

			} else {
				
				//if groups are assigned to this category, check if any of the groups this user is a part of are set to restrict viewing, if so, return true
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
		
		//if there are no groups assigned to this category return true (allow edit)	
		if ($this->groupsAssigned === false) {
			
			return(true);
			
		} else {
			
			//if groups are assigned to this category, check if any of the groups this user is a part of allow editing, if so, return true
			for ($x = 0; $x < count($this->resultArray); $x++) {
				
				if ($this->resultArray[$x]['allowEditing'] == '1') {
					
					return(true);
					
				}
				
			}
			
		}
		
		//returns false if the user is not in any of the assigned groups
		return(false);
		
	}
	
	//generates a list of MySQL query parts that will exclude categories that are assigned groups that the current user is not in
	function viewCategoryExclusionList($table) {
		
		//master account exception
		if ($_SESSION['userLevel'] == 1) {
			
			return;
			
		}
		
		$table = sanitize_string($table);

		$result = mysql_query("SELECT categoriesUserGroups.category, userGroups.restrictViewing, userGroups.allowEditing FROM categoriesUserGroups LEFT JOIN userGroupsMembers ON userGroupsMembers.groupId = categoriesUserGroups.groupId AND userGroupsMembers.username =  '{$_SESSION['username']}' LEFT JOIN userGroups ON userGroups.id = userGroupsMembers.groupId");
		
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {

				if ($row->restrictViewing != '1') {

					$return .= " AND $table.category != '" . sanitize_string($row->category) . "'";

				}

			}	
			
		} else {
			
			$result = mysql_query("SELECT categoriesUserGroups.category FROM categoriesUserGroups");

			while ($row = mysql_fetch_object($result)) {

				$return .= " AND $table.category != '" . sanitize_string($row->category) . "'";

			}
			
		}
		
		return($return);
		
	}
	
	//generates a list of MySQL query parts that will exclude categories that are assigned groups that the current user is not in
	function editCategoryExclusionList($table) {
		
		//master account exception
		if ($_SESSION['userLevel'] == 1) {
			
			return;
			
		}
		
		$table = sanitize_string($table);

		$result = mysql_query("SELECT categoriesUserGroups.category, userGroups.restrictViewing, userGroups.allowEditing FROM categoriesUserGroups LEFT JOIN userGroupsMembers ON userGroupsMembers.groupId = categoriesUserGroups.groupId AND userGroupsMembers.username =  '{$_SESSION['username']}' LEFT JOIN userGroups ON userGroups.id = userGroupsMembers.groupId");
		
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {

				if ($row->allowEditing != '1') {

					$return .= " AND $table.category != '" . sanitize_string($row->category) . "'";

				}

			}	
			
		} else {
			
			$result = mysql_query("SELECT categoriesUserGroups.category FROM categoriesUserGroups");

			while ($row = mysql_fetch_object($result)) {

				$return .= " AND $table.category != '" . sanitize_string($row->category) . "'";

			}
			
		}
		
		return($return);
		
	}
	
}

?>