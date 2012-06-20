<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$documentType = sanitize_string($_REQUEST['documentType']);
$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);

if (trim($documentType) != "") {$documentTypeFilter = " AND documentType = '{$documentType}'";}
if (trim($category) != "") {$categoryFilter = " AND category = '{$category}'";}
if (trim($subcategory) != "") {$subcategoryFilter = " AND subcategory = '{$subcategory}'";}
if (trim($subject) != "") {$subjectFilter = " AND subject = '{$subject}'";}

//Output new list via AJAX
$result = mysql_query("SELECT focusedDocuments.id, focusedDocuments.activeState, documents.shortcut, documents.category, documents.subcategory, documents.subject, documents.title, DATE_FORMAT(focusedDocuments.dateStarts, '%m/%d/%Y %h:%i %p') AS newDateStarts, DATE_FORMAT(focusedDocuments.dateExpires, '%m/%d/%Y %h:%i %p') AS newDateExpires FROM focusedDocuments INNER JOIN documents ON documents.id = focusedDocuments.id WHERE 1$documentTypeFilter$categoryFilter$subcategoryFilter$subjectFilter ORDER BY weight");
$count = mysql_num_rows($result);

$time = time();

if ($count == 0) {
	
	print "<div class=\"focused_document_container\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"><tr><td>No focused documents have been assigned.</td></tr></table></div>";
	exit;
	
}

while ($row = mysql_fetch_object($result)) {
	
	//setup the status icon
	if ($time < strtotime($row->newDateStarts)) {

		$showStatus = "icon_pending.gif";

	} elseif ($time > strtotime($row->newDateExpires) && $row->newDateExpires != "00/00/0000 12:00 AM") {

		$showStatus = "icon_expired.gif";

	} else {

		$showStatus = "icon_active.gif";

	}
	
	$category = htmlentities($row->category);
	$subcategory = htmlentities($row->subcategory);
	$subject = htmlentities($row->subject);
	
	//create the title
	$title = htmlentities($row->title);
	
	//setup the start and expiration dates
	if ($row->newDateStarts == "00/00/0000 12:00 AM") {
		
		$showStartDate = "&#8734;";
		
	} else {
		
		$showStartDate = $row->newDateStarts;
		
	}
	
	if ($row->newDateExpires == "00/00/0000 12:00 AM") {
		
		$showExpireDate = "&#8734;";
		
	} else {
		
		$showExpireDate = $row->newDateExpires;
		
	}
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
	
		$showStateOptions = "<a href=\"javascript:toggleActiveState('$row->id');\" onclick=\"return confirm('Are you sure you want to change the status of this focused document?');\"><span id=\"active_state_$row->id\">$row->activeState</span></a>";
		
	} else {
		
		$showStateOptions = "<span id=\"active_state_$row->id\">$row->activeState</span>";
		
	}
	
	print "<div id=\"focused_document_$row->id\" class=\"focused_document_container\">";
	print "<div class=\"handle\">";
	print "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"><tr><td width=\"50%\">";
	print "<div class=\"status\"><img src=\"/assets/core/resources/images/$showStatus\" border=\"0\"></div><a href=\"/documents/open/$row->shortcut\" title=\"$category/$subcategory/$subject\" target=\"_blank\">$title</a>";
	print "<td width=\"35%\">$showStartDate - $showExpireDate</td>";
	
	if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
		
		print "<td nowrap><div class=\"toolbar\"><div class=\"state\">$showStateOptions</div><div class=\"options\"><a href=\"javascript:initEditFocusedDocumentOptions('$row->id');\">Options</a></div><div class=\"remove\"><a href=\"javascript:removeFocusedDocument('$row->id');\" onClick=\"return confirm('Are you sure you want to remove this document from the focused list?');\">Remove</a></div></div></td>";
		
	}
	
	print "</td></tr></table>";
	print "</div>";
	print "</div>\n";
	
}

?>