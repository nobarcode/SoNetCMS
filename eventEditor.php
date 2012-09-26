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

$id = sanitize_string($_REQUEST['id']);
$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);

//create user groups validation object
$userGroup = new CategoryUserGroupValidator();

//if document is being updated load its data
if (trim($id) != "") {
	
	$time = date("Y-m-d H:i:s", time());
	$b = htmlentities(unsanitize_string($b));
	
	//set document editing tracker
	$result = mysql_query("INSERT INTO documentEditTracking (documentType, id, username, date) VALUES ('event', '{$id}', '{$_SESSION['username']}', '{$time}') ON DUPLICATE KEY UPDATE date = '{$time}'");
	
	//get document editing tracker info
	$result = mysql_query("SELECT username, DATE_FORMAT(date, '%M %d, %Y %h:%i %p') AS newDate FROM documentEditTracking WHERE documentType = 'event' AND id = '{$id}' AND username != '{$_SESSION['username']}'");
	
	while ($row = mysql_fetch_object($result)) {
		
		$editTracking .= "			<tr><td>$row->username</td><td>$row->newDate</td></tr>\n";
		
	}
	
	if (trim($editTracking) != "") {
		
		$showEditTracking .= "<div id=\"edit_tracking_container\">\n";
		$showEditTracking .= "	<div class=\"edit_tracking_header\">Also Editing:</div>";
		$showEditTracking .= "	<div class=\"edit_tracking_body\">";
		$showEditTracking .= "		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n";
		$showEditTracking .= "			<tr><td class=\"edit_tracking_list_header\">Username</td><td class=\"edit_tracking_list_header\">Editing Date</td></tr>\n";
		$showEditTracking .= $editTracking;
		$showEditTracking .= "		</table>\n";
		$showEditTracking .= "		<div class=\"edit_tracking_options\">\n";
		$showEditTracking .= "			<span onClick=\"$('#edit_tracking_container').hide();\">Hide</span> | <span onClick=\"clearEditTracking();\">Clear</span>";
		$showEditTracking .= "		</div>\n";
		$showEditTracking .= "	</div>\n";
		$showEditTracking .= "</div>\n";
		
	}
	
	//load document
	$result = mysql_query("SELECT *, EXTRACT(YEAR FROM startDate) AS startYear, EXTRACT(MONTH FROM startDate) AS startMonth, EXTRACT(DAY FROM startDate) AS startDay, EXTRACT(HOUR FROM startDate) AS startHour, EXTRACT(MINUTE FROM startDate) AS startMinute, EXTRACT(YEAR FROM expireDate) AS expireYear, EXTRACT(MONTH FROM expireDate) AS expireMonth, EXTRACT(DAY FROM expireDate) AS expireDay, EXTRACT(HOUR FROM expireDate) AS expireHour, EXTRACT(MINUTE FROM expireDate) AS expireMinute FROM events WHERE id = '{$id}' LIMIT 1");
	
	//catch ivalid ids
	if (mysql_num_rows($result) == 0) {print "invalid id"; exit;}
	
	$row = mysql_fetch_object($result);
	
	//content providers shouldn't be able to edit a published event
	if ($row->publishState == "Published" && $_SESSION['userLevel'] == 4) {
		
		exit;
		
	}
	
	$category = sanitize_string($row->category);
	$subcategory = sanitize_string($row->subcategory);
	$subject = sanitize_string($row->subject);
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup->loadCategoryUserGroups($category);
	if (!$userGroup->allowEditing()) {exit;}
	
	$showAuthor = " value=\"" . htmlentities($row->author) . "\"";
	$showTitle = " value=\"" . htmlentities($row->title) . "\"";
	
	$startMonth = $row->startMonth;
	$startDay = $row->startDay;
	$startYear = $row->startYear;
	$start_hour = date("h", strtotime("$row->startHour:$row->startMinute"));
	$start_minute = date("i", strtotime("$row->startHour:$row->startMinute"));
	$start_AMPM = date("A", strtotime("$row->startHour:$row->startMinute"));
	
	if ($start_AMPM == "AM") {
		
		$startAMSelected = " checked";
		
	} else {
		
		$startPMSelected = " checked";
		
	}
	
	$expireMonth = $row->expireMonth;
	$expireDay = $row->expireDay;
	$expireYear = $row->expireYear;
	$expire_hour = date("h", strtotime("$row->expireHour:$row->expireMinute"));
	$expire_minute = date("i", strtotime("$row->expireHour:$row->expireMinute"));
	$expire_AMPM = date("A", strtotime("$row->expireHour:$row->expireMinute"));
	
	if ($expire_AMPM == "AM") {
		
		$expireAMSelected = " checked";
		
	} else {
		
		$expirePMSelected = " checked";
		
	}
	
	if ($row->showComments == 1) {
		
		$showCommentsChecked = " checked";
		
	}
	
	if (trim($row->summaryImage) != "") {
		
		$showSummaryImage = " value=\"" . htmlentities($row->summaryImage) . "\"";
		
	}
	
	$showDocumentSummary = htmlentities($row->summary);
	$showSummaryLinkText = " value=\"" . htmlentities($row->summaryLinkText) . "\"";
	$showCustomHeader = $row->customHeader;
	
	//load versioning information
	$result = mysql_query("SELECT version, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i:%s %p') AS newDateCreated, usernameCreated FROM documentVersioning WHERE parentId = '{$id}' AND documentType = 'event' ORDER BY version DESC LIMIT 1");
	$row = mysql_fetch_object($result);
	
	$showVersioning .= "<div id=\"versioning_options\">\n";
	$showVersioning .= "	<div id=\"versioning_toolbar\">\n";
	$showVersioning .= "		<div class=\"current_version\"><b>Selected Version:</b> <span id=\"selected_version\">$row->version > $row->newDateCreated > $row->usernameCreated</span></div><div class=\"toggle_version_choices\"><a href=\"javascript:displayVersionOptions();\"><span id=\"versions_navigation\">Show Versions</span></a></div>";
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2) {
		
		$showVersioning .= "<div class=\"delete_versions\"><a href=\"javascript:deleteVersionAll();\" onclick=\"return confirm('Are you sure you want to delete all the versions associated with this document?');\">Delete All</a></div>";
		
	}
	
	$showVersioning .= "	</div>\n";
	$showVersioning .= "	<div id=\"version_choices\" style=\"display:none;\"></div>\n";
	$showVersioning .= "</div>\n";
	
	$setCurrentVersion = "currentVersion = '$row->version';";
	
	if ($row->publishState != 'Published') {
		
		$showPublishState = "Publish";
		
	} else {
		
		$showPublishState = "Unpublish";
		
	}
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
		
		$showEditorOptions .= "<a class=\"button\" href=\"javascript:togglePublishState($id)\" onclick=\"this.blur(); return confirm('Are you sure you want to change the publishing status of this event?');\"><span id=\"publish_state_$id\">$showPublishState</span></a>";
		
	}
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
		
		$showEditorOptions .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"deleteEvent.php?id=$id\" onclick=\"this.blur(); return confirm('Are you sure you want to delete this event?');\"><span>Delete</span></a>";
		
	}
	
	$showBackToDocument = "<div id=\"back_to_document\"><a class=\"button\" href=\"/events/id/$id\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Event</span></a>";
	$showBackToDocument .= "<p class=\"button_spacer\"></p><a class=\"button\" href=\"/eventEditorList.php\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Event List</span></a></div>";
		
	$showDocumentId = "\n									<input type=\"hidden\" name=\"id\" value=\"$id\">";
	
} else {
	
	//exit if the category has user groups assigned to it and the current user is not a member of any of those groups
	$userGroup->loadCategoryUserGroups($category);
	if (!$userGroup->allowEditing()) {exit;}
	
	$showSummaryImage = "no image selected";
	
	include("assets/core/config/part_default_header_event.php");
	
	$showBackToDocument = "<div id=\"back_to_document\"><a class=\"button\" href=\"/eventEditorList.php\" onclick=\"this.blur(); if (CKEDITOR.instances.documentBody.checkDirty()) {return confirm('Are you sure you want to cancel this editing session?\\n\\nThe changes you made will be lost if you continue.\\n\\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.')}\"><span>Event List</span></a></div>";
	
}

