<?php

class GroupValidator {
	
	var $isGroupAdmin;
	var $isGroupMember;
	
	function isGroupAdmin($groupId, $username) {
		
		//if the user is not an admin, validate that the user is allowed to access the requested group
		$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$username}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");
		
		if (mysql_num_rows($result) > 0) {
			
			return $this->isGroupAdmin = true;
			
		} else {
			
			return $this->isGroupAdmin = false;
			
		}
		
	}
	
	function isGroupMember($groupId, $username) {
		
		//validate that the user is a member of this group
		$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$username}' AND status = 'approved'");
		
		if (mysql_num_rows($result) > 0) {
			
			return $this->isGroupMember = true;
			
		} else {
			
			return $this->isGroupMember = false;
			
		}
		
	}
	
}

?>