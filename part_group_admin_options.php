<?php

if (trim($groupId) != "") {
	
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {

		//if the user is not an admin, validate that the user is allowed to edit the requested group
		$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

		if (mysql_num_rows($result) > 0) {
			
			showGroupAdminOptions($groupId);
			
		}
		
	} else {
		
		showGroupAdminOptions($groupId);
		
	}
	
}

function showGroupAdminOptions($groupId) {
	
	print "	<div id=\"group_admin_options_container\">";
	print "		<div class=\"view_group\"><a href=\"/groups/id/$groupId\">View Group</a></div><div class=\"edit_group\"><a href=\"/showMyGroupEditor.php?groupId=$groupId\">Edit Group</a></div><div class=\"manage_group_members\"><a href=\"/manageGroupMembers.php?groupId=$groupId\">Group Members</a></div><div class=\"group_event_editor\"><a href=\"/groupEventEditorList.php?groupId=$groupId\">Group Events</a></div><div class=\"group_conversations\"><a href=\"/showGroupConversationsList.php?groupId=$groupId\">Group Conversations</a></div><div class=\"group_images\"><a href=\"/groupGalleryEditor.php?groupId=$groupId\">Group Images</a></div>";
	print "	</div>";
	
}

?>