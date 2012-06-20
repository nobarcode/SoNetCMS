<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);

if (trim($id) != "") {
	
	$result = mysql_query("SELECT body FROM events WHERE id = '{$id}' LIMIT 1");
		
		//catch ivalid ids
		if (mysql_num_rows($result) > 0) {
			
			$row = mysql_fetch_object($result);
			print "$row->body";
			
	}
	
}

?>