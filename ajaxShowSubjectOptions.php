<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$subject = sanitize_string($_REQUEST['subject']);

$result = mysql_query("SELECT * FROM subjects WHERE id = '{$subject}' LIMIT 1");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	$row = mysql_fetch_object($result);
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup = new CategoryUserGroupValidator();
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	if (!$userGroup->allowEditing()) {exit;}
	
	if ($row->userSelectable == 1) {$userSelectableChecked = " checked";}
	
	print "<div class=\"editor_subject_container\">";
	print "<form id=\"edit_subject_options_form\" method=\"get\" action=\"ajaxUpdateSubjectOptions.php\">\n";
	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	print "<tr valign=\"center\"><td class=\"options_editor_container\"><input type=\"checkbox\" id=\"userSelectable\" name=\"userSelectable\" value=\"1\"$userSelectableChecked></td><td class=\"options_editor_container\">User Selectable</td></tr>";
	print "<tr valign=\"center\"><td></td><td width=\"100%\"><input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"edit_subject_options_cancel\" value=\"Cancel\"></td></tr>\n";
	print "</table>\n";
	print "<input type=\"hidden\" id=\"subject\" name=\"subject\" value=\"$subject\">\n";
	print "</form>\n";
	print "</div>";
	
}

?>