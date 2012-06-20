<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);
$subjectId = sanitize_string($_REQUEST['subjectId']);
$and = sanitize_string($_REQUEST['and']);

$documentCountTotal = showDocumentCount($category, $subcategory, $subject, '');
$documentCountPublished = showDocumentCount($category, $subcategory, $subject, ' AND publishState = \'Published\'');
$documentCountUnpublished = showDocumentCount($category, $subcategory, $subject, ' AND publishState = \'Unpublished\'');

header('Content-type: application/javascript');
print "$('#title_" . $subjectId . "').title = 'total: $documentCountTotal | published: $documentCountPublished | unpublished: $documentCountUnpublished';";

function showDocumentCount($category, $subcategory, $subject, $and) {
	
	$result = mysql_query("SELECT * FROM documents WHERE category = '{$category}' AND subcategory = '{$subcategory}' AND subject = '{$subject}'$and");
	$count = mysql_num_rows($result);
		
	return($count);
	
}


?>