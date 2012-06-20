<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$category = sanitize_string($_REQUEST['category']);
$subcategory = sanitize_string($_REQUEST['subcategory']);
$subject = sanitize_string($_REQUEST['subject']);
$subjectId = sanitize_string($_REQUEST['subjectId']);
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

changeDirection($category, $subcategory, $subject, $subjectId, $filterType, $filterValue, $s, $d, $shortcut, $status, $title, $dateCreated, $author, $orderBy, $change);

function changeDirection($category, $subcategory, $subject, $subjectId, $filterType, $filterValue, $s, $d, $shortcut, $status, $title, $dateCreated, $author, $orderBy, $change) {
	
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
	
	$result = mysql_query("SELECT shortcut FROM documents WHERE category = '{$category}' AND subcategory = '{$subcategory}' AND subject = '{$subject}'$queryFilter$excludeCategories");
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
	
	$result = mysql_query("SELECT *, DATE_FORMAT(dateCreated, '%m/%d/%Y %h:%i %p') AS newDateCreated FROM documents WHERE category = '{$category}' AND subcategory = '{$subcategory}' AND subject = '{$subject}'$queryFilter$excludeCategories ORDER BY $orderBySQL $directionSQL LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);

	if ($count < 1 && $totalRows > 0 && $s > 0) {
		
		$s -= $max_per_page;
		return changeDirection($category, $subcategory, $subject, $subjectId, $filterType, $filterValue, $s, '', $shortcut, $status, $title, $dateCreated, $author, $orderBy, $change);
		
	} else {
		
		if ($count > 0) {
			
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
			
			print "<div id=\"document_list_toolbar\">";
			print "<a href=\"documentEditor.php?category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject\"><img src=\"/assets/core/resources/images/tiny_icon_new.gif\" border=\"0\"> New Document</a>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print " <a href=\"javascript:publishAllDocuments('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('Are you sure you want to publish all documents in this subject?');\"><img src=\"/assets/core/resources/images/tiny_icon_publish_all.gif\" border=\"0\"> Publish All</a> ";
				print " <a href=\"javascript:unpublishAllDocuments('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('Are you sure you want to unpublish all documents in this subject?');\"><img src=\"/assets/core/resources/images/tiny_icon_unpublish_all.gif\" border=\"0\"> Unpublish All</a> ";
				
			}
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
				print " <a href=\"javascript:deleteAllDocuments('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '$shortcut', '$status', '$title', '$image', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('WARNING: Are you sure you want to delete ALL documents in this subject?');\"><img src=\"/assets/core/resources/images/tiny_icon_delete_all.gif\" border=\"0\"> Delete All</a>";
				
			}
			
			print "</div>";
			
			print "<div class=\"clear_right\"></div>";
			
			print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
			print "<tr>";
			print "<td class=\"document_list_header\"><div class=\"document_list_shortcut\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'shortcut', '$changeShortcut');\">Shortcut</a></div></td>";
			print "<td class=\"document_list_header\"><div class=\"document_list_status\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'status', '$changeStatus');\">Status</a></div></td>";
			print "<td class=\"document_list_header\"><div class=\"document_list_title\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'title', '$changeTitle');\">Title</a></div></td>";
			print "<td class=\"document_list_header\"><div class=\"document_list_date\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'dateCreated', '$changeDateCreated');\">Created</a></div></td>";
			print "<td class=\"document_list_header\"><div class=\"document_list_author\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '', '$shortcut', '$status', '$title', '$dateCreated', '$author', 'author', '$changeAuthor');\">Author</a></div></td>";
			print "<td class=\"document_list_header\"><div class=\"document_list_clone\">&nbsp;</div></td>";
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
			
				print "<td class=\"document_list_header\"><div class=\"document_list_delete\">&nbsp;</div></td>";
				
			}
			
			print "</tr>";
			
			while ($row = mysql_fetch_object($result)) {
				
				$showStatus = htmlentities($row->publishState);
				$showTitle = htmlentities($row->title);
				$showUsername = htmlentities($row->usernameCreated);
				
				//define which publish options to display
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
					$showPublishOptions = "<a href=\"javascript:togglePublishState('$row->id', '$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId');\" onclick=\"return confirm('Are you sure you want to change the publishing status of this document?');\"><span id=\"publish_state_$row->id\">";
				
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
						
						$showDocumentEditorOptions = "<a href=\"documentEditor.php?id=$row->id\" title=\"$showTitle\"><img src=\"/assets/core/resources/images/$documentEditorIcon\" border=\"0\"> $showTitle</a>";
						$showGalleryEditorOptions = "<a href=\"galleryEditor.php?id=$row->id\" title=\"$row->imageCount\"><img src=\"/assets/core/resources/images/tiny_icon_image.gif\" border=\"0\"> $row->imageCount</a>";
						
					} else {
						
						$showDocumentEditorOptions = "<span title=\"$showTitle\"><img src=\"/assets/core/resources/images/$documentEditorIcon\" border=\"0\"> $showTitle</span>";
						$showGalleryEditorOptions = "<span title=\"$row->imageCount\"><img src=\"/assets/core/resources/images/tiny_icon_image.gif\" border=\"0\"> $row->imageCount</span>";
						
					}
					
				} else {
					
					//display the editor for unpublished content
					$showDocumentEditorOptions = "<a href=\"documentEditor.php?id=$row->id\" title=\"$showTitle\"><img src=\"/assets/core/resources/images/$documentEditorIcon\" border=\"0\"> $showTitle</a>";
					$showGalleryEditorOptions = "<a href=\"galleryEditor.php?id=$row->id\" title=\"$row->imageCount\"><img src=\"/assets/core/resources/images/tiny_icon_image.gif\" border=\"0\"> $row->imageCount</a>";
					
				}
				
				print "<tr>";
				print "<td class=\"document_container\"><div class=\"document_list_shortcut\"><a href=\"/documents/open/$row->shortcut\" title=\"$row->shortcut\" target=\"_blank\"><img src=\"/assets/core/resources/images/tiny_icon_preview.gif\" border=\"0\"> $row->shortcut</a></div></td>";
				print "<td class=\"document_container\"><div class=\"document_list_status\">$showPublishOptions</div></td>";
				print "<td class=\"document_container\"><div class=\"document_list_title\">$showDocumentEditorOptions</div></td>";
				print "<td class=\"document_container\"><div class=\"document_list_date\">$row->newDateCreated</div></td>";
				print "<td class=\"document_container\"><div class=\"document_list_author\">$showUsername</div></td>";
				print "<td class=\"document_container\"><div class=\"document_list_clone\"><a href=\"javascript:cloneDocument('$row->id', '$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('Are you sure you want to clone this document?');\" title=\"Clone\"><img src=\"/assets/core/resources/images/tiny_icon_clone.gif\" border=\"0\"></a></div></td>";
				
				if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) {
				
					print "<td class=\"document_container\"><div class=\"document_list_delete\"><a href=\"javascript:deleteDocument('$row->id', '$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', '$s', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy');\" onclick=\"return confirm('Are you sure you want to delete this document?');\" title=\"Delete\"><img src=\"/assets/core/resources/images/tiny_icon_delete.gif\" border=\"0\"></a></div></td>";
					
				}
				
				print "</tr>";

			}

			print "</table>";
			
			print "<div id=\"document_navigation\">";
			print "	<div class=\"totals\">$totalRows Documents Found</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', $s, 'b', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy', '');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateDocumentList('$escapeCategory', '$escapeSubcategory', '$escapeSubject', '$subjectId', $s, 'n', '$shortcut', '$status', '$title', '$dateCreated', '$author', '$orderBy', '');\" title=\"Next Results\">Next</a></div></div>";
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
			
			print "<div id=\"document_list_toolbar\">";
			print "<a href=\"documentEditor.php?category=$urlCategory&subcategory=$urlSubcategory&subject=$urlSubject\"><img src=\"/assets/core/resources/images/tiny_icon_new.gif\" border=\"0\"> New Document</a>";
			print "</div>";
			print "<div class=\"clear_right\"></div>";
			print "<div style=\"padding:2px;\">There are no documents matching the selected criteria.</div>";
			
		}
		
	}
	
}
?>