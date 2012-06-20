<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//create user groups validation object
$userGroup = new CategoryUserGroupValidator();

//build document type list
$result = mysql_query("SELECT * FROM documentTypes ORDER BY weight");

while ($row = mysql_fetch_object($result)) {
	
	$showDocumentType = htmlentities($row->documentType);
	
	if ($row->documentType != unsanitize_string($documentType)) {
		
		$documentTypeList .= "\n									<option value=\"$showDocumentType\">$showDocumentType</option>";
		
	} else {
		
		$documentTypeList .=  "\n									<option value=\"$showDocumentType\" selected>$showDocumentType</option>";
		
	}
	
}

//build category list
$result = mysql_query("SELECT * FROM categories WHERE 1 ORDER BY weight");

while ($row = mysql_fetch_object($result)) {
	
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	
	if ($userGroup->allowEditing()) {
		
		$showCategory = htmlentities($row->category);
		
		if ($row->category != unsanitize_string($category)) {

			$categoryList .= "\n									<option value=\"" . htmlentities($row->category) . "\">$showCategory</option>";

		} else {

			$categoryList .= "\n									<option value=\"" . htmlentities($row->category) . "\" selected>$showCategory</option>";

		}
		
	}
	
}

//build subcateogries
$result = mysql_query("SELECT * FROM subcategories WHERE category = '{$category}' ORDER BY weight");

while ($row = mysql_fetch_object($result)) {

	$showSubcategory = htmlentities($row->subcategory);

	if ($row->subcategory != unsanitize_string($subcategory)) {

		$subcategoryList .= "\n									<option value=\"$showSubcategory\">$showSubcategory</option>";

	} else {

		$subcategoryList .= "\n									<option value=\"$showSubcategory\" selected>$showSubcategory</option>";

	}

}

//build subjects
$result = mysql_query("SELECT * FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}' ORDER BY weight");

while ($row = mysql_fetch_object($result)) {

	$showSubject = htmlentities($row->subject);

	if ($row->subject != unsanitize_string($subject)) {

		$subjectList .= "\n									<option value=\"$showSubject\">$showSubject</option>";

	} else {

		$subjectList .= "\n									<option value=\"$showSubject\" selected>$showSubject</option>";

	}

}

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Document Browser</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/selectDocument.js"></script>

<style>
@import url("/assets/core/resources/css/admin/selectDocument.css");
</style>

</head>
<body>
	<div id="body_container">
		<div id="body_inner">
			<div class="editor_query_options">
				<div id="loading">Loading...</div>
				<form id="query_filter" style="margin:0;">
				<table border="0" cellspacing="0" cellpadding="2">
				<tr valign="center"><td nowrap>Type:</td><td width="100%"><select id="documentType" name="documentType">
				<option value="">All</option>$documentTypeList
				</select></td></tr>
				<tr valign="center"><td nowrap>Category:</td><td width="100%"><select id="categories" name="category">
				<option value="">All</option>$categoryList
				</select></td></tr>
				<tr valign="center"><td nowrap>Subcategory:</td><td width="100%"><select id="subcategories" name="subcategory">
				<option value="">All</option>$subcategoryList
				</select></td></tr>
				<tr valign="center"><td nowrap>Subject:</td><td width="100%"><select id="subjects" name="subject">
				<option value="">All</option>$subjectList
				</select></td></tr>
				<tr valign="center"><td nowrap>Filter:</td><td width="100%"><select id="filterType" name="filterType"><option value="status">Status</option><option value="title">Title</option><option value="date">Date</option><option value="author">Author</option></select> <input type="text" id="filterValue" name="filterValue"> <input type="submit" value="Apply"></td></tr>
				</table>
				</form>
			</div>
			<div id="document_list">
			</div>
		</div>
	</div>
</body>
</html>
EOF;

?>