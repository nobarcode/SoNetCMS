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

$id = sanitize_string($_REQUEST['id']);
$componentJb = sanitize_string($_REQUEST['componentJb']);

//catch invalid query (no $id, etc)
if (trim($id) == "") {exit;}

$time = date("Y-m-d H:i:s", time());

//set document editing tracker
$result = mysql_query("INSERT INTO documentEditTracking (documentType, id, username, date) VALUES ('document', '{$id}', '{$_SESSION['username']}', '{$time}') ON DUPLICATE KEY UPDATE date = '{$time}'");

//load shorutcut, category, and subcategory info from parent document
$result = mysql_query("SELECT shortcut, category, subcategory, subject, publishState FROM documents WHERE id = '{$id}' LIMIT 1");

//catch ivalid ids
if (mysql_num_rows($result) == 0) {print "invalid id"; exit;}

$row = mysql_fetch_object($result);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
if (!$userGroup->allowEditing()) {exit;}

//validate user access level
if ($row->publishState == "Published" && ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3)) {exit;}

$shortcut = $row->shortcut;

//standard url encoding for variables to take you back to where you came:
$urlDocumentType = urlencode($_REQUEST['documentType']);
$urlCategory = urlencode($_REQUEST['category']);
$urlSubcategory = urlencode($_REQUEST['subcategory']);
$urlSubject = urlencode($_REQUEST['subject']);
$urlComponentJb = urlencode(unsanitize_string($componentJb));

//set component jumpback
$urlComponentJb = urlencode($componentJb);

$showVersioning .= "<div id=\"versioning_options\" style=\"display:none;\">\n";
$showVersioning .= "	<div id=\"versioning_toolbar\">\n";
$showVersioning .= "		<div class=\"current_version\"><b>Selected Version:</b> <span id=\"selected_version\"></span></div><div class=\"toggle_version_choices\"><a href=\"javascript:displayVersionOptions();\"><span id=\"versions_navigation\">Show Versions</span></a></div>";

if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {
	
	$showVersioning .= "<div class=\"delete_versions\"><a href=\"javascript:deleteVersionAll();\" onclick=\"return confirm('Are you sure you want to delete all the versions associated with this document?');\">Delete All</a></div>";
	
}

$showVersioning .= "	</div>\n";
$showVersioning .= "	<div id=\"version_choices\" style=\"display:none;\"></div>\n";
$showVersioning .= "</div>\n";

$showBackToDocument = "<div id=\"back_to_document\"><a class=\"button\" href=\"/documentEditor.php?id=$id&documentType=$urlDocumentType&category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject&componentJb=$urlComponentJb\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Edit Document</span></a>";

if (trim($componentJb) == "") {
	
	$showBackToDocument .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"/documents/open/$shortcut\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Document</span></a>";
	
} else {
	
	$showBackToDocument .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"$urlComponentJb\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Document</span></a>";
	
}

$showBackToDocument .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"documentManager.php?documentType=$urlDocumentType&category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject&componentJb=$urlComponentJb\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Document Manager</span></a></div>";

include("part_rich_text_editor_config_gallery.php");

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Gallery Editor</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/galleryEditor.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/ckeditor/ckeditor.js"></script>
<script language="javascript">

function initializeEditor() {
	
	CKEDITOR.replace('documentBody', {
		on : {
			
			instanceReady : function() {
				
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
				
				displayEditor();
				
			}
			
		},
		filebrowserBrowseUrl : '/assets/core/resources/filemanager/index.html',
		filebrowserLinkBrowseUrl : '/browserChooserMain.php',
		filebrowserImageBrowseUrl : '/assets/core/resources/filemanager/index.html',
	    filebrowserFlashBrowseUrl : '/assets/core/resources/filemanager/index.html',
$richTextEditorConfig
	});
	
}

id = '$id';
last_s = 0;
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/galleryEditor.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title"><a href="/galleries/id/$id">Gallery Editor</a></div>
		<div id="images_list_container"></div>
		<div id="editor_navigation">
			<a id="loading_editor_message" class="button" href="javascript:void();" onclick="this.blur();"><span>Loading editor, please wait...</span></a><a id="editor_button" class="button" style="display:none;" href="javascript:showImageEditor();" onclick="this.blur();"><span>Upload Images</span></a>$showBackToDocument
		</div>
		<div id="image_editor_container" style="display:none;">
			<div>
				<div id="image_editor_options">
					<form id="newDocumentForm">
					<table border="0" cellspacing="0" cellpadding="2" width="100%">
						<tr valign="center"><td nowrap>Image:</td><td width="100%"><input style="width:450px;" type="text" id="imageUrl" name="imageUrl"><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'imageUrl');" value="Browse"></td></tr>
						<tr valign="center"><td nowrap>Caption:</td><td width="100%"><input type="text" id="caption" name="caption" style="width:99%"></td></tr>
						<tr valign="center"><td nowrap>Title:</td><td width="100%"><input type="text" id="title" name="title" style="width:99%"></td></tr>
						<input type="hidden" id="id" name="id" value="$id">
						<input type="hidden" id="update_image_id" name="update_image_id" value="">
					</table>
					</form>
				</div>
				<div id="message_box" style="display:none;" onClick="$(this).hide();"></div><div id="editor_container"><div id="documentBody"></div></div>
				$showVersioning
				<div id="editor_options" style="display:none;"></div>
			</div>
		</div>
	</div>
</body>
</html>
EOF;

?>