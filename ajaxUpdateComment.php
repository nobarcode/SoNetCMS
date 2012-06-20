<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$id = sanitize_string($_REQUEST['id']);
$value = sanitize_string($_REQUEST['value']);
$type = sanitize_string($_REQUEST['type']);

if (trim($id) == "" || trim($value) == "" || trim($type) == "") {
	
	exit;
	
}

if ($error != 1) {
	
	$time = time();
	
	switch ($type) {
		
		case "documentComment":
			
			$result = mysql_query("SELECT * FROM commentsDocuments WHERE id = '{$id}' LIMIT 1");
			$row = mysql_fetch_object($result);

			if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['username'] != $row->username) {
				
				exit;

			}
			
			//add comment to database
			$result = mysql_query("UPDATE commentsDocuments SET dateUpdated = '{$time}', body = '{$value}' WHERE id = '{$id}'");
			break;
		
		case "documentImageComment":
			
			$result = mysql_query("SELECT * FROM commentsImages WHERE id = '{$id}' LIMIT 1");
			$row = mysql_fetch_object($result);

			if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['username'] != $row->username) {

				exit;

			}
			
			//add comment to database
			$result = mysql_query("UPDATE commentsImages SET dateUpdated = '{$time}', body = '{$value}' WHERE id = '{$id}'");
			break;
		
		case "userProfileComment":

			$result = mysql_query("SELECT * FROM commentsUserProfiles WHERE id = '{$id}' LIMIT 1");
			$row = mysql_fetch_object($result);

			if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['username'] != $row->username) {

				exit;

			}

			//add comment to database
			$result = mysql_query("UPDATE commentsUserProfiles SET dateUpdated = '{$time}', body = '{$value}' WHERE id = '{$id}'");
			break;
			
		case "userImageComment":
			
			$result = mysql_query("SELECT * FROM commentsImages WHERE id = '{$id}' LIMIT 1");
			$row = mysql_fetch_object($result);

			if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['username'] != $row->username) {

				exit;

			}
			
			//add comment to database
			$result = mysql_query("UPDATE commentsImages SET dateUpdated = '{$time}', body = '{$value}' WHERE id = '{$id}'");
			break;
			
		case "blogComment":
			
			$result = mysql_query("SELECT * FROM commentsDocuments WHERE id = '{$id}' LIMIT 1");
			$row = mysql_fetch_object($result);

			if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['username'] != $row->username) {

				exit;

			}
			
			//add comment to database
			$result = mysql_query("UPDATE commentsDocuments SET dateUpdated = '{$time}', body = '{$value}' WHERE id = '{$id}'");
			break;
			
		case "eventComment":

			$result = mysql_query("SELECT * FROM commentsDocuments WHERE id = '{$id}' LIMIT 1");
			$row = mysql_fetch_object($result);

			if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['username'] != $row->username) {

				exit;

			}

			//add comment to database
			$result = mysql_query("UPDATE commentsDocuments SET dateUpdated = '{$time}', body = '{$value}' WHERE id = '{$id}'");
			break;
			
		case "groupImageComment":
			
			$result = mysql_query("SELECT * FROM commentsImages WHERE id = '{$id}' LIMIT 1");
			$row = mysql_fetch_object($result);
			
			if ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['username'] != $row->username) {
				
				exit;
				
			}
			
			//add comment to database
			$result = mysql_query("UPDATE commentsImages SET dateUpdated = '{$time}', body = '{$value}' WHERE id = '{$id}'");
			break;
			
	}
	
	$body = htmlentities(unsanitize_string($value));
	$body = preg_replace("/\n|\r\n/", "<br>", $body);
	$body = preg_replace('/\'/', '\\\'', $body);
	
	header('Content-type: application/javascript');
	print "$('#comment_$id').html('$body');";
	print "$('#comment_container_$id').show();";
	exit;
	
} else {

	$showMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('$showMessage');\n";
	print "$('#message_box').show();\n";
	exit;
	
}

?>