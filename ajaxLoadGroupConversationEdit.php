<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_group_membership_validator.php");
include("class_config_reader.php");
include("class_process_bbcode.php");

$id = sanitize_string($_REQUEST['id']);
$groupId = sanitize_string($_REQUEST['groupId']);

//load site group validator
$groupValidator = new GroupValidator();
$groupValidator->isGroupAdmin($groupId, $_SESSION['username']);

if (trim($id) != "") {
	
	$result = mysql_query("SELECT author, body FROM conversationsPosts WHERE id = '{$id}' LIMIT 1");
		
		//catch ivalid ids
		if (mysql_num_rows($result) > 0) {
			
			$row = mysql_fetch_object($result);
			
			//if the user is not an admin or the author, exit
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['username'] == $row->author || $groupValidator->isGroupAdmin) {
			
				print $row->body;
				
			}
			
	}
	
}

?>