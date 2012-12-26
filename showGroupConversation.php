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

$parentId = sanitize_string($_REQUEST['parentId']);
$findId = sanitize_string($_REQUEST['findId']);

//read config file and determine if viewing groups requires authentication, if it does and the user is not logged in, jump to login page
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('viewGroupsAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	include("part_session_check.php");
	
}

//load main group information
$result = mysql_query("SELECT conversations.groupId, conversations.title, conversations.restricted, conversations.locked, groups.name, groups.allowNonMemberPosting FROM conversations INNER JOIN groups ON groups.id = conversations.groupId WHERE conversations.id = '{$parentId}' LIMIT 1");
if (mysql_num_rows($result) == 0) {
	
	$_SESSION['status'] = array('not found', $groupId, '');
	header("Location: /status.php?type=group");
	exit;
	
}

$row = mysql_fetch_object($result);
$groupId = $row->groupId;

//load site group validator
$groupValidator = new GroupValidator();
$groupValidator->isGroupMember($groupId, $_SESSION['username']);
$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);

//check if this conversation has been restricted, if so, make sure the user is a member of this conversation's group
if ($row->restricted == 1 && (!$groupValidator->isGroupMember && $_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3)) {
	
	exit;
	
}

//non-member posting
if ($row->allowNonMemberPosting == 1) {
	
	$nonMemberPosting = true;
	
} else {
	
	$nonMemberPosting = false;
	
}

if ($row->locked == 1) {
	
	$locked = true;
	$showLock = " class=\"locked\" title=\"Topic is Locked\"";
	
} else {
	
	$locked = false;
	
}

$conversationTitle = htmlentities($row->title);
$groupName = htmlentities($row->name);

//setup group connversation title options link
if ($groupValidator->isGroupAdmin || ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3)) {
	
	$showGroupComversationTitle = "<a id=\"conversation_title\"$showLock href=\"javascript:showConversationEditor();\">$conversationTitle</a>";
	
	$showGroupConversationOptions .= "					<div id=\"conversation_editor_container\" style=\"display:none;\">\n";
	$showGroupConversationOptions .= "						<div>\n";
	$showGroupConversationOptions .= "							<form id=\"edit_conversation_settings\" name=\"edit_conversation_settings\" action=\"ajaxUpdateGroupConversation.php\" method=\"post\" enctype=\"multipart/form-data\">\n";
	$showGroupConversationOptions .= "							<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n";
	$showGroupConversationOptions .= "							<tr valign=\"center\"><td class=\"label\">Title</td><td width=\"100%\" align=\"left\"><input type=\"text\" style=\"width:99%;\" id=\"title\" name=\"title\"></td></tr>\n";
	$showGroupConversationOptions .= "							<tr valign=\"top\"><td></td><td class=\"restricted_option\" width=\"100%\"><input type=\"checkbox\" id=\"restricted\" name=\"restricted\" value=\"1\"> Restrict this topic to group members only</td></tr>\n";
	$showGroupConversationOptions .= "							<tr valign=\"top\"><td></td><td class=\"locked_option\" width=\"100%\"><input type=\"checkbox\" id=\"locked\" name=\"locked\" value=\"1\"> Lock this topic</td></tr>\n";
	$showGroupConversationOptions .= "							<tr valign=\"center\"><td></td><td><input type=\"submit\" id=\"submit\" value=\"Save\"> <input type=\"button\" id=\"cancel\" value=\"Cancel\" onClick=\"showConversationEditor();\"></td></tr>\n";
	$showGroupConversationOptions .= "							</table>\n";
	$showGroupConversationOptions .= "							<input type=\"hidden\" id=\"parentId\" name=\"parentId\" value=\"$parentId\">\n";
	$showGroupConversationOptions .= "							</form>\n";
	$showGroupConversationOptions .= "						</div>\n";
	$showGroupConversationOptions .= "					</div>\n";
	
} else {
	
	$showGroupComversationTitle = "<span id=\"conversation_title\"$showLock>$conversationTitle</span>";
	
}

