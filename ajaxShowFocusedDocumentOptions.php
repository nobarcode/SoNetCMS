<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_editor_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

$result = mysql_query("SELECT *, EXTRACT(YEAR FROM dateStarts) AS startYear, EXTRACT(MONTH FROM dateStarts) AS startMonth, EXTRACT(DAY FROM dateStarts) AS startDay, EXTRACT(HOUR FROM dateStarts) AS startHour, EXTRACT(MINUTE FROM dateStarts) AS startMinute, EXTRACT(YEAR FROM dateExpires) AS expireYear, EXTRACT(MONTH FROM dateExpires) AS expireMonth, EXTRACT(DAY FROM dateExpires) AS expireDay, EXTRACT(HOUR FROM dateExpires) AS expireHour, EXTRACT(MINUTE FROM dateExpires) AS expireMinute FROM focusedDocuments WHERE id = '{$id}' LIMIT 1");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	$row = mysql_fetch_object($result);
	
	if ($row->startMonth > 0) {$startMonth = $row->startMonth;}
	if ($row->startDay > 0) {$startDay = $row->startDay;}
	if ($row->startYear > 0) {$startYear = $row->startYear;}
	
	if ($row->startMonth > 0 && $row->startDay > 0 && $row->startYear > 0) {
		
		$start_hour = date("h", strtotime("$row->startHour:$row->startMinute"));
		$start_minute = date("i", strtotime("$row->startHour:$row->startMinute"));
		$start_AMPM = date("A", strtotime("$row->startHour:$row->startMinute"));

		if ($start_AMPM == "AM") {

			$startAMSelected = " checked";

		} else {

			$startPMSelected = " checked";

		}
		
	}
	
	if ($row->expireMonth > 0) {$expireMonth = $row->expireMonth;}
	if ($row->expireDay > 0) {$expireDay = $row->expireDay;}
	if ($row->expireYear > 0) {$expireYear = $row->expireYear;}
	
	if ($row->expireMonth > 0 && $row->expireDay > 0 && $row->expireYear > 0) {
		
		$expire_hour = date("h", strtotime("$row->expireHour:$row->expireMinute"));
		$expire_minute = date("i", strtotime("$row->expireHour:$row->expireMinute"));
		$expire_AMPM = date("A", strtotime("$row->expireHour:$row->expireMinute"));

		if ($expire_AMPM == "AM") {

			$expireAMSelected = " checked";

		} else {

			$expirePMSelected = " checked";

		}
		
	}
	
	print "<div class=\"editor_focused_document_container\">";
	print "<form id=\"edit_focused_document_options_form\" method=\"get\" action=\"ajaxUpdateFocusedDocumentOptions.php\">\n";
	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	print "<tr valign=\"center\"><td class=\"options_editor_container\">Starts:</td><td class=\"options_editor_container\"><input type=\"text\" id=\"startMonth\" name=\"startMonth\" size=\"2\" value=\"$startMonth\"> <input type=\"text\" id=\"startDay\" name=\"startDay\" size=\"2\" value=\"$startDay\"> <input type=\"text\" id=\"startYear\" name=\"startYear\" size=\"4\" value=\"$startYear\"> <span id=\"start_date_selector\" class=\"date_selector\">mm/dd/yyyy</span> <input type=\"text\" id=\"startHour\" name=\"startHour\" size=\"2\" value=\"$start_hour\">:<input type=\"text\" id=\"startMinute\" name=\"startMinute\" size=\"2\" value=\"$start_minute\"> <input type=\"radio\" name=\"start_AMPM\" value=\"AM\"$startAMSelected> AM <input type=\"radio\" name=\"start_AMPM\" value=\"PM\"$startPMSelected> PM</td></tr>\n";
	print "<tr valign=\"center\"><td class=\"options_editor_container\">Expires:</td><td class=\"options_editor_container\"><input type=\"text\" id=\"expireMonth\" name=\"expireMonth\" size=\"2\" value=\"$expireMonth\"> <input type=\"text\" id=\"expireDay\" name=\"expireDay\" size=\"2\" value=\"$expireDay\"> <input type=\"text\" id=\"expireYear\" name=\"expireYear\" size=\"4\" value=\"$expireYear\"> <span id=\"expire_date_selector\" class=\"date_selector\">mm/dd/yyyy</span> <input type=\"text\" id=\"expireHour\" name=\"expireHour\" size=\"2\" value=\"$expire_hour\">:<input type=\"text\" id=\"expireMinute\" name=\"expireMinute\" size=\"2\" value=\"$expire_minute\"> <input type=\"radio\" name=\"expire_AMPM\" value=\"AM\"$expireAMSelected> AM <input type=\"radio\" name=\"expire_AMPM\" value=\"PM\"$expirePMSelected> PM</td></tr>\n";
	print "<tr valign=\"center\"><td class=\"options_editor_container\" colspan=\"2\"><input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"edit_focused_document_options_cancel\" value=\"Cancel\"></td></tr>\n";
	print "</table>\n";
	print "<input type=\"hidden\" id=\"id\" name=\"id\" value=\"$id\">\n";
	print "</form>\n";
	print "</div>";
	
}

?>