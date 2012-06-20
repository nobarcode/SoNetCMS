<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

$result = mysql_query("SELECT *, EXTRACT(YEAR FROM dateExpires) AS expireYear, EXTRACT(MONTH FROM dateExpires) AS expireMonth, EXTRACT(DAY FROM dateExpires) AS expireDay FROM announcements WHERE id = '{$id}' LIMIT 1");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	$row = mysql_fetch_object($result);
	if ($row->expireMonth > 0) {$expireMonth = $row->expireMonth;}
	if ($row->expireDay > 0) {$expireDay = $row->expireDay;}
	if ($row->expireYear > 0) {$expireYear = $row->expireYear;}	
	$title = htmlentities($row->title);
	$body = htmlentities($row->body);
	$linkUrl = htmlentities($row->linkUrl);
	$linkText = htmlentities($row->linkText);
	
	print "<form id=\"edit_announcement\" method=\"get\" action=\"ajaxUpdateAnnouncement.php\">\n";
	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">\n";
	print "<tr valign=\"center\"><td nowrap>Expires:</td><td width=\"100%\"><input type=\"text\" id=\"month\" name=\"month\" size=\"2\" value=\"$expireMonth\"> <input type=\"text\" id=\"day\" name=\"day\" size=\"2\" value=\"$expireDay\"> <input type=\"text\" id=\"year\" name=\"year\" size=\"4\" value=\"$expireYear\"> <span id=\"date_selector_edit\" class=\"date_selector\">mm/dd/yyyy</span></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Title:</td><td width=\"100%\"><input type=\"text\" id=\"title\" name=\"title\" style=\"width:99%\" value=\"$title\"></td></tr>\n";
	print "<tr valign=\"top\"><td nowrap>Body:</td><td width=\"100%\"><textarea id=\"body\" name=\"body\" rows=\"16\" style=\"width:99%;\">$body</textarea></td></tr>\n";
	print "<tr valign=\"center\"><td nowrap>Link:</td><td width=\"100%\"><input type=\"text\" id=\"linkText\" name=\"linkText\" size=\"32\" value=\"$linkText\"> URL: <input style=\"width:600px;\" type=\"text\" id=\"editLinkUrl\" name=\"editLinkUrl\" value=\"$linkUrl\"><input style=\"margin-left:5px;\" type=\"button\" onclick=\"openDocumentManager('selectPath', 'editLinkUrl');\" value=\"Browse\"></td></tr>";
	print "<tr valign=\"center\"><td colspan=\"2\"><input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"editor_cancel\" value=\"Cancel\"></td></tr>\n";
	print "</table>\n";
	print "<input type=\"hidden\" id=\"id\" name=\"id\" value=\"$id\">";
	print "</form>\n";
	
}

?>