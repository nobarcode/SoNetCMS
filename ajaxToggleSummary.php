<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$limit = sanitize_string($_REQUEST['limit']);
$elementId = sanitize_string($_REQUEST['elementId']);
$type = sanitize_string($_REQUEST['type']);
$action = sanitize_string($_REQUEST['action']);

if ($type == 'blog') {
	
	$result = mysql_query("SELECT summary FROM blogs WHERE id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);
	$linkTo = "/blogs/id/$id";
	
} else {
	
	$result = mysql_query("SELECT shortcut, summary FROM documents WHERE id = '{$id}' LIMIT 1");
	$row = mysql_fetch_object($result);
	$linkTo = "/documents/open/$row->shortcut";
	
}

if ($action == "expand") {
	
	$summary = htmlentities($row->summary);
	$summary = preg_replace("/\\n/", "<br>", $summary);
	
	print "$summary <a href=\"javascript:toggleSummary('$elementId', '$id', 'limit', '$limit', '$type');\">&#171;&nbsp;less</a>&nbsp;|&nbsp;<a href=\"$linkTo\">view</a>\n";
	
} else {
	
	print word_limiter($row->summary, $limit, $elementId, $id, $type) . "\n";
	
}

function word_limiter($str, $limit, $elementId, $id, $type) {
    
    // Don't bother about empty strings.
    // Get rid of them here because the regex below would match them too.
    if (trim($str) == '')
        return $str;
    
    // Added the initial \s* in order to make the regex work in case $str starts with whitespace.
    // Without it a string like " test" would be counted for two words instead of one.

    // This HIGHLY OPTIMIZED regexp pattern says:
    // anchor the pattern to the beginning of the string "^", 
    // then look for any number of space chars "\s*"
    // (but, b/c they are outside the parens, don't 
    // include them in the repetition count of the next sub-pattern)
    // then use a non-capturing sub-pattern "(?:)"
    // ( "?:" tells preg not to make a $matches[1] even though there is a parentheses)
    // and look for at least one non-space char followed by 0 or 1 space char "\S+\s*"
    // there must be at least 1 and not more than $limit 
    // repetitions of this non-capturing sub-pattern "{1,x}"
    preg_match('/^\s*+(?:\S++\s*+){1,'. (int) $limit .'}/', $str, $matches);
	
	// Only add end character if the string got chopped off.
	if (strlen($matches[0]) < strlen($str)) {
		
		$end_char = "&#8230;&nbsp;<a href=\"javascript:toggleSummary('$elementId', '$id', 'expand', '$limit', '$type');\">more&nbsp;&#187;</a>";
		
	}
	
	//html safe conversion	
	$matches[0] = htmlentities($matches[0]);
	$matches[0] = preg_replace("/\\n/", "<br>", $matches[0]);
	
	// Chop off trailing whitespace and add the end character.
    return rtrim($matches[0]) . $end_char;
}

?>