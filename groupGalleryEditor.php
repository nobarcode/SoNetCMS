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

$groupId = sanitize_string($_REQUEST['groupId']);
include("part_update_rootPath_group.php");

if (trim($groupId) == "") {exit;}

//validate group
$result = mysql_query("SELECT id FROM groups WHERE id = '{$groupId}'");

if (mysql_num_rows($result) == 0) {

	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
		
		print "<b>Resource ID #$groupId Not Found!</b><br><br>The requested resource is not available. There are two possible reasons for this error:<ol><li>The requested resource ID has not yet been assigned to anything.<li>The resource associated to the requested ID has been deleted.</ol>";
		
	}
	
	exit;

}

if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2) {
	
	//if the user is not an admin, validate that the user is allowed to edit the requested group
	$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");

	if (mysql_num_rows($result) == 0) {

		exit;

	}
	
}

//load category and subcategory
$result = mysql_query("SELECT name FROM groups WHERE id = '{$groupId}' LIMIT 1");

//catch ivalid ids
if (mysql_num_rows($result) == 0) {print "invalid id"; exit;}

$row = mysql_fetch_object($result);

$groupName = htmlentities($row->name);

$showBackToDocument = "<div id=\"back_to_document\"><a class=\"button\" href=\"/showGroupGallery.php?groupId=$groupId\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Gallery</span></a></div>";

include("part_rich_text_editor_config_gallery.php");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/groupGalleryEditor.css");
@import url("/assets/core/resources/css/main/groupAdminOptions.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/groupGalleryEditor.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/ckeditor/ckeditor.js"></script>
<script language="javascript">

function initializeEditor() {
	
	CKEDITOR.replace('documentBody', {
		on : {
			
			instanceReady : function() {
				
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
				
				displayEditor();
				
			}
			
		},
		filebrowserBrowseUrl: '/assets/core/resources/filemanager/index.html',
$richTextEditorConfig
	});
	
}

groupId = '$groupId';
last_s = 0;
</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("part_group_admin_options.php");

print <<< EOF
			<div class="subheader_title"><a href="/groupgalleries/id/$groupId">Gallery Editor</a></div>
			<div id="images_list_container"></div>
			<div id="editor_navigation">
				<a id="loading_editor_message" class="button" href="javascript:void();" onclick="this.blur();"><span>Loading editor, please wait...</span></a><a id="editor_button" class="button" style="display:none;" href="javascript:showImageEditor();" onclick="this.blur();"><span>Upload Images</span></a>$showBackToDocument
			</div>
			<div id="image_editor_container" style="display:none;">
				<div>
					<div id="image_editor_options">
						<form id="newDocumentForm">
						<table border="0" cellspacing="0" cellpadding="2" width="100%">
							<tr valign="center"><td nowrap>Image:</td><td width="100%"><input style="width:450px;" type="text" id="imageUrl" name="imageUrl"><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'imageUrl');" value="Browse"></td></tr>
							<tr valign="center"><td nowrap>Caption:</td><td width="100%"><input type="text" id="caption" name="caption" style="width:99%"></td></tr>
							<tr valign="center"><td nowrap>Title:</td><td width="100%"><input type="text" id="title" name="title" style="width:99%"></td></tr>
							<tr valign="center"><td nowrap></td><td width="100%"><input type="checkbox" id="showComments" name="showComments" value="1"> Display the comments for this document</td></tr>
							<input type="hidden" id="groupId" name="groupId" value="$groupId">
							<input type="hidden" id="update_image_id" name="update_image_id" value="">
						</table>
						</form>
					</div>
					<div id="message_box" style="display:none;" onClick="$(this).hide();"></div><div id="editor_container"><div id="documentBody"></div></div>
					<div id="editor_options" style="display:none;"></div>
				</div>
			</div>
EOF;

$site_container->showSiteContainerBottom();

?>