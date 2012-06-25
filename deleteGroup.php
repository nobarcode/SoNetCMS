<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);

if (trim($groupId) == "" || trim($_SESSION['username']) == "") {exit;}

//validate group
$result = mysql_query("SELECT id FROM groups WHERE id = '{$groupId}'");

if (mysql_num_rows($result) == 0) {

	exit;

}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND memberLevel = '1' AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));

//log file
if (file_exists("$script_directory/assets/logs/delete_group.html")) {
	
	if (filesize("$script_directory/assets/logs/delete_group.html") > 524288) {
		
		unlink("$script_directory/assets/logs/delete_group.html");
		
	}
	
}

$handle = fopen("$script_directory/assets/logs/delete_group.html", "a");
fwrite($handle, "#" . date(DATE_ATOM) . " by " . $_SESSION['username'] . "@" . $_SERVER['REMOTE_ADDR'] . "<br>");

//delete the group's image gallery comments and any votes associated to each image gallery comment
mysql_query("DELETE commentsImages, documentVotes, imagesGroups FROM imagesGroups LEFT JOIN commentsImages ON commentsImages.parentId = imagesGroups.parentId AND commentsImages.imageId = imagesGroups.id AND commentsImages.type = 'groupImageComment' LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'groupImageComment' WHERE imagesGroups.parentId = '{$groupId}'");

//delete the group's conversations
mysql_query("DELETE conversationsPosts, conversations FROM conversations INNER JOIN conversationsPosts ON conversationsPosts.parentId = conversations.id WHERE conversations.groupId = '{$groupId}'");

//delete the group's event, event comments, and event comment votes
mysql_query("DELETE commentsDocuments, documentVotes, events FROM events LEFT JOIN commentsDocuments ON commentsDocuments.parentId = events.id AND commentsDocuments.type = 'eventComment' LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment' WHERE events.groupId = '{$groupId}'");

//delete the group and its members
$test = mysql_query("DELETE groups, groupsMembers FROM groups LEFT JOIN groupsMembers ON groupsMembers.parentId = groups.id WHERE groups.id = '{$groupId}'");

if ($test) {
	
	//delete the user's personal directory
	$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	mysql_query("DELETE FROM fileManager WHERE fsPath LIKE BINARY '{$script_directory}/cms_groups/{$groupId}/%'");
	
	//log file
	fwrite($handle, "<br>#GROUP: " . $groupId . "<br>");
	
	deleteTree($handle, "$script_directory/cms_groups/$groupId");
	
}

//log file
fwrite($handle, "<br><hr><br>");

header("location: showMyGroups.php");

function deleteTree($handle, $dir) {
	
	$dir = rtrim($dir, '/');
	
	foreach(glob($dir . '/*') as $file) {
		
		if(is_dir($file)) {
			
			deleteTree($handle, $file);
			
		} else {
			
			unlink($file);
			
			//log file
			fwrite($handle, " -- (f) " . date(DATE_ATOM) . " unlink: " . $file . "<br>");
			
		}
		
	}
	
	rmdir($dir);
	
	//log file
	fwrite($handle, " -- (d) " . date(DATE_ATOM) . " rmdir: " . $dir . "<br>");
	
}

?>