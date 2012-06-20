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
<title>Announcement Editor</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script><script language="javascript" src="/assets/core/resources/javascript/dateSelectCalendar.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/announcementEditor.js"></script>

<script language="javascript">
userEditorViewLock = 0;
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/announcementEditor.css");
@import url("/assets/core/resources/css/admin/dateSelectCalendar.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title">Announcement Editor</div>
		<div id="announcement_list"></div>
		<div id="editor_options">
			<a class="button" href="javascript:showAddAnnouncement();" onclick="this.blur();"><span>Add Announcement</span></a>
		</div>
		<div id="message_box" class="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="add_announcement_container" style="display:none;">
			<div>
				<form id="add_announcement" method="get" action="ajaxAddAnnouncement.php">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Expires:</td><td width="100%"><input type="text" id="monthAdd" name="monthAdd" size="2"> <input type="text" id="dayAdd" name="dayAdd" size="2"> <input type="text" id="yearAdd" name="yearAdd" size="4"> <span id="date_selector_add" class="date_selector">mm/dd/yyyy</span></td></tr>
					<tr valign="center"><td nowrap>Title:</td><td width="100%"><input type="text" id="title" name="title" style="width:99%"></td></tr>
					<tr valign="top"><td nowrap>Body:</td><td width="100%"><textarea id="body" name="body" rows="16" style="width:99%;"></textarea></td></tr>
					<tr valign="center"><td nowrap>Link:</td><td width="100%"><input type="text" id="linkText" name="linkText" size="32"> URL: <input style="width:450px;" type="text" id="linkUrl" name="linkUrl"><input style="margin-left:5px;" type="button" onclick="openDocumentManager('selectPath', 'linkUrl');" value="Browse"></td></tr>
					<tr valign="center"><td colspan="2"><input type="submit" id="submit" value="Save"></td></tr>
				</table>
				</form>
			</div>	
		</div>
		<div id="calendar_container" style="display:none;"></div>
	</div>
</body>
</html>
EOF;

?>