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
			$htmlDocumentType = htmlentities($unsanitizeDocumentType);
			$escapeDocumentType = preg_replace('/\\\/', '\\\\\\', $htmlDocumentType);
			$escapeDocumentType = preg_replace('/\'/', '\\\'', $escapeDocumentType);
			
			$unsanitizeCategory = unsanitize_string($category);
			$htmlCategory = htmlentities($unsanitizeCategory);
			$escapeCategory = preg_replace('/\\\/', '\\\\\\', $htmlCategory);
			$escapeCategory = preg_replace('/\'/', '\\\'', $escapeCategory);
			
			$unsanitizeSubcategory = unsanitize_string($subcategory);
			$htmlSubcategory = htmlentities($unsanitizeSubcategory);
			$escapeSubcategory = preg_replace('/\\\/', '\\\\\\', $htmlSubcategory);
			$escapeSubcategory = preg_replace('/\'/', '\\\'', $escapeSubcategory);
			
			$unsanitizeSubject = unsanitize_string($subject);
			$htmlSubject = htmlentities($unsanitizeSubject);
			$escapeSubject = preg_replace('/\\\/', '\\\\\\', $htmlSubject);
			$escapeSubject = preg_replace('/\'/', '\\\'', $escapeSubject);
			
			print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
			print "<tr>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'shortcut', '$changeShortcut');\">Shortcut</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'status', '$changeStatus');\">Status</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'title', '$changeTitle');\">Title</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'dateCreated', '$changeDateCreated');\">Created</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'author', '$changeAuthor');\">Author</a></td>";
			print "</tr>";
			
			while ($row = mysql_fetch_object($result)) {
				
				$showStatus = htmlentities($row->publishState);
				
				$showCategory = htmlentities($row->category);
				$showSubcategory = htmlentities($row->subcategory);
				$showSubject = htmlentities($row->subject);
				
				$showTitle = htmlentities($row->title);
				$showUsername = htmlentities($row->usernameCreated);
				
				//define which publish options to display
				if ($row->publishState == "Published") {
					
					$showPublishOptions = "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_published.gif\" border=\"0\"> $showStatus</span></a>";
					
				} else {
					
					$showPublishOptions = "<img style=\"margin:0px; padding:0px;\" src=\"/assets/core/resources/images/tiny_icon_unpublished.gif\" border=\"0\"> $showStatus</span></a>";
					
				}
				
				//define which document editor icon to display
				if ($row->component != 1) {
					
					$documentEditorIcon = "tiny_icon_select_document.gif";
					
				} else {
					
					$documentEditorIcon = "tiny_icon_component.gif";
					
				}
				
				print "<tr class=\"document_container_row\">";
				print "<td class=\"document_container\"><a href=\"/documents/open/$row->shortcut\" title=\"$showCategory/$showSubcategory/$showSubject\" target=\"blank\"><img src=\"/assets/core/resources/images/tiny_icon_preview.gif\" border=\"0\"> $row->shortcut</a></td>";
				print "<td class=\"document_container\">$showPublishOptions</td>";
				print "<td class=\"document_container\"><a href=\"javascript:selectDocument('/documents/open/$row->shortcut');\"><img src=\"/assets/core/resources/images/$documentEditorIcon\" border=\"0\"> $showTitle</td>";
				print "<td class=\"document_container\">$row->newDateCreated</td>";
				print "<td class=\"document_container\">$showUsername</td>";
				print "</tr>";

			}

			print "</table>";
			
			print "<div id=\"document_navigation\">";
			print "	<div class=\"totals\">$totalRows Documents Found</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateDocumentList('$s', 'b', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateDocumentList('$s', 'n', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
			print "</div>";
			
		} else {
			
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
			
			$subject = unsanitize_string($subject);
			$urlSubject = urlencode($subject);
			$htmlSubject = htmlentities($subject);
			$escapeSubject = preg_replace('/\\\/', '\\\\\\', $htmlSubject);
			$escapeSubject = preg_replace('/\'/', '\\\'', $escapeSubject);

			print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
			print "<tr>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'shortcut', '$changeShortcut');\">Shortcut</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'status', '$changeStatus');\">Status</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'title', '$changeTitle');\">Title</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'dateCreated', '$changeDateCreated');\">Created</a></td>";
			print "<td class=\"document_list_header\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'author', '$changeAuthor');\">Author</a></td>";
			print "<tr class=\"document_container_row\">";
			print "<td class=\"document_container\" colspan=\"5\">There are no documents matching the selected criteria.</td>";
			print "</tr>";
			print "</table>";
			
		}
		
	}
	
}
?>