//build category list
$result = mysql_query("SELECT * FROM categories WHERE 1 ORDER BY weight");

while ($row = mysql_fetch_object($result)) {
	
	$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
	
	if ($userGroup->allowEditing()) {
		
		$showCategory = htmlentities($row->category);
		
		if ($row->category != unsanitize_string($category)) {

			$categoryList .= "\n									<option value=\"" . htmlentities($row->category) . "\">$showCategory</option>";

		} else {

			$categoryList .= "\n									<option value=\"" . htmlentities($row->category) . "\" selected>$showCategory</option>";

		}
		
	}
	
}

//build subcateogries
$result = mysql_query("SELECT * FROM subcategories WHERE category = '{$category}' ORDER BY weight");

while ($row = mysql_fetch_object($result)) {

	$showSubcategory = htmlentities($row->subcategory);

	if ($row->subcategory != unsanitize_string($subcategory)) {

		$subcategoryList .= "\n									<option value=\"$showSubcategory\">$showSubcategory</option>";

	} else {

		$subcategoryList .= "\n									<option value=\"$showSubcategory\" selected>$showSubcategory</option>";

	}

}

//build subjects
$result = mysql_query("SELECT * FROM subjects WHERE category = '{$category}' AND subcategory = '{$subcategory}' ORDER BY weight");

