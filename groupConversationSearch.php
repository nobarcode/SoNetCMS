<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$search = sanitize_string($_REQUEST['search']);
$groupId = sanitize_string($_REQUEST['groupId']);

if (trim($groupId) == "") {
	
	exit;
	
}

//load main group information
$result = mysql_query("SELECT name FROM groups WHERE id = '{$groupId}' LIMIT 1");
if (mysql_num_rows($result) == 0) {
	
	exit;
	
}

$row = mysql_fetch_object($result);
$groupName = htmlentities($row->name);

$search = unsanitize_string($search);
$search = htmlentities($search);

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/groupConversationSearch.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/groupConversationSearch.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div class="subheader_title"><a href="showGroupConversationsList.php?groupId=$groupId">$groupName</a> > Conversation Search</div>
			<div id="search_options_container" style="display:block;">
				<form id="search">
					<input type="text" id="search" name="search" size="32" value="$search"> <select id="orderBy" name="orderBy"><option value="relevance" selected>Relevance</option><option value="author">Author</option><option value="date">Date Published</option></select> <input type="radio" name="orderDirection" value="desc" checked> Descending <input type="radio" name="orderDirection" value="asc"> Ascending <input type="submit" id="submit" value="Search">
					<input type="hidden" id="groupId" name="groupId" value="$groupId">
				</form>
			</div>
			<div id="post_list"></div>
EOF;

$site_container->showSiteContainerBottom();

?>