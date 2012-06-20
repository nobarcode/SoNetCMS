<?

// the following variables can be used to display status messages:
// 
// > $type: identifies the type of status, currently:
//
//   	file		-	this status is for a file request
//   	profile		-	thsi status is related to a profile
//   	gallery		-	thsi status is related to an image gallery
//   	document	-	thsi status is related to an image gallery
// 
// > $notification: conatins the circumstances of this status, currently:
// 	 	
//   	#file:
// 		 friends			-	the file security is set to friends and can only be accessed by an authenitcated user that is friends with the owner
// 		 private			-	the file security is set to private adn can only be accessed by the owner of the file
//		 authenticated		-	the file security is set to authenticated and can only be accessed by a user that is authenitcated
// 		
//   	#profile:
// 		 not found			-	the requested profile was not found
// 		
//   	#gallery:
// 		 empty				-	the gallery doesn't contain any images
//   	
//   	#document:
// 		 not found			-	the requested document was not found
//   	
// > $object: contains either the full path and filename of the requested file (if applicable) or a document shortcut/id
// 
// > $owner: contains the username associated with the requested file (if applicable)

if ($type == "file") {
	
	$showFilename = basename($object);
	
	if ($notification == "private") {
		
		$title = "File Unavailable: $showFilename";
		$explanation = "The file you requested is currently unavailable.";
		$additional = "- This file is not available due to the security settings specified by its owner.<br />- Some files can only be accessed by their owners.";
		
	} elseif ($notification == "friends") {
		
		$title = "Exclusive Access: $showFilename";
		$explanation = "The file you requested is only available to $owner's friends.";
		$additional = "- This file is not available due to the security settings specified by its owner.<br />- You can request to be added to $owner's friend list by visiting their <a href=\"/showProfile.php?username=$owner\">profile page</a>.";
	
	} elseif ($notification == "authenticated") {
		
		$title = "Authentication Required: $showFilename";
		$explanation = "The file you requested is currently unavailable.";
		$additional = "- This file is not available due to the security settings specified by its owner.<br />- The file can only be accessed by members that are signed in.<br />- Please <a href=\"/signIn.php\">sign in</a> and try your request again.";
		
	} elseif ($notification == "user group access") {
		
		$title = "Access Denied: $showFilename";
		$explanation = "The file you requested has been restricted to a group or groups by an administrator.";
		$additional = "- You must be a group member to access this file.<br />- Only an administrator can add you to the necessary group.";
		
	}
	
} elseif ($type == "profile") {
	
	if ($notification == "not found") {
		
		$title = "Profile Not Found: $owner";
		$explanation = "The requested profile does not exist.";
		$additional = "- The specified username is invalid.<br />- The profile may have been deleted.<br />- If a user's account is pending approval, the associated profile cannot be viewed.<br />- You can try <a href=\"profileSearch.php\">searching</a> for the person.";
		
	}
	
} elseif ($type == "gallery") {
	
	if ($notification == "empty") {
		
		$title = "No Images";
		$explanation = "The selected image gallery is empty.";
		$additional = "- No additional information is available.";
		
	}
	
} elseif ($type == "document") {
	
	if ($notification == "not found") {
		
		$title = "Document Not Found: $object";
		$explanation = "The requested document does not exist.";
		$additional = "- The specified shortcut is invalid.<br />- You can try <a href=\"/documentSearch.php\">searching</a> for the document.";
		
	} elseif ($notification == "user group access") {
		
		$title = "Access Denied: $object";
		$explanation = "The requested document has been restricted to a group or groups by an administrator.";
		$additional = "- You must be a group member to view this document.<br />- Only an administrator can add you to the necessary group.";
		
	}
	
} elseif ($type == "blog") {
	
	if ($notification == "not found") {
		
		$title = "Blog Not Found: ID #$object";
		$explanation = "The requested blog does not exist.";
		$additional = "- The specified id is invalid.<br />- You can try <a href=\"/documentSearch.php\">searching</a> for the blog.";
		
	} elseif ($notification == "user group access") {
		
		$title = "Access Denied: ID #$object";
		$explanation = "The requested blog has been restricted to a group or groups by an administrator.";
		$additional = "- You must be a group member to view this blog.<br />- Only an administrator can add you to the necessary group.";
		
	}
	
} elseif ($type == "event") {
	
	if ($notification == "not found") {
		
		$title = "Event Not Found: ID #$object";
		$explanation = "The requested event does not exist.";
		$additional = "- The specified id is invalid.";
		
	} elseif ($notification == "user group access") {
		
		$title = "Access Denied: ID #$object";
		$explanation = "The requested event has been restricted to a group or groups by an administrator.";
		$additional = "- You must be a group member to view this event.<br />- Only an administrator can add you to the necessary group.";
		
	}
	
} elseif ($type == "group") {
	
	if ($notification == "not found") {
		
		$title = "Group Not Found: ID #$object";
		$explanation = "The requested group does not exist.";
		$additional = "- The specified id is invalid.<br />- You can try <a href=\"/groupSearch.php\">searching</a> for the group.";
		
	}
	
}

print <<< EOF
			<div class="subheader_title"><div class="subheader_inner_container">$title</div></div>
			<div id="message_body">
				<div id="explanation">
					$explanation
				</div>
				<div id="additional_container">
					<div id="additional_title">Additional Information:</div>
					<div id="additional">
						$additional
					</div>
				</div>
			</div>
			<div id="back_button">
				<a class="button" href="javascript:window.history.back();"><span>Back</span></a>
			</div>
EOF;

?>