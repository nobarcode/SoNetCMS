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
include("part_update_rootPath_user.php");
include("assets/core/config/part_ratings.php");

$id = sanitize_string($_REQUEST['id']);
$componentJb = sanitize_string($_REQUEST['componentJb']);
$documentType = sanitize_string($_REQUEST['documentType']);
$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);

//create user groups validation object
$userGroup = new CategoryUserGroupValidator();

//if the document is being updated, load its data
if (trim($id) != "") {
	
	$time = date("Y-m-d H:i:s", time());
	$b = htmlentities(unsanitize_string($b));
	
	//set document editing tracker
	$result = mysql_query("INSERT INTO documentEditTracking (documentType, id, username, date) VALUES ('document', '{$id}', '{$_SESSION['username']}', '{$time}') ON DUPLICATE KEY UPDATE date = '{$time}'");
	
	//get document editing tracker info
	$result = mysql_query("SELECT username, DATE_FORMAT(date, '%M %d, %Y %h:%i %p') AS newDate FROM documentEditTracking WHERE documentType = 'document' AND id = '{$id}' AND username != '{$_SESSION['username']}'");
	
	while ($row = mysql_fetch_object($result)) {
		
		$editTracking .= "			<tr><td>$row->username</td><td>$row->newDate</td></tr>\n";
		
	}
	
	if (trim($editTracking) != "") {
		
		$showEditTracking .= "<div id=\"edit_tracking_container\">\n";
		$showEditTracking .= "	<div class=\"edit_tracking_header\">Also Editing:</div>";
		$showEditTracking .= "	<div class=\"edit_tracking_body\">";
		$showEditTracking .= "		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n";
		$showEditTracking .= "			<tr><td class=\"edit_tracking_list_header\">Username</td><td class=\"edit_tracking_list_header\">Editing Date</td></tr>\n";
		$showEditTracking .= $editTracking;
		$showEditTracking .= "		</table>\n";
		$showEditTracking .= "		<div class=\"edit_tracking_options\">\n";
		$showEditTracking .= "			<span onClick=\"$('#edit_tracking_container').hide();\">Hide</span> | <span onClick=\"clearEditTracking();\">Clear</span>";
		$showEditTracking .= "		</div>\n";
		$showEditTracking .= "	</div>\n";
		$showEditTracking .= "</div>\n";
		
	}
	
	//load document
	$result = mysql_query("SELECT * FROM documents WHERE id = '{$id}' LIMIT 1");
		
	//catch ivalid ids
	if (mysql_num_rows($result) == 0) {print "invalid id"; exit;}
	
	$row = mysql_fetch_object($result);
	
	//validate user access level
	if ($row->publishState == "Published" && ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3)) {exit;}
	
	$documentType = sanitize_string($row->documentType);
	$category = sanitize_string($row->category);
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup->loadCategoryUserGroups($category);
	if (!$userGroup->allowEditing()) {exit;}
	
	$subcategory = sanitize_string($row->subcategory);
	$subject = sanitize_string($row->subject);
	
	$showRating = showOptions($ratingOptions, $row->rating);
	
	$showAuthor = " value=\"" . htmlentities($row->author) . "\"";
	$showTitle = " value=\"" . htmlentities($row->title) . "\"";
	$shortcut = $row->shortcut;
	$showShortcut = " value=\"" . $row->shortcut . "\"";
	
	if ($row->showDetails == 1) {
		
		$showDetailsChecked = " checked";
		
	}
	
	if ($row->showToolbar == 1) {
		
		$showToolbarChecked = " checked";
		
	}
	
	if ($row->showComments == 1) {
		
		$showCommentsChecked = " checked";
		
	}
	
	if ($row->requireAuthentication == 1) {
		
		$showRequireAuthenticationChecked = " checked";
		
	}
	
	if ($row->doNotSyndicate == 1) {
		
		$showDoNotSyndicateChecked = " checked";
		
	}
	
	if ($row->component == 1) {
		
		$showComponentChecked = " checked";
		
	}
	
	if (trim($row->summaryImage) != "") {
		
		$showSummaryImage = " value=\"" . htmlentities($row->summaryImage) . "\"";
		
	}
	
	if (trim($row->cssPath) != "") {
		
		$showCssPath = " value=\"" . htmlentities($row->cssPath) . "\"";
		$loadCssPath = htmlentities($row->cssPath);
		
	}
	
	$showDocumentSummary = htmlentities($row->summary);
	$showSummaryLinkText = " value=\"" . htmlentities($row->summaryLinkText) . "\"";
	$showKeywords = htmlentities($row->keywords);
	$showGalleryLinkText = " value=\"" . htmlentities($row->galleryLinkText) . "\"";
	$showGalleryLinkBackURL = " value=\"" . htmlentities($row->galleryLinkBackUrl) . "\"";
	$showGalleryLinkBackText = " value=\"" . htmlentities($row->galleryLinkBackText) . "\"";
	
	//standard url encoding for variables to take you back to where you came:
	$urlDocumentType = urlencode($_REQUEST['documentType']);
	$urlCategory = urlencode($_REQUEST['category']);
	$urlSubcategory = urlencode($_REQUEST['subcategory']);
	$urlSubject = urlencode($_REQUEST['subject']);
	$urlComponentJb = unsanitize_string($componentJb); //should already be urlencoded
	
	$htmlComponentJb = htmlentities(unsanitize_string($componentJb));
	
	//load versioning information
	$result = mysql_query("SELECT version, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i:%s %p') AS newDateCreated, usernameCreated FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'document' ORDER BY version DESC LIMIT 1");
	$row = mysql_fetch_object($result);

	$showVersioning .= "<div id=\"versioning_options\">\n";
	$showVersioning .= "	<div id=\"versioning_toolbar\">\n";
	$showVersioning .= "		<div class=\"current_version\"><b>Selected Version:</b> <span id=\"selected_version\">$row->version > $row->newDateCreated > $row->usernameCreated</span></div><div class=\"toggle_version_choices\"><a href=\"javascript:displayVersionOptions();\"><span id=\"versions_navigation\">Show Versions</span></a></div>";
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {
		
		$showVersioning .= "<div class=\"delete_versions\"><a href=\"javascript:deleteVersionAll();\" onclick=\"return confirm('Are you sure you want to delete all the versions associated with this document?');\">Delete All</a></div>";
		
	}
	
	$showVersioning .= "	</div>\n";
	$showVersioning .= "	<div id=\"version_choices\" style=\"display:none;\"></div>\n";
	$showVersioning .= "</div>\n";
	
	$setCurrentVersion = "\ncurrentVersion = '$row->version';";
	
	//load featuredDocument, focusedDocument, and publishState information
	$result = mysql_query("SELECT featuredDocuments.id as featuredDocument, focusedDocuments.id AS focusedDocument, documents.publishState FROM documents LEFT JOIN focusedDocuments ON focusedDocuments.id = documents.id LEFT JOIN featuredDocuments ON featuredDocuments.id = documents.id WHERE documents.id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);
	
	//featured
	if (trim($row->featuredDocument) == "") {
		
		$showFeatured = "Set Featured";
		
	} else {
		
		$showFeatured = "Remove Featured";
		
	}
	
	//focused
	if (trim($row->focusedDocument) == "") {
		
		$showFocused = "Set Focus";
		
	} else {
		
		$showFocused = "Remove Focus";
		
	}
	
	//published
	if ($row->publishState != 'Published') {
		
		$showPublishState = "Publish";
		
	} else {
		
		$showPublishState = "Unpublish";
		
	}
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
		
		$showEditorOptions = "<a class=\"button\" href=\"javascript:toggleFeatured($id);\" onclick=\"this.blur();\"><span id=\"featured\">$showFeatured</span></a>";
		$showEditorOptions .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"javascript:toggleFocused($id);\" onclick=\"this.blur();\"><span id=\"focused\">$showFocused</span></a>";
		$showEditorOptions .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"javascript:togglePublishState($id)\" onclick=\"this.blur(); return confirm('Are you sure you want to change the publishing status of this document?');\"><span id=\"publish_state_$id\">$showPublishState</span></a>";
		
	}
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
		
		$showEditorOptions .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"deleteDocument.php?id=$id\" onclick=\"this.blur(); return confirm('Are you sure you want to delete this document?');\"><span>Delete</span></a>";
		
	}
	
	$showBackToDocument = "<div id=\"back_to_document\"><a class=\"button\" href=\"galleryEditor.php?id=$id&documentType=$urlDocumentType&category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject&componentJb=$urlComponentJb\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Gallery</span></a>";
	
	if (trim($componentJb) == "") {
		
		$showBackToDocument .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"/documents/open/$shortcut\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Document</span></a>";
		
	} else {
		
		$showBackToDocument .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"$urlComponentJb\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Document</span></a>";
		
	}
	
	$showBackToDocument .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"documentManager.php?documentType=$urlDocumentType&category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject&componentJb=$urlComponentJb\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Document Manager</span></a></div>";
	
	$showDocumentId = "\n									<input type=\"hidden\" name=\"id\" value=\"$id\">";
	
} else {
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup->loadCategoryUserGroups($category);
	if (!$userGroup->allowEditing()) {exit;}
	
	//standard url encoding for variables to take you back to where you came:
	$urlDocumentType = urlencode($_REQUEST['documentType']);
	$urlCategory = urlencode($_REQUEST['category']);
	$urlSubcategory = urlencode($_REQUEST['subcategory']);
	$urlSubject = urlencode($_REQUEST['subject']);
	
	$showRating = showOptions($ratingOptions, '');
	
	$urlComponentJb = unsanitize_string($componentJb); //should alreadt be urlencoded
	
	$showSummaryImage = "no image selected";
	
	$showBackToDocument = "<div id=\"back_to_document\"><a class=\"button\" href=\"documentManager.php?documentType=$urlDocumentType&category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject&componentJb=$urlComponentJb\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Document Manager</span></a></div>";
	
}

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

