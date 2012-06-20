<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");
include("part_update_rootPath_group.php");

$groupId = sanitize_string($_REQUEST['groupId']);

//check if a group is being edited
if (trim($groupId) != "") {
	
	//if the user is not an admin require that they be an owner
	if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3) {
		
		$requireOwner = " AND groupsMembers.username = '". $_SESSION['username'] . "' AND (groupsMembers.memberLevel = '1' OR groupsMembers.memberLevel = '2')";
		
	}
	
	$result = mysql_query("SELECT * FROM groups LEFT JOIN groupsMembers ON groups.id = groupsMembers.parentId WHERE groups.id = '{$groupId}'$requireOwner LIMIT 1");
	
	//catch ivalid ids
	if (mysql_num_rows($result) == 0) {

		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {

			print "<b>Resource ID #$groupId Not Found!</b><br><br>The requested resource is not available. There are two possible reasons for this error:<ol><li>The requested resource ID has not yet been assigned to anything.<li>The resource associated to the requested ID has been deleted.</ol>";

		}

		exit;

	}
	
	$row = mysql_fetch_object($result);
	
	$showName = " value=\"" . htmlentities($row->name) . "\"";
	
	if ($row->approvalRequired == 1) {
		
		$showApprovalRequired = " checked";
		
	}
	
	if ($row->exclusiveRequired == 1) {
		
		$showExclusiveRequired = " checked";
		
	}
	
	if ($row->allowNonMemberPosting == 1) {
		
		$showAllowNonMemberPosting = " checked";
		
	}
	
	if (trim($row->summaryImage) != "") {
		
		$showSummaryImage = " value=\"" . htmlentities($row->summaryImage) . "\"";
		
	}
	
	$showGroupSummary = htmlentities($row->summary);
	
	$showEditorOptions = "<div id=\"editor_options\"><a class=\"button\" href=\"deleteGroup.php?groupId=$groupId\" onclick=\"this.blur(); return confirm('Are you sure you want to delete this group?');\"><span>Delete</span></a></div>";
	
	$showGroupId = "\n									<input type=\"hidden\" name=\"groupId\" value=\"$groupId\">";
	
} else {
	
	$showSummaryImage = "no image selected";
	
}

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showMyGroupEditor.css");
@import url("/assets/core/resources/css/main/groupAdminOptions.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showMyGroupEditor.js"></script>
<script language="javascript">
groupId = '$groupId';
</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("part_group_admin_options.php");

print <<< EOF
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div class="subheader_title">Group Editor</div>
			<div class="editor_box_container">
				<form id="newDocumentForm" action="showMyBlogEditor.php" method="post" enctype="multipart/form-data">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Group Name:</td><td width="100%"><input type="text" id="name" name="name"$showName style="width:99%"></td></tr>
					<tr valign="center"><td nowrap>Image or Logo:</td><td width="100%"><input style="width:450px;" type="text" id="summaryImage" name="summaryImage"$showSummaryImage><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'summaryImage');" value="Browse"></td></tr>
					<tr valign="top"><td nowrap>About:</td><td width="100%"><textarea id="summary" name="summary" rows="8" style="width:99%;">$showGroupSummary</textarea></td></tr>
					<tr valign="top"><td nowrap>Options:</td><td width="100%">
					<input type="checkbox" id="approvalRequired" name="approvalRequired" value="1"$showApprovalRequired> Require approval to join this group<br>
					<input type="checkbox" id="exclusiveRequired" name="exclusiveRequired" value="1"$showExclusiveRequired> Members of this group cannot be part of another group<br>
					<input type="checkbox" id="allowNonMemberPosting" name="allowNonMemberPosting" value="1"$showAllowNonMemberPosting> Allow non-members to create and reply to conversations
					</td></tr>
					<tr valign="top"><td nowrap></td><td width="100%"><input type="button" value="Save Group" onClick="ajaxSave();"></td></tr>
					$showGroupId
				</table>
				</form>
			</div>
			$showEditorOptions
EOF;

$site_container->showSiteContainerBottom();

?>