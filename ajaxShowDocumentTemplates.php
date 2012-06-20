<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");

$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));

include("$script_directory/assets/core/resources/templates/documents/document_templates.php");

print "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">";

for ($x = 0; $x < count($templates); $x++) {
	
	$id = $x;
	$image = $templates[$x]['image'];
	$title = $templates[$x]['title'];
	$description = $templates[$x]['description'];
	$category = $templates[$x]['category'];
	
	//determine if separator class is applied
	if (($x + 1) % count($templates) != 0 && $templates[$x+1]['category'] != "true") {
		
		$separator = " separator";
		
	} else {
		
		$separator = "";
		
	}
	
	if ($category != "true") {
		
		print "<tr class=\"template_row$separator\" valign=\"top\" onClick=\"selectTemplate('$id');\">";
		print "<td width=\"100\">";
		
		if (trim($image) != "") {
			
			print "<img src=\"$image\" style=\"display:block;\">";
			
		}
		
		print "</td><td><b>$title</b><br>$description</td>";
		print "</tr>";
		
	} else {
		
		if ($x > 0) {
			
			print "<tr class=\"template_category_row\" valign=\"top\">";
			
		} else {
			
			print "<tr valign=\"top\">";
			
		}
		
		print "<td colspan=\"2\"><div class=\"template_category\">$title</div></td>";
		print "</tr>";
		
	}
	
}

print "</table>";

?>