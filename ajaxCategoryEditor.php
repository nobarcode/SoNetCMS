<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//Output new list via AJAX
$result = mysql_query("SELECT * FROM categories ORDER BY weight");
$count = mysql_num_rows($result);

if ($count == 0) {
	
	print "<div class=\"category_container\"><div class=\"handle\"><div class=\"name\">No categories currently exist.</div></div></div>";
	exit;
	
}

$userGroup = new CategoryUserGroupValidator();

while ($row = mysql_fetch_object($result)) {
	
	$category = htmlentities($row->category);
	$urlCategory = urlencode($row->category);
	
	$escapeCategory = preg_replace('/\\\/', '\\\\\\', $category);
	$escapeCategory = preg_replace('/\'/', '\\\'', $escapeCategory);
	
	$documentCountTotal = showDocumentCount($row->category, 'document', '');
	$documentCountPublished = showDocumentCount($row->category, 'document', ' AND publishState = \'Published\'');
	$documentCountUnpublished = showDocumentCount($row->category, 'document', ' AND publishState = \'Unpublished\'');
	
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	
	if ($userGroup->allowEditing()) {
		
		print "<div id=\"category_$row->id\" class=\"category_container\"><div class=\"handle\"><div class=\"name\"><a id=\"title_$row->id\" href=\"javascript:initEditCategory('$escapeCategory', 'title_$row->id');\" title=\"total: $documentCountTotal | published: $documentCountPublished | unpublished: $documentCountUnpublished\">$category</a></div><div class=\"toolbar\"><div class=\"subcategories\"><a href=\"subcategoryEditor.php?category=$urlCategory\">Subcategories</a></div>";

		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {

			print "<div class=\"options\"><a href=\"javascript:initEditCategoryOptions('$row->id');\">Options</a></div>";

		}

		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {

			print "<div class=\"groups\"><a href=\"javascript:initEditCategoryUserGroups('$row->id');\">Groups</a></div><div class=\"delete\"><a href=\"javascript:deleteCategory('$escapeCategory');\" onClick=\"return confirm('Are you sure you want to delete this category?');\">Delete</a></div>";

		}

		print "</div></div></div>\n";
		
	}
	
}

function showDocumentCount($category, $type, $and) {
	
	$category = sanitize_string($category);
	
	if ($type == 'document') {
		
		$result = mysql_query("SELECT * FROM documents WHERE category = '{$category}'$and");
		$count = mysql_num_rows($result);
		
	}
	
	return($count);
	
}

?>