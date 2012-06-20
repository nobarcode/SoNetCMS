<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

include("part_rich_text_editor_config_categoryEditor.php");

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Category Editor</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/categoryEditor.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/ckeditor/ckeditor.js"></script>

<script language="javascript">
editCategoryOptionsViewLock = 0;

function initializeEditor() {
	
	CKEDITOR.replace('flyoutContent', {
		filebrowserBrowseUrl : '/assets/core/resources/filemanager/index.html',
		filebrowserLinkBrowseUrl : '/browserChooserMain.php',
		filebrowserImageBrowseUrl : '/assets/core/resources/filemanager/index.html',
	    filebrowserFlashBrowseUrl : '/assets/core/resources/filemanager/index.html',
$richTextEditorConfig
	});
	
}

</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/categoryEditor.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title">Category Editor</div>
		<div id="categories_list">
		</div>
		<div id="editor_options">
			<a class="button" href="javascript:showAddCategory();" onclick="this.blur();"><span>Add Category</span></a>
		</div>				
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="add_category_container" style="display:none;">
			<div>
				<form id="add_category" method="get" action="ajaxAddCategory.php">
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr valign="center"><td class="label">Category</td><td width="100%"><input style="width:450px;" type="text" id="newCategory" name="newCategory"></td></tr>
					<tr valign="top"><td></td><td class="user_selectable" width="100%"><input type="checkbox" id="userSelectable" name="userSelectable" value="1"> User Selectable</td></tr>
					</table>
					<input type="submit" id="submit" value="Save">
				</form>
			</div>
		</div>
	</div>
</body>
</html>
EOF;

?>