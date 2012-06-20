<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");

$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));

include("$script_directory/assets/core/resources/templates/documents/document_templates.php");

$id = sanitize_string($_REQUEST['id']);
$html = $templates[$id]['html'];
$css = $templates[$id]['css'];

$file = file_get_contents("$script_directory$html");

$escapeCssPath = preg_replace('/\\\/', '\\\\\\', $css);
$escapeCssPath = preg_replace('/\'/', '\\\'', $escapeCssPath);

$escapeBody = preg_replace('/\\\/', '\\\\\\', $file);
$escapeBody = preg_replace("/\\n/", "\\\\n", $escapeBody);
$escapeBody = preg_replace("/\\r/", "\\\\r", $escapeBody);
$escapeBody = preg_replace('/\'/', '\\\'', $escapeBody);

//output javascript
header('Content-type: application/javascript');

//call the select function
print "window.opener.selectTemplate('$escapeCssPath','$escapeBody');";

?>