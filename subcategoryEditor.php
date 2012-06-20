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

$category = sanitize_string($_REQUEST['category']);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);
if (!$userGroup->allowEditing()) {exit;}

$unsanitizeCategory = unsanitize_string($category);
$javascript_loader_category = preg_replace('/\'/', '\\\'', $unsanitizeCategory);

$urlCategory = urlencode($unsanitizeCategory);
$htmlCategory = htmlentities($unsanitizeCategory);

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Subcategory Editor</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/subcategoryEditor.js"></script>

<script language="javascript">
category = '$javascript_loader_category';
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/subcategoryEditor.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title"><a href="categoryEditor.php">$htmlCategory</a> &gt; Subcategories</div>
		<div id="subcategories_list">
		</div>
		<div id="editor_options">
			<a class="button" href="javascript:showAddSubcategory();" onclick="this.blur();"><span>Add Subcategory</span></a>
		</div>
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="add_subcategory_container" style="display:none;">
			<div>
				<form id="add_subcategory" method="get" action="ajaxAddSubcategory.php">
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr valign="center"><td class="label">Subcategory</td><td width="100%"><input style="width:450px;" type="text" id="newSubcategory" name="newSubcategory"></td></tr>
					<tr valign="top"><td></td><td class="user_selectable" width="100%"><input type="checkbox" id="userSelectable" name="userSelectable" value="1"> User Selectable</td></tr>
					</table>
					<input type="submit" id="submit" value="Save">
					<input type="hidden" name="category" value="$htmlCategory">
				</form>
			</div>
		</div>
	</div>
</body>
</html>
EOF;

?>