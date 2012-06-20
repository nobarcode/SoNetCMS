<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);
if (!$userGroup->allowEditing()) {exit;}

//Output new list via AJAX
$result = mysql_query("SELECT * FROM subcategories WHERE category = '{$category}' ORDER BY weight");
$count = mysql_num_rows($result);

if ($count == 0) {
	
	print "<div class=\"subcategory_container\"><div class=\"handle\"><div class=\"name\">No subcategories currently exist.</div></div></div>";
	exit;
	
}

while ($row = mysql_fetch_object($result)) {
	
	$category = htmlentities($row->category);
	$urlCategory = urlencode($row->category);
	
	$subcategory = htmlentities($row->subcategory);
	$urlSubcategory = urlencode($row->subcategory);
	
	$escapeCategory = preg_replace('/\\\/', '\\\\\\', $category);
	$escapeCategory = preg_replace('/\'/', '\\\'', $escapeCategory);
	
	$escapeSubcategory = preg_replace('/\\\/', '\\\\\\', $subcategory);
	$escapeSubcategory = preg_replace('/\'/', '\\\'', $escapeSubcategory);
	
	$documentCountTotal = showDocumentCount($row->category, $row->subcategory, 'document', '');
	$documentCountPublished = showDocumentCount($row->category, $row->subcategory, 'document', ' AND publishState = \'Published\'');
	$documentCountUnpublished = showDocumentCount($row->category, $row->subcategory, 'document', ' AND publishState = \'Unpublished\'');

	print "<div id=\"subcategory_$row->id\" class=\"subcategory_container\"><div class=\"handle\"><div class=\"name\"><a id=\"title_$row->id\" class=\"subcategory_data\" href=\"javascript:initEditSubcategory('$escapeCategory', '$escapeSubcategory', 'title_$row->id');\" title=\"total: $documentCountTotal | published: $documentCountPublished | unpublished: $documentCountUnpublished\">$subcategory</a></div><div class=\"toolbar\"><div class=\"subjects\"><a href=\"subjectEditor.php?category=$urlCategory&subcategory=$urlSubcategory\">Subjects</a></div>";
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
		
		print "<div class=\"options\"><a href=\"javascript:initEditSubcategoryOptions('$row->id');\">Options</a></div>";
		
	}
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {
		
		print "<div class=\"delete\"><a href=\"javascript:deleteSubcategory('$escapeCategory', '$escapeSubcategory');\" onClick=\"return confirm('Are you sure you want to delete this subcategory?');\">Delete</a></div>";
		
	}
	
	print "</div></div></div>\n";

}

function showDocumentCount($category, $subcategory, $type, $and) {
	
	$category = sanitize_string($category);
	$subcategory = sanitize_string($subcategory);
	
	if ($type == 'document') {
		
		$result = mysql_query("SELECT * FROM documents WHERE category = '{$category}' AND subcategory = '{$subcategory}'$and");
		$count = mysql_num_rows($result);
		
	}
	
	return($count);
	
}

?>