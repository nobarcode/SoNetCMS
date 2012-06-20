<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$type = sanitize_string($_REQUEST['type']);

if (trim($id) == "" || trim($type) == "") {$error = 1;}

if ($error != 1) {
	
	switch ($type) {
		
		case "documentComment":
			
			$result = mysql_query("SELECT username FROM commentsDocuments WHERE id = '{$id}'");
			$row = mysql_fetch_object($result);
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->username) {
				
				mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'documentComment' WHERE commentsDocuments.id = '{$id}' AND commentsDocuments.type = 'documentComment'");
				
			}
			
			break;
		
		case "documentImageComment":
			
			$result = mysql_query("SELECT username FROM commentsImages WHERE id = '{$id}'");
			$row = mysql_fetch_object($result);
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->username) {
				
				mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'documentImageComment' WHERE commentsImages.id = '{$id}' AND commentsImages.type = 'documentImageComment'");
				
			}
			
			break;
		
		case "blogComment":
			
			$result = mysql_query("SELECT username FROM commentsDocuments WHERE id = '{$id}'");
			$row = mysql_fetch_object($result);

			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->username) {
				
				mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'blogComment' WHERE commentsDocuments.id = '{$id}' AND commentsDocuments.type = 'blogComment'");
				
			}
			
			break;
			
		case "userProfileComment":
			
			$result = mysql_query("SELECT parentId, username FROM commentsUserProfiles WHERE id = '{$id}'");
			$row = mysql_fetch_object($result);
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->username || $_SESSION['username'] == $row->parentId) {
				
				mysql_query("DELETE commentsUserProfiles, documentVotes FROM commentsUserProfiles LEFT JOIN documentVotes ON documentVotes.parentId = commentsUserProfiles.id AND documentVotes.type = 'userProfileComment' WHERE commentsUserProfiles.id = '{$id}'");
				
			}
			
			break;
			
		case "userImageComment":
			
			$result = mysql_query("SELECT username FROM commentsImages WHERE id = '{$id}'");
			$row = mysql_fetch_object($result);
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->username) {
				
				mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'userImageComment' WHERE commentsImages.id = '{$id}' AND commentsImages.type = 'userImageComment'");
				
			}
			
			break;
			
		case "eventComment":

			$result = mysql_query("SELECT username FROM commentsDocuments WHERE id = '{$id}'");
			$row = mysql_fetch_object($result);
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->username) {
				
				mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment' WHERE commentsDocuments.id = '{$id}' AND commentsDocuments.type = 'eventComment'");
				
			}
			
			break;
		
		case "groupImageComment":
			
			$result = mysql_query("SELECT username FROM commentsImages WHERE id = '{$id}'");
			$row = mysql_fetch_object($result);
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->username) {
				
				mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'groupImageComment' WHERE commentsImages.id = '{$id}' AND commentsImages.type = 'groupImageComment'");
				
			}
			
			break;
			
	}
	
}

?>