while ($row = mysql_fetch_object($result)) {

	$showSubject = htmlentities($row->subject);

	if ($row->subject != unsanitize_string($subject)) {

		$subjectList .= "\n									<option value=\"$showSubject\">$showSubject</option>";

	} else {

		$subjectList .= "\n									<option value=\"$showSubject\" selected>$showSubject</option>";

	}

}

include("part_rich_text_editor_config_event.php");

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Event Editor</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/dateSelectCalendar.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/eventEditor.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/ckeditor/ckeditor.js"></script>

<script language="javascript">

id = '$id';
$setCurrentVersion

function initializeEditor() {
	
	CKEDITOR.replace('documentBody', {
		filebrowserBrowseUrl : '/assets/core/resources/filemanager/index.html',
		filebrowserLinkBrowseUrl : '/browserChooserMain.php',
		filebrowserImageBrowseUrl : '/assets/core/resources/filemanager/index.html',
	    filebrowserFlashBrowseUrl : '/assets/core/resources/filemanager/index.html',
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

function initializeCustomHeaderEditor() {
	
	CKEDITOR.replace('customHeader', {
		filebrowserBrowseUrl: '/assets/core/resources/filemanager/index.html',
		customConfig : '/assets/core/resources/javascript/ckeditor/config_custom_header_event.js'
	});
	
	CKEDITOR.instances.customHeader.on('instanceReady', function(){
		
		CKEDITOR.instances.customHeader.dataProcessor.htmlFilter.addRules ({
			
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

<style>
@import url("/assets/core/resources/css/admin/globalControlPanel.css");
@import url("/assets/core/resources/css/admin/eventEditor.css");
@import url("/assets/core/resources/css/admin/dateSelectCalendar.css");
@import url("/assets/core/resources/css/admin/controlPanelMinibar.css");
</style>

</head>
<body>
EOF;

include("part_control_panel_minibar.php");

print <<< EOF
	<div id="body_inner">
		$showEditTracking
		<div class="subheader_title">Event Editor</div>
		<form id="newDocumentForm">
			<div class="editor_box_container">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr valign="center"><td nowrap>Category:</td><td width="100%"><select id="categories" name="category">
				<option value="">Please Select</option>$categoryList
				</select></td></tr>
				<tr valign="center"><td nowrap>Subcategory:</td><td width="100%"><select id="subcategories" name="subcategory">
				<option value="">Select a category above</option>$subcategoryList
				</select></td></tr>
				<tr valign="center"><td nowrap>Subject:</td><td width="100%"><select id="subjects" name="subject">
				<option value="">Select a category above</option>$subjectList
				</select></td></tr>
				<tr valign="center"><td nowrap>Title:</td><td width="100%"><input type="text" id="title" name="title"$showTitle style="width:99%"></td></tr>
				<tr valign="center"><td nowrap>Starts:</td><td width="100%"><input type="text" id="startMonth" name="startMonth" size="2" value="$startMonth"> <input type="text" id="startDay" name="startDay" size="2" value="$startDay"> <input type="text" id="startYear" name="startYear" size="4" value="$startYear"> <span id="start_date_selector" class="date_selector">mm/dd/yyyy</span> <input type="text" id="startHour" name="startHour" size="2" value="$start_hour">:<input type="text" id="startMinute" name="startMinute" size="2" value="$start_minute"> <input type="radio" name="start_AMPM" value="AM"$startAMSelected> AM <input type="radio" name="start_AMPM" value="PM"$startPMSelected> PM</td></tr>
				<tr valign="center"><td nowrap>Expires:</td><td width="100%"><input type="text" id="expireMonth" name="expireMonth" size="2" value="$expireMonth"> <input type="text" id="expireDay" name="expireDay" size="2" value="$expireDay"> <input type="text" id="expireYear" name="expireYear" size="4" value="$expireYear"> <span id="expire_date_selector" class="date_selector">mm/dd/yyyy</span> <input type="text" id="expireHour" name="expireHour" size="2" value="$expire_hour">:<input type="text" id="expireMinute" name="expireMinute" size="2" value="$expire_minute"> <input type="radio" name="expire_AMPM" value="AM"$expireAMSelected> AM <input type="radio" name="expire_AMPM" value="PM"$expirePMSelected> PM</td></tr>
				<tr valign="center"><td nowrap>Summary Image:</td><td width="100%"><input style="width:450px;" type="text" id="summaryImage" name="summaryImage"$showSummaryImage><input style="margin-left:5px;" type="button" onclick="openFileManager('selectPath', 'summaryImage');" value="Browse"></td></tr>
				<tr valign="top"><td nowrap>Summary:</td><td width="100%"><textarea id="summary" name="summary" rows="5" style="width:99%;">$showDocumentSummary</textarea></td></tr>
				<tr valign="center"><td nowrap>Summary Link:</td><td width="100%"><input type="text" id="summaryLinkText" name="summaryLinkText"$showSummaryLinkText style="width:99%"></td></tr>
				<tr valign="top"><td nowrap>Header:</td>
				<td width="100%">
					<div id="customHeader">
					$showCustomHeader
					</div>
					<input type="button" id="activate_custom_header_editor" value="Advanced Header Customization" onClick="toggleCustomHeaderEditor();">
				</td>
				</tr>
				<tr valign="center"><td nowrap>Options:</td><td width="100%"><input type="checkbox" id="showComments" name="showComments" value="1"$showCommentsChecked> Display the comments for this document</td></tr>
				</table>$showDocumentId
			</div>
		</form>
		<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
		<div id="loading_editor_message"><div>Loading editor, please wait...</div></div>
		<div id="editor_container" style="display:none;">
			<div id="documentBody"></div>
		</div>
		$showVersioning
		<div id="editor_options">$showEditorOptions$showBackToDocument</div>
		<div id="calendar_container" style="display:none;"></div>
	</div>
</body>
</html>
EOF;

?>