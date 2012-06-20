<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");
include("part_update_rootPath_user.php");
include("assets/core/config/part_ratings.php");

$id = sanitize_string($_REQUEST['id']);

//create user groups validation object
$userGroup = new CategoryUserGroupValidator();

//check if document is being edited
if (trim($id) != "") {
	
	$result = mysql_query("SELECT * FROM blogs WHERE id = '{$id}' LIMIT 1");
	
	//catch ivalid ids
	if (mysql_num_rows($result) == 0) {print "invalid id"; exit;}
	
	$row = mysql_fetch_object($result);
	
	//if the user is not an admin or the usernameCreated, exit
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['username'] != $row->usernameCreated) {exit;}
	
	$documentType = sanitize_string($row->documentType);
	$category = sanitize_string($row->category);	
	$subcategory = sanitize_string($row->subcategory);
	$subject = sanitize_string($row->subject);
	
	$showRating = showOptions($ratingOptions, $row->rating);
	
	$showTitle = " value=\"" . htmlentities($row->title) . "\"";
	
	if (trim($row->summaryImage) != "") {
		
		$showSummaryImage = " value=\"" . htmlentities($row->summaryImage) . "\"";
		
	}
	
	$showDocumentSummary = htmlentities($row->summary);
	$showKeywords = htmlentities($row->keywords);
	$showLinkText = " value=\"" . htmlentities($row->linkText) . "\"";
	$showCustomHeader = $row->customHeader;
	
	if ($row->publishState != 'Published') {
		
		$showPublishState = "Publish";
		
	} else {
		
		$showPublishState = "Unpublish";
		
	}
	
	$showEditorOptions .= "<a class=\"button\" href=\"javascript:togglePublishState($id)\" onclick=\"this.blur(); return confirm('Are you sure you want to change the publishing status of this blog?');\"><span id=\"publish_state_$id\">$showPublishState</span></a>";
	$showEditorOptions .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"deleteBlog.php?id=$id\" onclick=\"this.blur(); return confirm('Are you sure you want to delete this blog?');\"><span>Delete</span></a>";
	
	$showBackToDocument = "<div id=\"back_to_document\"><a class=\"button\" href=\"/blogs/id/$id\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Blog</span></a>";
	$showBackToDocument .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"showMyBlog.php\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>My Blogs</span></a></div>";
	
	$showDocumentId = "\n									<input type=\"hidden\" name=\"id\" value=\"$id\">";
	
} else {
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup->loadCategoryUserGroups($category);
	if (!$userGroup->allowEditing()) {exit;}
	
	include("assets/core/config/part_default_header_blog.php");
	
	$showRating = showOptions($ratingOptions, '');
	
	$showBackToDocument = "<div id=\"back_to_document\"><a class=\"button\" href=\"showMyBlog.php\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>My Blogs</span></a></div>";
	
}

//if user is an admin don't hide userSelectable
if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 || $_SESSION['userLevel'] == 4) {
	
	$userSelectable = " AND userSelectable = '1'";
	
}

//build document type list
$result = mysql_query("SELECT * FROM documentTypes WHERE 1 $userSelectable ORDER BY weight");

while ($row = mysql_fetch_object($result)) {
	
	$showDocumentType = htmlentities($row->documentType);
	
	if ($row->documentType != unsanitize_string($documentType)) {
		
		$documentTypeList .= "\n									<option value=\"$showDocumentType\">$showDocumentType</option>";
		
	} else {
		
		$documentTypeList .=  "\n									<option value=\"$showDocumentType\" selected>$showDocumentType</option>";
		
	}
	
}

//build category list
$result = mysql_query("SELECT * FROM categories WHERE 1$userSelectable ORDER BY weight");

while ($row = mysql_fetch_object($result)) {
	
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	
	if ($userGroup->allowRead()) {
		
		$showCategory = htmlentities($row->category);
		
		if ($row->category != unsanitize_string($category)) {

			$categoryList .= "\n									<option value=\"" . htmlentities($row->category) . "\">$showCategory</option>";

		} else {

			$categoryList .= "\n									<option value=\"" . htmlentities($row->category) . "\" selected>$showCategory</option>";

		}
		
	}
	
}

//build subcateogries
$result = mysql_query("SELECT * FROM subcategories WHERE category = '{$category}'$userSelectable ORDER BY weight");

