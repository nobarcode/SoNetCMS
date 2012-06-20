<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$multipleId = sanitize_string($_REQUEST['multipleId']);

if (!is_array($multipleId) || trim($_SESSION['username']) == "") {$error = 1;}

if ($error != 1) {
	
	for ($x = 0; $x < count($multipleId); $x++) {
		
		mysql_query("DELETE blogs, documentVotes FROM blogs LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.id = '{$multipleId[$x]}' AND blogs.author = '{$_SESSION['username']}'");
		mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'blogComment' WHERE commentsDocuments.parentId = '{$multipleId[$x]}' AND commentsDocuments.type = 'blogComment'");
		
	}
	
}

?>