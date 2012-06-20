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
$subcategory = sanitize_string($_REQUEST['subcategory']);

//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
$userGroup = new CategoryUserGroupValidator();
$userGroup->loadCategoryUserGroups($category);
if (!$userGroup->allowEditing()) {exit;}

$unsanitizeCategory = unsanitize_string($category);
$subcategory = unsanitize_string($subcategory);

$javascript_loader_category = preg_replace('/\'/', '\\\'', $unsanitizeCategory);
$javascript_loader_subcategory = preg_replace('/\'/', '\\\'', $subcategory);

$htmlCategory = htmlentities($unsanitizeCategory);
$urlCategory = urlencode($unsanitizeCategory);

$htmlSubcategory = htmlentities($subcategory);
$urlSubcategory = urlencode($subcategory);

//load the subjects
$result = mysql_query("SELECT * FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}' ORDER BY weight");
$count = mysql_num_rows($result);

if ($count == 0) {$noDataMessage = "<div style=\"width:920px; margin-top:5px; float:left;\">No subjects currently exist.</div>";}

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Subject Editor</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/subjectEditor.js"></script>

<script language="javascript">
category = '$javascript_loader_category';
subcategory = '$javascript_loader_subcategory';
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/subjectEditor.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title"><a href="categoryEditor.php">$htmlCategory</a> &gt; <a href="subcategoryEditor.php?category=$urlCategory&subcategory=$urlSubcategory">$htmlSubcategory</a> &gt; Subjects</div>
		<div id="subjects_list">
		</div>
		<div id="editor_options">
			<a class="button" href="javascript:showAddSubject();" onclick="this.blur();"><span>Add Subject</span></a>
		</div>	
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="add_subject_container" style="display:none;">
			<div>
				<form id="add_subject" method="get" action="ajaxAddSubject.php">
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr valign="center"><td class="label">Subject</td><td width="100%"><input style="width:450px;" type="text" id="newSubject" name="newSubject"></td></tr>
					<tr valign="top"><td></td><td class="user_selectable" width="100%"><input type="checkbox" id="userSelectable" name="userSelectable" value="1"> User Selectable</td></tr>
					</table>
					<input type="submit" id="submit" value="Save">
					<input type="hidden" name="category" value="$htmlCategory">
					<input type="hidden" name="subcategory" value="$htmlSubcategory">
				</form>
			</div>
		</div>
	</div>
</body>
</html>
EOF;

?>