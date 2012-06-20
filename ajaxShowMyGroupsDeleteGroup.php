<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);

if (trim($groupId) == "" || trim($_SESSION['username']) == "") {$error = 1;}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to delete the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND memberLevel = '1' AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

if ($error != 1) {
	
	//delete the group's image gallery comments and any votes associated to each image gallery comment
	mysql_query("DELETE commentsImages, documentVotes, imagesGroups FROM imagesGroups LEFT JOIN commentsImages ON commentsImages.parentId = imagesGroups.parentId AND commentsImages.imageId = imagesGroups.id AND commentsImages.type = 'groupImageComment' LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'groupImageComment' WHERE imagesGroups.parentId = '{$groupId}'");
	
	//delete the group's conversations
	mysql_query("DELETE conversationsPosts, conversations FROM conversations INNER JOIN conversationsPosts ON conversationsPosts.parentId = conversations.id WHERE conversations.groupId = '{$groupId}'");
	
	//delete the group's events, event comments, and event comment votes
	mysql_query("DELETE commentsDocuments, documentVotes, events FROM events LEFT JOIN commentsDocuments ON commentsDocuments.parentId = events.id AND commentsDocuments.type = 'eventComment' LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment' WHERE events.groupId = '{$groupId}'");
	
	//delete the group and its members
	$result = mysql_query("DELETE groups, groupsMembers FROM groups LEFT JOIN groupsMembers ON groupsMembers.parentId = groups.id LEFT JOIN events ON events.groupId = groups.id WHERE groups.id = '{$groupId}'");

	if ($result) {
		
		//delete the user's personal directory
		$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
		deleteTree("$script_directory/cms_groups/$groupId");
		
	}
		
}

function deleteTree($dir) {
	
	$dir = rtrim($dir, '/');
	
	foreach(glob($dir . '/*') as $file) {
		
		if(is_dir($file)) {
			
			deleteTree($file);
			
		} else {
			
			unlink($file);
			mysql_query("DELETE FROM fileManager WHERE fsPath = '{$file}'");
			
		}
		
	}
	
	rmdir($dir);
	
}

?>