include("part_rich_text_editor_config_document.php");

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Document Editor</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script><script language="javascript" src="/assets/core/resources/javascript/documentEditor.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/ckeditor/ckeditor.js"></script>
<script language="javascript">

id = '$id';
cssFile = '$loadCssPath';$setCurrentVersion

function initializeEditor(path) {
	
	if (typeof path == "undefined") {
		
		path = "";
		
	}
	
	if (path != "") {
		
		CKEDITOR.replace('documentBody', {
			filebrowserBrowseUrl : '/assets/core/resources/filemanager/index.html',
			filebrowserLinkBrowseUrl : '/browserChooserMain.php',
			filebrowserImageBrowseUrl : '/assets/core/resources/filemanager/index.html',
			filebrowserFlashBrowseUrl : '/assets/core/resources/filemanager/index.html',
			contentsCss: ['/assets/core/resources/css/main/rte.css', '/assets/core/resources/css/main/custom.css', path],
	$richTextEditorConfig
		});
		
	} else {
		
		CKEDITOR.replace('documentBody', {
			filebrowserBrowseUrl : '/assets/core/resources/filemanager/index.html',
			filebrowserLinkBrowseUrl : '/browserChooserMain.php',
			filebrowserImageBrowseUrl : '/assets/core/resources/filemanager/index.html',
			filebrowserFlashBrowseUrl : '/assets/core/resources/filemanager/index.html',
			contentsCss: ['/assets/core/resources/css/main/rte.css', '/assets/core/resources/css/main/custom.css'],
	$richTextEditorConfig
		});
		
	}
	
	CKEDITOR.instances.documentBody.on('instanceReady', function(){
		
		CKEDITOR.instances.documentBody.dataProcessor.htmlFilter.addRules ({
			
			text : function(data) {
				//find all bits in double brackets                       
				var matches = data.match(/\[\[(.*?)\]\]/g);
				
				//go through each match and replace the encoded characters
				if (matches != null) {
					
					for (i = 0; i < matches.length; i++) {
						
						var replacedString = matches[i];
						replacedString = matches[i].replace(/&quot;/g,'"');
						data = data.replace(matches[i],replacedString);
						
					}
					
				}
				
				return data;
				
			}
				
		});
		
	});
	
}

