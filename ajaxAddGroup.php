<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$groupId = sanitize_string($_REQUEST['groupId']);
$name = sanitize_string($_REQUEST['name']);
$approvalRequired = sanitize_string($_REQUEST['approvalRequired']);
$exclusiveRequired = sanitize_string($_REQUEST['exclusiveRequired']);
$allowNonMemberPosting = sanitize_string($_REQUEST['allowNonMemberPosting']);
$summary = sanitize_string($_REQUEST['summary']);
$summaryImage = sanitize_string($_REQUEST['summaryImage']);

//build error messages if any of the following fields are empty
if (trim($name) == "") {$error = 1; $errorMessage .= "- Please enter a name.<br>";}
if (trim($summary) == "") {$error = 1; $errorMessage .= "- Please enter a summary for your group in the about section.<br>";}

//if an error has occurred, perform the following:
if ($error == 1) {
	
	//build main error container
	$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>$errorMessage";
	
	header('Content-type: application/javascript');
	print "$('#message_box').html('$showErrorMessage');";
	print "$('#message_box').show();";
	exit;

// if no error occurred, handle the data:	
} else {
	
	//define showDetails if it's empty
	if (trim($approvalRequired) == "") {$approvalRequired = "0";}
	
	//define showToolbar if it's empty
	if (trim($exclusiveRequired) == "") {$exclusiveRequired = "0";}
	
	//get the current date and time
	$time = date("Y-m-d H:i:s", time());
	
	//if this is a new group, do an insert
	if (trim($groupId) == "") {
		
		//populate the database
		$result = mysql_query("INSERT INTO groups (name, dateCreated, approvalRequired, exclusiveRequired, allowNonMemberPosting, summary, summaryImage) VALUES ('{$name}', '{$time}', '{$approvalRequired}', '{$exclusiveRequired}', '{$allowNonMemberPosting}', '{$summary}', '{$summaryImage}')");
		
		//grab the id for this new group ID
		$groupId = mysql_result(mysql_query("SELECT LAST_INSERT_ID() AS id"), 0, "id");
		
		if (!$result) {
			
			//build main error container
			$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>- System error. Unable to create your group.";

			header('Content-type: application/javascript');
			print "$('#message_box').html('$showErrorMessage');";
			print "$('#message_box').show();";
			exit;
			
		} else {
			
			$result = mysql_query("INSERT INTO groupsMembers (parentId, username, memberLevel, dateJoined, status) VALUES ($groupId, '{$_SESSION['username']}', '1', '{$time}', 'approved')");
				
			//check if group owner was added successfully, if not, delete the group and display an error
			if (!$result) {
				
				//roll back database
				$result = mysql_query("DELETE groups WHERE id = '{$groupId}'");
				
				//build main error container
				$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>- System error. Unable to add owner.";
				header('Content-type: application/javascript');
				print "$('#message_box').html('$showErrorMessage');";
				print "$('#message_box').show();";
				exit;
				
			} else {
				
				$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
				mkdir("$script_directory/cms_groups/$groupId");
				
			}
			
			//jump to the image gallery editor for this new base article
			header('Content-type: application/javascript');
			print "window.location = '/groups/id/$groupId';";
			exit;
			
		}
		
	//if this is an update to an existing group, do an update	
	} else {
		
		//validate user access level
		$result = mysql_query("SELECT parentId FROM groupsMembers WHERE parentId = '{$groupId}' AND username = '{$_SESSION['username']}' AND (memberLevel = '1' OR memberLevel = '2') AND status = 'approved'");
		
		if (mysql_num_rows($result) == 0 && ($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2)) {
			
			//build main error container
			$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>- You are not authorized to edit this group.";

			header('Content-type: application/javascript');
			print "$('#message_box').html('$showErrorMessage');";
			print "$('#message_box').show();";
			exit;
			
		}
			
		//populate the database
		$result = mysql_query("UPDATE groups SET name = '{$name}', approvalRequired = '{$approvalRequired}', exclusiveRequired = '{$exclusiveRequired}', allowNonMemberPosting = '{$allowNonMemberPosting}', summaryImage = '{$summaryImage}', summary = '{$summary}' WHERE id = '{$groupId}'");
		
		if (!$result) {

			//build main error container
			$showErrorMessage = "<b>There was an error processing your request, please check the following:</b><br>- System error. Unable to update your group.";

			header('Content-type: application/javascript');
			print "$('#message_box').html('$showErrorMessage');";
			print "$('#message_box').show();";
			exit;

		}			
		
		//jump to the image gallery editor for this new base article
		header('Content-type: application/javascript');
		print "window.location = '/groups/id/$groupId';";
		exit;
			
	}
		
}
	
?>