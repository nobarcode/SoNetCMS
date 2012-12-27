<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$listCategory = sanitize_string($_REQUEST['listCategory']);
$listSubcategory = sanitize_string($_REQUEST['listSubcategory']);
$listSubject = sanitize_string($_REQUEST['listSubject']);
$listAuthor = sanitize_string($_REQUEST['listAuthor']);

$listCategory = unsanitize_string($listCategory);
$urlListCategory = urlencode($listCategory);
$listCategory = htmlentities($listCategory);
if (trim($listCategory) != "") {
	
	if (trim($listSubcategory) != "" || trim($listSubject) != "") {
		
		$searchCriteria['category'] = "<a href=\"documentList.php?listCategory=$urlListCategory\">$listCategory</a>";
		
	} else {
		
		$searchCriteria['category'] = $listCategory;
		
	}
	
}

$listSubcategory = unsanitize_string($listSubcategory);
$urlListSubcategory = urlencode($listSubcategory);
$listSubcategory = htmlentities($listSubcategory);
if (trim($listSubcategory) != "") {
	
	if (trim($listSubject) != "") {
		
		$searchCriteria['subcategory'] = "<a href=\"documentList.php?listCategory=$urlListCategory&listSubcategory=$urlListSubcategory\">$listSubcategory</a>";
		
	} else {
		
		$searchCriteria['subcategory'] = $listSubcategory;
		
	}
	
}

$listSubject = unsanitize_string($listSubject);
$listSubject = htmlentities($listSubject);
if (trim($listSubject) != "") {$searchCriteria['subject'] = $listSubject;}

$listAuthor = unsanitize_string($listAuthor);
$listAuthor = htmlentities($listAuthor);
if (trim($listAuthor) != "") {$searchCriteria['author'] = $listAuthor;}

foreach($searchCriteria as $value) {
	
	$x++;
	
	$showSearch .= $value;
	
	if ($x < count($searchCriteria)) {
		
		$showSearch .= " &gt; ";
		
	}
	
}

if ((trim($searchCriteria['category']) != "" || trim($searchCriteria['subcategory']) != "" || trim($searchCriteria['subject']) != "") && trim($searchCriteria['author']) == "") {
	
	$header = "Browsing: ";
	
} else {
	
	$header = "Authored by: ";
	
}

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/documentList.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/documentList.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div class="subheader_title">$header$showSearch</div>
			<div id="search_options_container" style="display:block;">
				<form id="document_search" method="get" action="ajaxDocumentSearch.php">
					<select id="orderBy" name="orderBy"><option value="date">Date Published</option><option value="title">Title</option><option value="author">Author</option><option value="votes">Votes</option><option value="rating">Rating</option></select> <input type="radio" name="orderDirection" value="desc" checked> Descending <input type="radio" name="orderDirection" value="asc"> Ascending
					<input type="hidden" id="listCategory" name="listCategory" value="$listCategory">
					<input type="hidden" id="listSubcategory" name="listSubcategory" value="$listSubcategory">
					<input type="hidden" id="listSubject" name="listSubject" value="$listSubject">
					<input type="hidden" id="listAuthor" name="listAuthor" value="$listAuthor">
				</form>
			</div>
			<div id="message_box" class="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div id="documents_list"></div>
EOF;

$site_container->showSiteContainerBottom();

function showOptions($options, $selected) {
	
	for($x = 0; $x < count($options); $x++) {
		
		if ($selected == $options[$x]) {
			
			$return .= "<option value=\"" . $options[$x] . "\" selected>" . htmlentities($options[$x]) . "</option>";
			
		} else {
			
			$return .= "<option value=\"" . $options[$x] . "\">" . htmlentities($options[$x]) . "</option>";
			
		}
		
	}
	
	return($return);
		
}

?>