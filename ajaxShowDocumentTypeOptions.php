<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$documentType = sanitize_string($_REQUEST['documentType']);

$result = mysql_query("SELECT * FROM documentTypes WHERE id = '{$documentType}' LIMIT 1");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	$row = mysql_fetch_object($result);
	$documentType = htmlentities($row->documentType);
	
	if ($row->userSelectable == 1) {$userSelectableChecked = " checked";}
	
	if ($row->focus == 1) {$focusChecked = " checked";}
	
	if ($row->type == "Popular") {
		
		$focusTypePopularSelected = " checked";
		
	} else {
		
		$focusTypeNewestSelected = " checked";
		
	}
	
	print "<div class=\"editor_document_type_container\">";
	print "<form id=\"edit_document_type_options_form\" method=\"get\" action=\"ajaxUpdateDocumentTypeOptions.php\">\n";
	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	print "<tr valign=\"center\"><td class=\"options_editor_container\"><input type=\"checkbox\" id=\"userSelectable\" name=\"userSelectable\" value=\"1\"$userSelectableChecked></td><td class=\"options_editor_container\">User Selectable</td></tr>";
	print "<tr valign=\"center\"><td></td><td width=\"100%\"><input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"edit_document_type_options_cancel\" value=\"Cancel\"></td></tr>\n";
	print "</table>\n";
	print "<input type=\"hidden\" id=\"documentType\" name=\"documentType\" value=\"$documentType\">\n";
	print "</form>\n";
	print "</div>";
	
}

?>