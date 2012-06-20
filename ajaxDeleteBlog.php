<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

if (trim($id) == "" || trim($_SESSION['username']) == "") {$error = 1;}

if ($error != 1) {
	
	$result = mysql_query("SELECT author FROM blogs WHERE id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['username'] == $row->author) {
		
		mysql_query("DELETE blogs, documentVotes FROM blogs LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.id = '{$id}'");
		mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'blogComment' WHERE commentsDocuments.parentId = '{$id}' AND commentsDocuments.type = 'blogComment'");
		
	}
	
}

?>