if (trim($_SESSION['username']) != "" && ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $groupValidator->isGroupAdmin || ($groupValidator->isGroupMember && !$locked) || (!$groupValidator->isGroupMember && $nonMemberPosting && !$locked))) {
	
	$showReplyOptions .= "				<a id=\"loading_editor_message\" class=\"button\" href=\"javascript:void();\" onclick=\"this.blur();\"><span>Loading editor, please wait...</span></a><a class=\"button\" id=\"reply_button\" style=\"display:none;\" href=\"javascript:showReplyToConversation();\" onclick=\"this.blur();\"><span>Reply to Conversation</span></a>\n";
	
}

include("part_rich_text_editor_config_conversation_reply.php");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showGroupConversation.css");
@import url("/assets/core/resources/css/main/groupAdminOptions.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/showGroupConversation.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/ckeditor/ckeditor.js"></script>
<script language="javascript">
parentId = '$parentId';
findId = '$findId';
groupId = '$groupId';
last_s = 0;

function initializeEditor() {
	
	CKEDITOR.replace('post_edit', {
		on : {
			
			instanceReady : function() {
				
				CKEDITOR.instances.post_edit.dataProcessor.htmlFilter.addRules ({
					
					text : function(data) {
						//find all bits in double brackets                       
						var matches = data.match(/\[\[(.*?)\]\]/g);
						
						//go through each match and replace the encoded characters
						if (matches != null) {
							
							for (match in matches) {
								
								var replacedString=matches[match];
								replacedString = matches[match].replace(/&quot;/g,'"');
								data = data.replace(matches[match],replacedString);
								
							}
							
						}
						
						return data;
						
					}
						
				});
				
				scrollTo($('#edit_in_place'));
				
			}
			
		},
		filebrowserBrowseUrl: '/assets/core/resources/filemanager/index.html',
$richTextEditorConfig
	});
	
}

function initializeReply() {
	
	CKEDITOR.replace('documentBody', {
		on : {
			
			instanceReady : function() {
				
				displayEditor();
				
			}
			
		},
		filebrowserBrowseUrl: '/assets/core/resources/filemanager/index.html',
$richTextEditorConfig
	});
	
	CKEDITOR.instances.documentBody.on('instanceReady', function(){
		
		CKEDITOR.instances.documentBody.dataProcessor.htmlFilter.addRules ({
			
			text : function(data) {
				//find all bits in double brackets                       
				var matches = data.match(/\[\[(.*?)\]\]/g);
				
				//go through each match and replace the encoded characters
				if (matches != null) {
					
					for (i = 0; i < matches.length; i++) {
						
						var replacedString = matches[i];
						replacedString = matches[i].replace(/&quot;/g,'"');
						data = data.replace(matches[i],replacedString);
						
					}
					
				}
				
				return data;
				
			}
				
		});
		
	});
	
}

</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("part_group_admin_options.php");

print <<< EOF
			<div class="subheader_title"><a href="/groups/id/$groupId">$groupName</a> &gt; <a href="showGroupConversationsList.php?groupId=$groupId">Conversations</a> &gt; $showGroupComversationTitle</div>
			$showGroupConversationOptions
			<div id="conversations_list"></div>
			<div id="conversation_options">
			$showReplyOptions
				<div id="conversation_search">
					<form action="/groupConversationSearch.php" method="get"><input type="text" id="search" name="search" size="32"> <input type="submit" id="submit" value="Search"><input type="hidden" id="groupId" name="groupId" value="$groupId"></form>
				</div>
			</div>
			<div id="reply_container" style="display:none;">
				<div>
					<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
					<form id="reply_to_conversation" name="reply_to_conversation">
					<div id="documentBody"></div>
					<div class="editor_buttons"><input type="submit" id="submit" value="Save"> <input type="button" id="cancel" value="Cancel" onClick="showReplyToConversation();"></div>
					<input type="hidden" id="parentId" name="parentId" value="$parentId">
					<input type="hidden" id="groupId" name="groupId" value="$groupId">
					</form>
				</div>
			</div>
EOF;

$site_container->showSiteContainerBottom();

?>