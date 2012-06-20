<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("requestVariableSanitizer.inc");
include("class_config_reader.php");

$filter = sanitize_string($_REQUEST['filter']);

//read config file and determine if the forms processor is enabled, if not then exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('enableRssFeed') != 'true') {
	
	exit;
	
}


//Restrict viewing when usergroups are assigned to categories
$result = mysql_query("SELECT category FROM categoriesUserGroups INNER JOIN userGroups ON categoriesUserGroups.groupId = userGroups.id AND userGroups.restrictViewing = '1'");
		
if (mysql_num_rows($result) > 0) {
	
	while ($row = mysql_fetch_object($result)) {

		if ($row->restrictViewing != '1') {

			$restrictViewing .= " AND category != '" . sanitize_string($row->category) . "'";

		}

	}
	
}


if (trim($filter) == "") {
	
	//all
	$result = mysql_query("SELECT id AS shortcut, category, title, DATE_FORMAT(dateCreated, '%a, %d %b %Y %T') AS showDate, summary, '0' AS doNotSyndicate, '0' AS component, 'blog' AS docType FROM blogs WHERE publishState = 'Published'$restrictViewing UNION SELECT shortcut, category, title, DATE_FORMAT(datePublished, '%a, %d %b %Y %T') AS showDate, summary, doNotSyndicate, component, 'document' AS docType FROM documents WHERE publishState = 'Published' AND doNotSyndicate != '1' AND component != '1'$restrictViewing ORDER BY showDate DESC LIMIT 50");
	
} elseif ($filter == "documents") {
	
	//documents
	$result = mysql_query("SELECT shortcut, category, title, DATE_FORMAT(datePublished, '%a, %d %b %Y %T') AS showDate, summary, doNotSyndicate, component, 'document' AS docType FROM documents WHERE publishState = 'Published' AND doNotSyndicate != '1' AND component != '1'$restrictViewing ORDER BY showDate DESC LIMIT 50");
	
} elseif ($filter == "blogs") {
	
	//blogs
	$result = mysql_query("SELECT id AS shortcut, category, title, DATE_FORMAT(dateCreated, '%a, %d %b %Y %T') AS showDate, summary, 'blog' AS docType FROM blogs WHERE publishState = 'Published'$restrictViewing ORDER BY showDate DESC LIMIT 50");
	
}

$website = preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']);
$websiteURL = "http://" . $_SERVER['HTTP_HOST'];

header('Content-type: application/xml');

print <<< EOF
<?xml version="1.0"?>
<rss version="2.0">

	<channel>
		<title>$website</title>
		<description>Most recently published from $website</description>
		<link>$websiteURL</link>

EOF;

////Cycle through each item and display.
while ($row = mysql_fetch_object($result)) {

$title = htmlentities($row->title);
$showDate = $row->showDate . " " . date('T');
$summary = htmlentities($row->summary);

if ($row->docType == "document") {
	
	$linkTo = "$websiteURL/documents/open/$row->shortcut";
	
} elseif ($row->docType == "blog") {
	
	$linkTo = "$websiteURL/blogs/id/$row->shortcut";
	
}

print <<< EOF
		<item>
			<title>$title</title>
			<pubDate>$showDate</pubDate>
			<description>$summary</description>
			<link>$linkTo</link>
		</item>
		
EOF;

}

print <<< EOF
	</channel>
</rss>
EOF;

?>