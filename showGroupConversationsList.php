<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_group_membership_validator.php");
include("class_config_reader.php");
include("part_update_rootPath_user.php");

$groupId = sanitize_string($_REQUEST['groupId']);

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, jump to login page
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	include("part_session_check.php");
	
}

//load main group information
$result = mysql_query("SELECT name, allowNonMemberPosting FROM groups WHERE id = '{$groupId}' LIMIT 1");
if (mysql_num_rows($result) == 0) {
	
	$_SESSION['status'] = array('not found', $groupId, '');
	header("Location: /status.php?type=group");
	exit;
	
}

$row = mysql_fetch_object($result);
$groupName = htmlentities($row->name);

//non-member posting
if ($row->allowNonMemberPosting == 1) {
	
	$nonMemberPosting = true;
	
} else {
	
	$nonMemberPosting = false;
	
}

//load site group validator
$groupValidator = new GroupValidator();
$groupValidator->isGroupMember($groupId, $_SESSION['username']);
$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);

if (trim($_SESSION['username']) != "" && ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || ($groupValidator->isGroupMember && !$locked) || (!$groupValidator->isGroupMember && $nonMemberPosting && !$locked))) {
	
	$showAddConversationOptions .= "				<a id=\"loading_editor_message\" class=\"button\" href=\"javascript:void();\" onclick=\"this.blur();\"><span>Loading editor, please wait...</span></a><a class=\"button\" id=\"add_conversation_button\" style=\"display:none;\" href=\"javascript:showAddConversation();\" onclick=\"this.blur();\"><span>Add Conversation</span></a>\n";
	
}

if ($groupValidator->isGroupMember) {
	
	$showRestrictOption = "<tr valign=\"top\"><td></td><td class=\"restricted_option\" width=\"100%\"><input type=\"checkbox\" id=\"restricted\" name=\"restricted\" value=\"1\"> Restrict this topic to group members only</td></tr>";
	
}

if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $groupValidator->isGroupAdmin) {
	
	$showLockable = "<tr valign=\"top\"><td></td><td class=\"locked_option\" width=\"100%\"><input type=\"checkbox\" id=\"locked\" name=\"locked\" value=\"1\"> Lock this topic</td></tr>";
	
}

include("part_rich_text_editor_config_new_conversation.php");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showGroupConversationsList.css");
@import url("/assets/core/resources/css/main/groupAdminOptions.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showGroupConversationsList.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/ckeditor/ckeditor.js"></script>
<script language="javascript">
groupId = '$groupId';

function initializeEditor() {
	
	CKEDITOR.replace('documentBody', {
		on : {
			
			instanceReady : function() {
				
				displayEditor();
				
			}
			
		},
		filebrowserBrowseUrl: '/assets/core/resources/filemanager/index.html',
$richTextEditorConfig
	});
	
}

</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("part_group_admin_options.php");

print <<< EOF
			<div class="subheader_title"><a href="/groups/id/$groupId">$groupName</a> &gt; Conversations</div>
			<div id="conversations_list"></div>
			<div id="conversation_options">
				$showAddConversationOptions
				<div id="conversation_search">
					<form action="/groupConversationSearch.php" method="get"><input type="text" id="search" name="search" size="32"> <input type="submit" id="submit" value="Search"><input type="hidden" id="groupId" name="groupId" value="$groupId"></form>
				</div>
				<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
				<div id="add_conversation_container" style="display:none;">
					<div>
						<form id="add_conversation" name="add_conversation" action="ajaxAddGroupConversation.php" method="post" enctype="multipart/form-data">
							<table border="0" cellspacing="0" cellpadding="0" width="100%" class="new_conversation_options">
							<tr valign="center"><td class="label">Title</td><td class="field"><input type="text" style="width:99%;" id="title" name="title"></td></tr>
							$showRestrictOption
							$showLockable
							</table>
							<div id="documentBody"></div>
							<div class="editor_buttons"><input type="submit" id="submit" value="Save"> <input type="button" id="cancel" value="Cancel" onClick="showAddConversation();"></div>
							<input type="hidden" id="groupId" name="groupId" value="$groupId">
						</form>
					</div>
				</div>
			</div>
EOF;

$site_container->showSiteContainerBottom();

?>