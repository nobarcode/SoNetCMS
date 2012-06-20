<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("part_admin_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>User Group Manager</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/userGroupsEditor.js"></script>

<script language="javascript">
userEditorViewLock = 0;
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/userGroupsEditor.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		<div class="subheader_title">User Group Manager</div>
		<div id="user_groups_list">
		</div>
		<div id="editor_options">
			<a class="button" href="javascript:showAddUserGroup();" onclick="this.blur();"><span>Add User Group</span></a>
		</div>				
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="add_user_group_container" style="display:none;">
			<div>
				<form id="add_user_group" method="get" action="ajaxAddUserGroup.php">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Name</td><td width="100%"><input type="text" id="name" name="name" style="width:99%"></td></tr>
					<tr valign="center"><td></td><td width="100%"><input type="checkbox" id="restrictViewing" name="restrictViewing" value="1"> Restrict Viewing</td></tr>
					<tr valign="center"><td nowrap></td><td width="100%"><input type="checkbox" id="allowEditing" name="allowEditing" value="1"> Allow Editing</td></tr>
					<tr valign="center"><td colspan="2"><input type="submit" id="submit" value="Save"></td></tr>
				</table>
				</form>
			</div>	
		</div>
	</div>
</body>
</html>
EOF;

?>