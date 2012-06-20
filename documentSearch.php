<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$top_search = sanitize_string($_REQUEST['top_search']);

$search = unsanitize_string($top_search);
$search = htmlentities($search);

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/documentSearch.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/documentSearch.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div class="subheader_title">Search</div>
			<div id="search_options_container" style="display:block;">
				<form id="document_search" method="get" action="ajaxDocumentSearch.php">
					<input type="text" id="search" name="search" size="32" value="$search"> <select id="orderBy" name="orderBy"><option value="relevance" selected>Relevance</option><option value="date">Date Published</option><option value="title">Title</option><option value="author">Author</option><option value="votes">Votes</option><option value="rating">Rating</option></select> <input type="radio" name="orderDirection" value="desc" checked> Descending <input type="radio" name="orderDirection" value="asc"> Ascending <input type="submit" id="submit" value="Search">
				</form>
			</div>	
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