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

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Document Type Editor</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/documentTypeEditor.js"></script>

<script language="javascript">
editDocumentTypeOptionsViewLock = 0;
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/documentTypeEditor.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title">Document Type Editor</div>
		<div id="document_types_list">
		</div>
		<div id="editor_options">
			<a class="button" href="javascript:showAddDocumentType();" onclick="this.blur();"><span>Add Document Type</span></a>
		</div>
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="add_document_type_container" style="display:none;">
			<div>
				<form id="add_document_type" method="get" action="ajaxAddDocumentType.php">
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr valign="center"><td class="label">Document Type</td><td width="100%"><input style="width:450px;" type="text" id="newDocumentType" name="newDocumentType"></td></tr>
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