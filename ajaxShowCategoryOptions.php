<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);

$result = mysql_query("SELECT * FROM categories WHERE id = '{$category}' LIMIT 1");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	$row = mysql_fetch_object($result);
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup = new CategoryUserGroupValidator();
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	if (!$userGroup->allowEditing()) {exit;}
	
	$category = htmlentities($row->category);
	
	if ($row->hidden == 1) {$hiddenChecked = " checked";}
	if ($row->useAlternateClass == 1) {$useAlternateClassChecked = " checked";}
	if ($row->userSelectable == 1) {$userSelectableChecked = " checked";}
	
	if (trim($row->defaultUrl) != "") {
		
		$showDefaultUrl = "value=\"" . htmlentities($row->defaultUrl) . "\"";
		
	}
	
	print "<div class=\"editor_category_container\">";
	print "<form id=\"edit_category_options_form\" method=\"get\" action=\"ajaxUpdateCategoryOptions.php\">\n";
	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n";
	print "<tr valign=\"center\"><td class=\"options_editor_container\"><input type=\"checkbox\" id=\"userSelectable\" name=\"userSelectable\" value=\"1\"$userSelectableChecked></td><td class=\"options_editor_container\" width=\"100%\">User Selectable</td></tr>";
	print "<tr valign=\"center\"><td class=\"options_editor_container\"><input type=\"checkbox\" id=\"hidden\" name=\"hidden\" value=\"1\"$hiddenChecked></td><td class=\"options_editor_container\" width=\"100%\">Hidden</td></tr>";
	print "<tr valign=\"center\"><td class=\"options_editor_container\"><input type=\"checkbox\" id=\"useAlternateClass\" name=\"useAlternateClass\" value=\"1\"$useAlternateClassChecked></td><td class=\"options_editor_container\" width=\"100%\">Use Alternate Menu Option</td><td class=\"options_editor_container\"></td></tr>";
	print "<tr valign=\"center\"><td class=\"options_editor_container\"></td><td class=\"options_editor_container\" width=\"100%\">Default URL: <input style=\"width:600px;\" type=\"text\" id=\"defaultUrl\" name=\"defaultUrl\" size=\"32\"$showDefaultUrl><input style=\"margin-left:5px;\" type=\"button\" onclick=\"openDocumentManager('selectPath', 'defaultUrl');\" value=\"Browse\"></td></tr>";
	print "<tr valign=\"top\"><td class=\"options_editor_container\"></td><td class=\"options_editor_container\" width=\"100%\">Flyout Menu:<br><div id=\"flyoutContent\"></div></td></tr>";
	print "<tr valign=\"center\"><td></td><td width=\"100%\"><input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"edit_category_options_cancel\" value=\"Cancel\"></td></tr>\n";
	print "</table>\n";
	print "<input type=\"hidden\" id=\"id\" name=\"id\" value=\"$row->id\">\n";
	print "</form>\n";
	print "</div>";
	
}

?>