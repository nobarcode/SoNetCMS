<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$multipleId = sanitize_string($_REQUEST['multipleId']);

if (!is_array($multipleId)) {exit;}

//load user group validator object
$userGroup = new CategoryUserGroupValidator();

for ($x = 0; $x < count($multipleId); $x++) {
	
	$result = mysql_query("SELECT category FROM events WHERE id = '{$multipleId[$x]}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	if ($userGroup->allowEditing()) {
		
		//delete the document's comments and any votes associated to each comment
		mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment' WHERE commentsDocuments.parentId = '{$multipleId[$x]}' AND commentsDocuments.type = 'eventComment'");

		//delete the document and its associated votes if there are any
		mysql_query("DELETE FROM events WHERE id = '{$multipleId[$x]}'");

		//delete document versioning information
		mysql_query("DELETE FROM documentVersioning WHERE parentId = '{$multipleId[$x]}' AND documentType = 'event'");
		
	}
	
}

?>