<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$multipleId = sanitize_string($_REQUEST['multipleId']);

if (is_array($multipleId)) {
	
	for ($x = 0; $x < count($multipleId); $x++) {
		
		if (trim($multipleId[$x]) != "") {
			
			//check if the user being edited is the master account, if it is - make sure the user performing the edit is the master account
			$result = mysql_query("SELECT level FROM users WHERE username = '{$multipleId[$x]}'"); 
			$row = mysql_fetch_object($result);
			
			//if user to delete is not a master account, continue with deletion
			if ($row->level != 1) {
				
				//delete this user's friends
				mysql_query("DELETE FROM friends WHERE owner = '{$multipleId[$x]}'");
				
				//delete user from any user who has this user as a friend (regardless of status) and reorder their friend's list for them
				$result = mysql_query("SELECT owner FROM friends WHERE friend = '{$multipleId[$x]}'");
				
				while($row = mysql_fetch_object($result)) {
					
					$weight = mysql_result(mysql_query("SELECT weight FROM friends WHERE owner = '{$row->owner}' AND friend = '{$multipleId[$x]}'"), 0, "weight");
					$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM friends WHERE owner = '{$row->owner}'"), 0, "totalRows");
					
					if ($totalRows == 0) {$error = 1;}
					
					if ($error != 1) {
						
						mysql_query("DELETE FROM friends WHERE owner = '{$row->owner}' AND friend = '{$multipleId[$x]}'");
						
						for ($x = $weight + 1; $x <= $totalRows; $x++) {
							
							mysql_query("UPDATE friends SET weight = (weight-1) WHERE owner = '{$row->owner}' AND weight = '{$x}'");
							
						}
						
					}
					
				}
				
				//delete comments created by this user for blogs, documents, and events along with any votes associated with each comment
				mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'documentComment' WHERE commentsDocuments.username = '{$multipleId[$x]}' AND commentsDocuments.type = 'documentComment'");
				mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'blogComment' WHERE commentsDocuments.username = '{$multipleId[$x]}' AND commentsDocuments.type = 'blogComment'");
				mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment' WHERE commentsDocuments.username = '{$multipleId[$x]}' AND commentsDocuments.type = 'eventComment'");
				
				//delete comments created by this user for all gallery types along with any votes associated with each comment
				mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'documentImageComment' WHERE commentsImages.username = '{$multipleId[$x]}' AND commentsImages.type = 'documentImageComment'");
				mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'eventImageComment' WHERE commentsImages.username = '{$multipleId[$x]}' AND commentsImages.type = 'eventImageComment'");
				mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'userImageComment' WHERE commentsImages.username = '{$multipleId[$x]}' AND commentsImages.type = 'userImageComment'");
				
				//delete comments created by this user for any other user along with any votes associated with them
				mysql_query("DELETE commentsUserProfiles, documentVotes FROM commentsUserProfiles LEFT JOIN documentVotes ON documentVotes.parentId = commentsUserProfiles.id AND documentVotes.type = 'userProfileComment' WHERE commentsUserProfiles.username = '{$multipleId[$x]}'");
				
				//delete all comments attached to this user's blog (regardless of who created them) and any votes associated with them
				mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'blogComment' INNER JOIN blogs ON blogs.author = '{$multipleId[$x]}' WHERE commentsDocuments.parentId = blogs.id AND commentsDocuments.type = 'blogComment'");
				
				//delete all comments associated with this user's gallery along with any votes associated with each comment
				mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'userImageComment' WHERE commentsImages.parentId = '{$multipleId[$x]}'");
				
				//delete comments attached to this user's profile (regardless of who created the comment) and any votes associated with them
				mysql_query("DELETE commentsUserProfiles, documentVotes FROM commentsUserProfiles LEFT JOIN documentVotes ON documentVotes.parentId = commentsUserProfiles.id AND documentVotes.type = 'userProfileComment' WHERE commentsUserProfiles.parentId = '{$multipleId[$x]}'");
				
				//delete user's blogs and any votes associated to each blog
				mysql_query("DELETE blogs, documentVotes FROM blogs LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.author = '{$multipleId[$x]}'");
				
				//delete user's images
				mysql_query("DELETE imagesUsers FROM imagesUsers WHERE parentId = '{$multipleId[$x]}'");
				
				//delete user's messages
				mysql_query("DELETE FROM messages WHERE toUser = '{$multipleId[$x]}'");
				
				//delete user's votes
				mysql_query("DELETE FROM documentVotes WHERE username = '{$multipleId[$x]}'");
				
				//delete groups created by user
				$result = mysql_query("SELECT parentId FROM groupsMembers WHERE username = '{$multipleId[$x]}' AND memberLevel = '1' AND status = 'approved'");
				deleteGroup($result);
				
				//delete the group conversation posts created by user
				mysql_query("DELETE FROM conversationsPosts WHERE author = '{$multipleId[$x]}'");
				
				//delete group conversation root threads too
				mysql_query("DELETE FROM conversations WHERE author = '{$multipleId[$x]}'");
				
				//delete user from group member list
				mysql_query("DELETE FROM groupsMembers WHERE username = '{$multipleId[$x]}'");
				
				//delete the user's autosave data
				mysql_query("DELETE FROM autoSaveContent WHERE username = '{$multipleId[$x]}'");
				
				//delete the user's profile
				mysql_query("DELETE FROM users WHERE username = '{$multipleId[$x]}'");
				
				//delete the user's personal directory
				$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
				mysql_query("DELETE FROM fileManager WHERE fsPath LIKE BINARY '{$script_directory}/cms_users/{$multipleId[$x]}%'");
				deleteTree("$script_directory/cms_users/$multipleId[$x]");
				
			}
			
		}
		
	}
	
}