</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/documentEditor.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		$showEditTracking
		<div class="subheader_title">Document Editor</div>
		<form id="newDocumentForm">
			<div class="editor_box_container">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr valign="center"><td nowrap>Shortcut:</td><td width="100%"><input type="text" id="shortcut" name="shortcut"$showShortcut style="width:99%"></td></tr>
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
				<tr valign="center"><td nowrap>Author:</td><td width="100%"><input type="text" id="author" name="author"$showAuthor style="width:99%"></td></tr>
				<tr valign="center"><td nowrap>Title:</td><td width="100%"><input type="text" id="title" name="title"$showTitle style="width:99%"></td></tr>
				<tr valign="center"><td nowrap>Summary Image:</td><td width="100%"><input style="width:450px;" type="text" id="summaryImage" name="summaryImage"$showSummaryImage><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'summaryImage');" value="Browse"></td></tr>
				<tr valign="top"><td nowrap>Summary:</td><td width="100%"><textarea id="summary" name="summary" rows="5" style="width:99%;">$showDocumentSummary</textarea></td></tr>
				<tr valign="center"><td nowrap>Summary Link:</td><td width="100%"><input type="text" id="summaryLinkText" name="summaryLinkText"$showSummaryLinkText style="width:99%"></td></tr>
				<tr valign="top"><td nowrap>Keywords:</td><td width="100%"><textarea id="keywords" name="keywords" rows="5" style="width:99%;">$showKeywords</textarea></td></tr>
				<tr valign="center"><td nowrap>Gallery Link:</td><td width="100%"><input type="text" id="galleryLinkText" name="galleryLinkText"$showGalleryLinkText style="width:99%"></td></tr>
				<tr valign="center"><td nowrap>Gallery Link Back:</td><td width="100%"><input type="text" id="galleryLinkBackText" name="galleryLinkBackText"$showGalleryLinkBackText size="32"> URL: <input style="width:450px;" type="text" id="galleryLinkBackUrl" name="galleryLinkBackUrl"$showGalleryLinkBackURL><input style="margin-left:5px;" type="button" onclick="openDocumentManager('selectPath', 'galleryLinkBackUrl');" value="Browse"></td></tr>
				<tr valign="center"><td nowrap>CSS File:</td><td width="100%"><input style="width:450px;" type="text" id="cssPath" name="cssPath"$showCssPath><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPathCss', 'cssPath');" value="Browse"><input style="margin-left:5px;" type="button" onclick="clearCss();" value="Clear"></td></tr>
				<tr valign="top"><td nowrap>Options:</td><td width="100%">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr valign="top">
					<td>
						<input type="checkbox" id="showToolbar" name="showToolbar" value="1"$showToolbarChecked> Display a toolbar for this document<br>
						<input type="checkbox" id="showComments" name="showComments" value="1"$showCommentsChecked> Display the comments for this document<br>
						<input type="checkbox" id="requireAuthentication" name="requireAuthentication" value="1"$showRequireAuthenticationChecked> Require authentication to view this document
					</td>
					<td style="width:20px;"></td>
					<td>
						<input type="checkbox" id="doNotSyndicate" name="doNotSyndicate" value="1"$showDoNotSyndicateChecked> Do not syndicate this document<br>
						<input type="checkbox" id="component" name="component" value="1"$showComponentChecked> This document is a component
					</td>
					</tr>
				</table>
				</td></tr>
				</table>$showDocumentId
				<input type="hidden" id="oldShortcut" name="oldShortcut"$showShortcut>
				<input type="hidden" id="componentJb" name="componentJb" value="$htmlComponentJb">
			</div>
		</form>
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="loading_editor_message"><div>Loading editor, please wait...</div></div>
		<div id="editor_container" style="display:none;">
			<div id="documentBody">
			</div>
		</div>
		$showVersioning
		<div id="editor_options">$showEditorOptions$showBackToDocument</div>
	</div>
</body>
</html>
EOF;

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