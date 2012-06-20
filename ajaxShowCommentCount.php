<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$elementId = sanitize_string($_REQUEST['elementId']);
$type = sanitize_string($_REQUEST['type']);
$id = sanitize_string($_REQUEST['id']);

if (trim($elementId) == "" || trim($type) == "" || trim($id) == "") {
	
	exit;
	
}

switch ($type) {
	
	case "document":
		
		$result = mysql_query("SELECT id FROM commentsDocuments WHERE parentId = '{$id}' AND type = 'documentComment'");
		$count = mysql_num_rows($result);
		break;
		
	case "blog":
		
		$result = mysql_query("SELECT id FROM commentsDocuments WHERE parentId = '{$id}' AND type = 'blogComment'");
		$count = mysql_num_rows($result);
		break;
		
}

header('Content-type: application/javascript');
print "$('#$elementId').html('$count');";

?>