function deleteGroup($result) {
	
	while($row = mysql_fetch_object($result)) {

		//delete the group's image gallery comments and any votes associated to each image gallery comment
		mysql_query("DELETE commentsImages, documentVotes, imagesGroups FROM imagesGroups LEFT JOIN commentsImages ON commentsImages.parentId = imagesGroups.parentId AND commentsImages.imageId = imagesGroups.id AND commentsImages.type = 'groupImageComment' LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'groupImageComment' WHERE imagesGroups.parentId = '{$row->parentId}'");
		
		//delete the group's conversations
		mysql_query("DELETE conversationsPosts, conversations FROM conversations INNER JOIN conversationsPosts ON conversationsPosts.parentId = conversations.id WHERE conversations.groupId = '{$row->parentId}'");
		
		//delete the group's events, event comments, and event comment votes
		mysql_query("DELETE commentsDocuments, documentVotes, events FROM events LEFT JOIN commentsDocuments ON commentsDocuments.parentId = events.id AND commentsDocuments.type = 'eventComment' LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment' WHERE events.groupId = '{$row->parentId}'");
		
		//delete the group and its members
		$test = mysql_query("DELETE groups, groupsMembers FROM groups LEFT JOIN groupsMembers ON groupsMembers.parentId = groups.id LEFT JOIN events ON events.groupId = groups.id WHERE groups.id = '{$row->parentId}'");
		
		if ($test) {
			
			//delete the user's personal directory
			$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
			mysql_query("DELETE FROM fileManager WHERE fsPath LIKE BINARY '{$script_directory}/cms_groups/{$row->parentId}%'");
			deleteTree("$script_directory/cms_groups/$row->parentId");
			
		}
		
	}
	
}

function deleteTree($dir,$deleteRootToo=true) {
	
	if(!$dh = @opendir($dir)) {
		
		return;
		
	}
	
	while(false !== ($obj = readdir($dh))) {
		
		if($obj == '.' || $obj == '..') {
			
			continue;
			
		}
		 
		if(!@unlink($dir . '/' . $obj)) {
			
			deleteTree($dir . '/' . $obj, true);
			
		}
		
	}

	closedir($dh);

	if($deleteRootToo) {
		
		@rmdir($dir);
		
	}
	
	return(true);
	
}

//I've seen some behavior where more than just the directory that was
//passed is deleted. (i.e. passed: /dir/dir1/dir2/ and everything in
///dir2 is deleted as well as everything in /dir1) The function below
//will be temprarily replaced by the function above for testing.

//Affects:
// ajaxDeleteGroup
// ajaxDeleteMultipleGroups
// ajaxDeleteMultipleUsers
// ajaxDeleteUser
// deleteGroup

//function deleteTree($dir) {
//	
//	$dir = rtrim($dir, '/');
//	
//	foreach(glob($dir . '/*') as $file) {
//		
//		if(is_dir($file)) {
//			
//			deleteTree($file);
//			
//		} else {
//			
//			unlink($file);
//			mysql_query("DELETE FROM fileManager WHERE fsPath = '{$file}'");
//			
//		}
//		
//	}
//	
//	rmdir($dir);
//	
//}

?>