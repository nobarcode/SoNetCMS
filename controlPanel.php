<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");
include("part_update_rootPath_user.php");

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Control Panel</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>

<script language="javascript">
userEditorViewLock = 0;
</script>

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/controlPanel.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

if (file_exists("assets/_delete_me.php")) {
	
	print "<div id=\"warning_message\"><div><b>WARNING:</b> You have not deleted the _delete_me.php file. Please use the <a style=\"color:#ffffff;\" href=\"/assets/core/resources/filemanager/index.html\" target=\"_blank\">File Manager</a> to delete the _delete_me.php file as soon as possible.</div></div>";
	
}

print <<< EOF
	<div id="body_inner">
		<div class="icons">
			<div class="icon"><div class="icon_image"><a href="/categoryEditor.php"><img src="/assets/core/resources/images/icon_category_editor.jpg" border="0"></a></div><div class="icon_description">Website Structure</div></div>
			<div class="icon"><div class="icon_image"><a href="/documentTypeEditor.php"><img src="/assets/core/resources/images/icon_document_types.jpg" border="0"></a></div><div class="icon_description">Document Types</div></div>
			<div class="icon"><div class="icon_image"><a href="/documentManager.php"><img src="/assets/core/resources/images/icon_document_manager.jpg" border="0"></a></div><div class="icon_description">Document Manager</div></div>
			<div class="icon"><div class="icon_image"><a href="/featuredDocumentEditor.php"><img src="/assets/core/resources/images/icon_focused_document_editor.jpg" border="0"></a></div><div class="icon_description">Featured & Focused</div></div>
			<div class="icon"><div class="icon_image"><a href="/announcementEditor.php"><img src="/assets/core/resources/images/icon_announcement_editor.jpg" border="0"></a></div><div class="icon_description">Announcements</div></div>
			<div class="icon"><div class="icon_image"><a href="/eventEditorList.php"><img src="/assets/core/resources/images/icon_event_editor.jpg" border="0"></a></div><div class="icon_description">Events</div></div>
			<div class="icon"><div class="icon_image"><a href="/assets/core/resources/filemanager/index.html"><img src="/assets/core/resources/images/icon_file_manager.jpg" border="0"></a></div><div class="icon_description">File Manager</div></div>
EOF;

if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {
	
	print "			<div class=\"icon\"><div class=\"icon_image\"><a href=\"/userEditor.php\"><img src=\"/assets/core/resources/images/icon_user_editor.jpg\" border=\"0\"></a></div><div class=\"icon_description\">User Manager</div></div>\n";
	print "			<div class=\"icon\"><div class=\"icon_image\"><a href=\"/userGroupsEditor.php\"><img src=\"/assets/core/resources/images/icon_user_groups_editor.jpg\" border=\"0\"></a></div><div class=\"icon_description\">User Group Manager</div></div>\n";
	
}

print <<< EOF
		</div>
	</div>
</body>
</html>
EOF;

?>