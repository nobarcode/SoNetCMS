<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//Output new list via AJAX
$result = mysql_query("SELECT * FROM documentTypes ORDER BY weight");
$count = mysql_num_rows($result);

if ($count == 0) {
	
	print "<div class=\"document_type_container\"><div class=\"handle\"><div class=\"name\">No document types currently exist.</div></div></div>";
	exit;
	
}

while ($row = mysql_fetch_object($result)) {
	
	$documentType = htmlentities($row->documentType);
	
	$escapeDocumentType = preg_replace('/\\\/', '\\\\\\', $documentType);
	$escapeDocumentType = preg_replace('/\'/', '\\\'', $escapeDocumentType);
	
	print "<div id=\"document_type_$row->id\" class=\"document_type_container\"><div class=\"handle\"><div class=\"name\"><a id=\"title_$row->id\" class=\"document_type_data\" href=\"javascript:initEditDocumentType('$escapeDocumentType', 'title_$row->id');\">$documentType</a></div><div class=\"toolbar\">";
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
		
		print "<div class=\"options\"><a href=\"javascript:initEditDocumentTypeOptions('$row->id');\">Options</a></div>";
		
	}
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {
		
		print "<div class=\"delete\"><a href=\"javascript:deleteDocumentType('$escapeDocumentType');\" onClick=\"return confirm('Are you sure you want to delete this document type?');\">Delete</a></div>";
		
	}
	
	print "</div></div></div>\n";

}

?>