while ($row = mysql_fetch_object($result)) {

	$showSubcategory = htmlentities($row->subcategory);

	if ($row->subcategory != unsanitize_string($subcategory)) {

		$subcategoryList .= "\n									<option value=\"$showSubcategory\">$showSubcategory</option>";

	} else {

		$subcategoryList .= "\n									<option value=\"$showSubcategory\" selected>$showSubcategory</option>";

	}

}

//build subjects
$result = mysql_query("SELECT * FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}'$userSelectable ORDER BY weight");

while ($row = mysql_fetch_object($result)) {

	$showSubject = htmlentities($row->subject);

	if ($row->subject != unsanitize_string($subject)) {

		$subjectList .= "\n									<option value=\"$showSubject\">$showSubject</option>";

	} else {

		$subjectList .= "\n									<option value=\"$showSubject\" selected>$showSubject</option>";

	}

}

include("part_rich_text_editor_config_blog.php");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showMyBlogEditor.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showMyBlogEditor.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/ckeditor/ckeditor.js"></script>
<script language="javascript">

id = '$id';

function initializeEditor() {
	
	CKEDITOR.replace('documentBody', {
		filebrowserBrowseUrl: '/assets/core/resources/filemanager/index.html',
$richTextEditorConfig				
	});
	
}

function initializeCustomHeaderEditor() {
	
	CKEDITOR.replace('customHeader', {
		filebrowserBrowseUrl: '/assets/core/resources/filemanager/index.html',
		customConfig : '/assets/core/resources/javascript/ckeditor/config_custom_header_blog.js'
	});
	
}

</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div class="subheader_title">Blog Editor</div>
			<div class="editor_box_container">
				<form id="newDocumentForm">
					<table border="0" cellspacing="0" cellpadding="2" width="100%">
						<tr valign="center"><td nowrap>Type:</td><td width="100%"><select id="documentTypes" name="documentType">
						<option value="">Please Select</option>$documentTypeList
						</select></td></tr>
						<tr valign="center"><td nowrap>Category:</td><td width="100%"><select id="categories" name="category">
						<option value="">Please Select</option>$categoryList
						</select></td></tr>
						<tr valign="center"><td nowrap>Subcategory:</td><td width="100%"><select id="subcategories" name="subcategory">
						<option value="">Select a category above</option>$subcategoryList
						</select></td></tr>
						<tr valign="center"><td nowrap>Subject:</td><td width="100%"><select id="subjects" name="subject">
						<option value="">Select a category above</option>$subjectList
						</select></td></tr>
						<tr valign="center"><td nowrap>Rating:</td><td width="100%"><select id="rating" name="rating">
						<option value="">No Rating</option>$showRating
						</select></td></tr>
						<tr valign="center"><td nowrap>Title:</td><td width="100%"><input type="text" id="title" name="title"$showTitle style="width:99%"></td></tr>
						<tr valign="center"><td nowrap>Summary Image:</td><td width="100%"><input style="width:450px;" type="text" id="summaryImage" name="summaryImage"$showSummaryImage><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'summaryImage');" value="Browse"></td></tr>
						<tr valign="top"><td nowrap>Summary:</td><td width="100%"><textarea id="summary" name="summary" rows="5" style="width:99%;">$showDocumentSummary</textarea></td></tr>
						<tr valign="top"><td nowrap>Keywords:</td><td width="100%"><textarea id="keywords" name="keywords" rows="5" style="width:99%;">$showKeywords</textarea></td></tr>
						<tr valign="top"><td nowrap>Header:</td>
						<td width="100%">
<div id="customHeader">
$showCustomHeader
</div>
<input type="button" id="activate_custom_header_editor" value="Advanced Header Customization" onClick="toggleCustomHeaderEditor();">
						</td>
						</tr>
					</table>$showDocumentId
				</form>
			</div>
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div id="loading_editor_message"><div>Loading editor, please wait...</div></div>
			<div id="editor_container" style="display:none;">
				<div id="documentBody"></div>
			</div>
			<div id="editor_options">$showEditorOptions$showBackToDocument</div>
EOF;

$site_container->showSiteContainerBottom();

function showOptions($options, $selected) {
	
	for($x = 0; $x < count($options); $x++) {
		
		if (trim($selected) != "" && $selected == $x) {
			
			$return .= "<option value=\"" . $x . "\" selected>" . htmlentities($options[$x]) . "</option>";
			
		} else {
			
			$return .= "<option value=\"" . $x . "\">" . htmlentities($options[$x]) . "</option>";
			
		}
		
	}
	
	return($return);
		
}

?>