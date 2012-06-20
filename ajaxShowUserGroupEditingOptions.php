<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

$result = mysql_query("SELECT * FROM userGroups WHERE id = '{$id}' LIMIT 1");
$count = mysql_num_rows($result);

if ($count > 0) {
	
	$row = mysql_fetch_object($result);
	$name = htmlentities($row->name);
	if ($row->restrictViewing == 1) {$restrictViewingChecked = " checked";}
	if ($row->allowEditing == 1) {$allowEditingChecked = " checked";}
	
	print "<form id=\"edit_user_group\" method=\"get\" action=\"ajaxUpdateUserGroup.php\">\n";
	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">\n";
	print "<tr valign=\"center\"><td nowrap>Name</td><td width=\"100%\"><input type=\"text\" id=\"name\" name=\"name\" style=\"width:99%\" value=\"$name\"></td></tr>";
	print "<tr valign=\"center\"><td></td><td width=\"100%\"><input type=\"checkbox\" id=\"restrictViewing\" name=\"restrictViewing\" value=\"1\"$restrictViewingChecked> Restrict Viewing</td></tr>";
	print "<tr valign=\"center\"><td nowrap></td><td width=\"100%\"><input type=\"checkbox\" id=\"allowEditing\" name=\"allowEditing\" value=\"1\"$allowEditingChecked> Allow Editing</td></tr>";
	print "<tr valign=\"center\"><td colspan=\"2\"><input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"editor_cancel\" value=\"Cancel\"></td></tr>\n";
	print "</table>\n";
	print "<input type=\"hidden\" id=\"id\" name=\"id\" value=\"$id\">";
	print "</form>\n";
	
}

?>