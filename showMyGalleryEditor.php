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
include("part_update_rootPath_user.php");

//if session is empty, exit
if (trim($_SESSION['username']) == "") {
	
	exit;
}

$username = $_SESSION['username'];

include("part_rich_text_editor_config_gallery.php");

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showMyGalleryEditor.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/showMyGalleryEditor.js"></script>
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

username = '$username';
last_s = 0;

</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div class="subheader_title"><a href="/usergalleries/username/$username">My Gallery</a></div>
			<div id="images_list_container"></div>
			<div id="editor_navigation">
				<a id="loading_editor_message" class="button" href="javascript:void();" onclick="this.blur();"><span>Loading editor, please wait...</span></a><a id="editor_button" class="button" style="display:none;" href="javascript:showImageEditor();" onclick="this.blur();"><span>Upload Images</span></a>
			</div>
			<div id="image_editor_container" style="display:none;">
			<div>
				<div id="image_editor_options">
					<form id="newDocumentForm">
						<table border="0" cellspacing="0" cellpadding="2" width="100%">
							<tr valign="center"><td nowrap>Image:</td><td width="100%"><input style="width:450px;" type="text" id="imageUrl" name="imageUrl"><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'imageUrl');" value="Browse"></td></tr>
							<tr valign="center"><td nowrap>Caption:</td><td width="100%"><input type="text" id="caption" name="caption" style="width:99%"></td></tr>
							<tr valign="center"><td nowrap>Title:</td><td width="100%"><input type="text" id="title" name="title" style="width:99%"></td></tr>
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