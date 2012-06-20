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
$filterType = sanitize_string($_REQUEST['filterType']);
$filterValue = sanitize_string($_REQUEST['filterValue']);
$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$shortcut = sanitize_string($_REQUEST['shortcut']);
$status = sanitize_string($_REQUEST['status']);
$title = sanitize_string($_REQUEST['title']);
$dateCreated = sanitize_string($_REQUEST['dateCreated']);
$author = sanitize_string($_REQUEST['author']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$change = sanitize_string($_REQUEST['change']);

changeDirection($documentType, $category, $subcategory, $subject, $filterType, $filterValue, $s, $d, $shortcut, $status, $title, $dateCreated, $author, $orderBy, $change);

function changeDirection($documentType, $category, $subcategory, $subject, $filterType, $filterValue, $s, $d, $shortcut, $status, $title, $dateCreated, $author, $orderBy, $change) {
	
	//create user groups validation object
	$userGroup = new CategoryUserGroupValidator();
	$excludeCategories = $userGroup->viewCategoryExclusionList('documents');
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	//setup filtering
	switch ($filterType) {
		
		case "status":
			
			$queryFilter = " AND publishState LIKE '$filterValue%'";
			
		break;
		
		case "title":
			
			$queryFilter = " AND title LIKE '%$filterValue%'";
			
		break;
		
		case "date":
			
			$queryFilter = " AND DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i %p') LIKE '%$filterValue%'";
			
		break;
		
		case "author":
			
			$queryFilter = " AND usernameCreated LIKE '%$filterValue%'";
			
		break;
		
	}
	
	if ($orderBy == $change) {
		
		if ($change == "shortcut") {if ($shortcut == "desc") {$shortcut = "asc";} else {$shortcut = "desc";} $changeShortcut = "shortcut";}
		if ($change == "status") {if ($status == "desc") {$status = "asc";} else {$status = "desc";} $changeStatus = "status";}
		if ($change == "title") {if ($title == "desc") {$title = "asc";} else {$title = "desc";} $changeTitle = "title";}
		if ($change == "dateCreated") {if ($dateCreated == "desc") {$dateCreated = "asc";} else {$dateCreated = "desc";} $changeDateCreated = "dateCreated";}
		if ($change == "author") {if ($author == "desc") {$author = "asc";} else {$author = "desc";} $changeAuthor = "author";}
		
	}
	
	//create direction SQL based on posted variables (do not use posted vatiables directly)
	if ($shortcut == "desc") {$shortcutDirection = "DESC";} else {$shortcutDirection = "ASC";}
	if ($status == "desc") {$statusDirection = "DESC";} else {$statusDirection = "ASC";}
	if ($title == "desc") {$titleDirection = "DESC";} else {$titleDirection = "ASC";}
	if ($dateCreated == "desc") {$dateCreatedDirection = "DESC";} else {$dateCreatedDirection = "ASC";}
	if ($author == "desc") {$authorDirection = "DESC";} else {$authorDirection = "ASC";}
	
	if ($orderBy == "shortcut") {$orderBySQL = "shortcut"; $directionSQL = $shortcutDirection; $changeShortcut = "shortcut";}
	if ($orderBy == "status") {$orderBySQL = "publishState"; $directionSQL = $statusDirection; $changeStatus = "status";}
	if ($orderBy == "title") {$orderBySQL = "title"; $directionSQL = $titleDirection; $changeTitle = "title";}
	if ($orderBy == "dateCreated") {$orderBySQL = "dateCreated"; $directionSQL = $dateCreatedDirection; $changeDateCreated = "dateCreated";}
	if ($orderBy == "author") {$orderBySQL = "usernameCreated"; $directionSQL = $authorDirection; $changeAuthor = "author";}
	
	if (trim($documentType) != "") {$documentTypeFilter = " AND documentType = '{$documentType}'";}
	if (trim($category) != "") {$categoryFilter = " AND category = '{$category}'";}
	if (trim($subcategory) != "") {$subcategoryFilter = " AND subcategory = '{$subcategory}'";}
	if (trim($subject) != "") {$subjectFilter = " AND subject = '{$subject}'";}
	
	$result = mysql_query("SELECT shortcut FROM documents WHERE 1$documentTypeFilter$categoryFilter$subcategoryFilter$subjectFilter$queryFilter$excludeCategories");
	$totalRows = mysql_num_rows($result);

	$showTotalPages = ceil($totalRows / $max_per_page);

	if ($d == "b") {

		$s -= $max_per_page;

		if ($s < 0) {

			$s = 0;

		}

	}

	if ($d == "n") {

		if ($s + $max_per_page < $totalRows) {

			$s += $max_per_page;

		}

	}

	if ($totalRows > 0) {

		$showCurrentPage = floor($s / $max_per_page) + 1;

	} else {

		$showCurrentPage = 0;

	}
	
	$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i %p') AS newDateCreated FROM documents WHERE 1$documentTypeFilter$categoryFilter$subcategoryFilter$subjectFilter$queryFilter$excludeCategories ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);

	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($documentType, $category, $subcategory, $subject, $filterType, $filterValue, $s, '', $shortcut, $status, $title, $dateCreated, $author, $orderBy, $change);
		
	} else {
		
		if ($count > 0) {
			
			$unsanitizeDocumentType = unsanitize_string($documentType);
			$urlDocumentType = urlencode($unsanitizeDocumentType);
			$htmlDocumentType = htmlentities($unsanitizeDocumentType);
			$escapeDocumentType = preg_replace('/\\\/', '\\\\\\', $htmlDocumentType);
			$escapeDocumentType = preg_replace('/\'/', '\\\'', $escapeDocumentType);
			
			$unsanitizeCategory = unsanitize_string($category);
			$urlCategory = urlencode($unsanitizeCategory);
			$htmlCategory = htmlentities($unsanitizeCategory);
			$escapeCategory = preg_replace('/\\\/', '\\\\\\', $htmlCategory);
			$escapeCategory = preg_replace('/\'/', '\\\'', $escapeCategory);
			
			$unsanitizeSubcategory = unsanitize_string($subcategory);
			$urlSubcategory = urlencode($unsanitizeSubcategory);
			$htmlSubcategory = htmlentities($unsanitizeSubcategory);
			$escapeSubcategory = preg_replace('/\\\/', '\\\\\\', $htmlSubcategory);
			$escapeSubcategory = preg_replace('/\'/', '\\\'', $escapeSubcategory);
			
			$unsanitizeSubject = unsanitize_string($subject);
			$urlSubject = urlencode($unsanitizeSubject);
			$htmlSubject = htmlentities($unsanitizeSubject);
			$escapeSubject = preg_replace('/\\\/', '\\\\\\', $htmlSubject);
			$escapeSubject = preg_replace('/\'/', '\\\'', $escapeSubject);
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print "<form id=\"multipleDocumentsAction\">";
				
			}
			
			print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
			print "<tr>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print "<td class=\"document_list_header\" width=\"10\"><div class=\"document_header_checkbox\"></div></td>";
				
			}
			
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'shortcut', '$changeShortcut');\">Shortcut</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'status', '$changeStatus');\">Status</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'title', '$changeTitle');\">Title</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'dateCreated', '$changeDateCreated');\">Created</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'author', '$changeAuthor');\">Author</a></td>";
			print "<td class=\"document_list_header\">&nbsp;</td>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
				print "<td class=\"document_list_header\">&nbsp;</td>";
				
			}
			
			print "</tr>";
			
			while ($row = mysql_fetch_object($result)) {
				
				$showStatus = htmlentities($row->publishState);
				$showTitle = htmlentities($row->title);
				$showUsername = htmlentities($row->usernameCreated);
				
				//define which publish options to display
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
					$showPublishOptions = "<a href=\"javascript:togglePublishState('$row->id');\" onclick=\"return confirm('Are you sure you want to change the publishing status of this document?');\"><span id=\"publish_state_$row->id\">";
				
					if ($row->publishState == "Published") {
						
						$showPublishOptions .= "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_published.gif\" border=\"0\"> $showStatus</span></a>";
						
					} else {
						
						$showPublishOptions .= "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_unpublished.gif\" border=\"0\"> $showStatus</span></a>";
						
					}
					
				} else {
					
					$showPublishOptions = $showStatus;
					
				}
				
				//define which document editor icon to display
				if ($row->component != 1) {
					
					$documentEditorIcon = "tiny_icon_edit.gif";
					
				} else {
					
					$documentEditorIcon = "tiny_icon_component.gif";
					
				}
				
				//define which document editor options to display
				if ($row->publishState == "Published") {
					
					//validate user access level for published content
					if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
						
						$showDocumentEditorOptions = "<a href=\"documentEditor.php?id=$row->id&documentType=$urlDocumentType&category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject\" title=\"$showTitle\"><img src=\"/assets/core/resources/images/$documentEditorIcon\" border=\"0\"> $showTitle</a>";
						
					} else {
						
						$showDocumentEditorOptions = "<span title=\"$showTitle\"><img src=\"/assets/core/resources/images/$documentEditorIcon\" border=\"0\"> $showTitle</span>";
						
					}
					
				} else {
					
					//display the editor for unpublished content
					$showDocumentEditorOptions = "<a href=\"documentEditor.php?id=$row->id&documentType=$urlDocumentType&category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject\" title=\"$showTitle\"><img src=\"/assets/core/resources/images/$documentEditorIcon\" border=\"0\"> $showTitle</a>";
					
				}
				
				print "<tr class=\"document_container_row\">";
				
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
					
					print "<td class=\"document_container\" width=\"10\"><div class=\"document_header_checkbox\"><input style=\"vertical-align:middle;\" type=\"checkbox\" id=\"multipleId[]\" name=\"multipleId[]\" value=\"$row->id\"></div></td>";
					
				}
				
				print "<td class=\"document_container\"><a href=\"/documents/open/$row->shortcut\" title=\"$row->shortcut\" target=\"_blank\"><img src=\"/assets/core/resources/images/tiny_icon_preview.gif\" border=\"0\"> $row->shortcut</a></td>";
				print "<td class=\"document_container\">$showPublishOptions</td>";
				print "<td class=\"document_container\">$showDocumentEditorOptions</td>";
				print "<td class=\"document_container\">$row->newDateCreated</td>";
				print "<td class=\"document_container\">$showUsername</td>";
				print "<td class=\"document_container\"><a href=\"javascript:cloneDocument('$row->id', '$s', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('Are you sure you want to clone this document?');\" title=\"Clone\"><img src=\"/assets/core/resources/images/tiny_icon_clone.gif\" border=\"0\"></a></td>";
				
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
					print "<td class=\"document_container\"><a href=\"javascript:deleteDocument('$row->id', '$s', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete this document?');\" title=\"Delete\"><img src=\"/assets/core/resources/images/tiny_icon_delete.gif\" border=\"0\"></a></td>";
					
				}
				
				print "</tr>";

			}
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print "<tr>";
				print "<td class=\"document_list_options\" width=\"10\">";
				print "<div class=\"check_all\"><input id=\"check_all\" name=\"check_all\" type=\"checkbox\" onclick=\"$('#multipleDocumentsAction :checkbox').attr('checked', this.checked);\"></div></td><td class=\"document_list_options\" colspan=\"7\"><div class=\"select_all\">Select All</div><div class=\"toggle_selected_state\"><a href=\"javascript:toggleMultipleDocumentsStatus($s, '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('Are you sure you want to change the published status of the selected documents?');\">Switch Status</a></div><div class=\"delete_selected\"><a href=\"javascript:deleteMultipleDocuments($s, '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete the selected documents?');\">Delete</a></div>";
				print "</td>";
				print "</tr>";
				
			}
			
			print "</table>";
			
			print "<div id=\"document_navigation\">";
			print "	<div class=\"totals\">$totalRows Documents</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateDocumentList('$s', 'b', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateDocumentList('$s', 'n', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
			print "</div>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print "</form>";
				
			}
			
		} else {
			
			$urlDocumentType = urlencode(unsanitize_string($documentType));
			$urlCategory = urlencode(unsanitize_string($category));
			$urlSubcategory = urlencode(unsanitize_string($subcategory));
			$urlSubject = urlencode(unsanitize_string($subject));
			
			print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
			print "<tr>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'shortcut', '$changeShortcut');\">Shortcut</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'status', '$changeStatus');\">Status</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'title', '$changeTitle');\">Title</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'dateCreated', '$changeDateCreated');\">Created</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'author', '$changeAuthor');\">Author</a></td>";
			print "<td class=\"document_list_header\">&nbsp;</td>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
				print "<td class=\"document_list_header\"><div class=\"document_list_delete\">&nbsp;</div></td>";
				
			}
			
			print "</tr>";
			print "<tr class=\"document_container_row\">";
			print "<td class=\"document_container\" colspan=\"7\">There are no documents matching the selected criteria.</td>";
			print "</tr>";
			print "</table>";
			
		}
		
		print "<div id=\"editor_options\">";
		print "<a class=\"button\" href=\"documentEditor.php?documentType=$urlDocumentType&category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject\"><span>New Document</span></a>";
		print "</div>";
		
	}
	
}
?>