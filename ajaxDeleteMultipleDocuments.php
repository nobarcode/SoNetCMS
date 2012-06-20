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

//create user groups validation object
$userGroup = new CategoryUserGroupValidator();
$excludeCategories = $userGroup->viewCategoryExclusionList('documents');

for ($x = 0; $x < count($multipleId); $x++) {
	
	//delete the document and its associated votes if there are any
	mysql_query("DELETE documents, documentVotes FROM documents LEFT JOIN documentVotes ON documentVotes.parentId = documents.id AND documentVotes.type = 'document' WHERE documents.id = '{$multipleId[$x]}'$excludeCategories");
	
	if (mysql_affected_rows() > 0) {
		
		//delete the document's comments and any votes associated to each comment
		mysql_query("DELETE commentsDocuments, documentVotes FROM commentsDocuments LEFT JOIN documentVotes ON documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'documentComment' WHERE commentsDocuments.parentId = '{$multipleId[$x]}' AND commentsDocuments.type = 'documentComment'");
		
		//delete the document's image gallery comments and any votes associated to each image gallery comment
		mysql_query("DELETE commentsImages, documentVotes FROM commentsImages LEFT JOIN documentVotes ON documentVotes.parentId = commentsImages.id AND documentVotes.type = 'documentImageComment' WHERE commentsImages.parentId = '{$multipleId[$x]}' AND commentsImages.type = 'documentImageComment'");
		
		//delete the versioning information for each image related to this document
		$result = mysql_query("SELECT id FROM imagesDocuments WHERE parentId = '{$multipleId[$x]}'");
		while ($row = mysql_fetch_object($result)) {
			
			mysql_query("DELETE FROM documentVersioning WHERE parentId = '{$row->id}' AND documentType = 'documentImage'");
			
		}
		
		//delete the document's image gallery
		mysql_query("DELETE FROM imagesDocuments WHERE imagesDocuments.parentId = '{$multipleId[$x]}'");
		
		//delete document versioning information
		mysql_query("DELETE FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'document'");
		
		//delete document edit tracking data
		mysql_query("DELETE FROM documentEditTracking WHERE documentType = 'document' AND id = '{$id}'");
		
		deleteFeatured($multipleId[$x]);
		deleteFocused($multipleId[$x]);
		
	}
	
}

function deleteFeatured($id) {
	
	//delete any featured document's with this ID
	$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM featuredDocuments"), 0, "totalRows");
	$result = mysql_query("SELECT weight FROM featuredDocuments WHERE id = '{$id}'");
	
	if (mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_object($result);
		$weight = $row->weight;
		
		mysql_query("DELETE FROM featuredDocuments WHERE id = '{$id}'");
		
		for ($x = $weight + 1; $x <= $totalRows; $x++) {
			
			mysql_query("UPDATE featuredDocuments SET weight = (weight-1) WHERE weight = '{$x}'");
			
		}
		
	}
	
}

function deleteFocused($id) {
	
	//delete any focused document's with this ID
	$totalRows = mysql_result(mysql_query("SELECT COUNT(*) AS totalRows FROM focusedDocuments"), 0, "totalRows");
	$result = mysql_query("SELECT weight FROM focusedDocuments WHERE id = '{$id}'");
	
	if (mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_object($result);
		$weight = $row->weight;
		
		mysql_query("DELETE FROM focusedDocuments WHERE id = '{$id}'");
		
		for ($x = $weight + 1; $x <= $totalRows; $x++) {
			
			mysql_query("UPDATE focusedDocuments SET weight = (weight-1) WHERE weight = '{$x}'");
			
		}
		
	}
	
}

?>