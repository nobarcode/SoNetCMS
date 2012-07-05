<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$subject = sanitize_string($_REQUEST['subject']);
$body = sanitize_string($_REQUEST['body']);
$id = sanitize_string($_REQUEST['id']);
$reply = sanitize_string($_REQUEST['reply']);
$toUser = sanitize_string($_REQUEST['toUser']);
$returnURL = sanitize_string($_REQUEST['returnURL']);

if (trim($_SESSION['username']) == "" || trim($toUser) == "") {
	
	exit;
	
}

if (trim($subject) == "") {$error = 1; $errorMessage .= "- Please supply a subject.<br>";}
if (trim($body) == "") {$error = 1; $errorMessage .= "- The body of your message is empty.<br>";}

if ($error != 1) {
	
	//read config file
	$config = new ConfigReader();
	$config->loadConfigFile('assets/core/config/config.properties');
	
	$time = time();
	
	mysql_query("INSERT INTO messages (dateSent, toUser, FromUser, subject, body, status) VALUES ({$time}, '{$toUser}', '{$_SESSION['username']}', '{$subject}', '{$body}', 'unread')");
	
	$result = mysql_query("SELECT name, email FROM users WHERE username = '{$toUser}' AND allowEmailNotifications = 1");
	
	while ($row = mysql_fetch_object($result)) {
		
		include("assets/core/config/notifications/process_message/notification.php");
		
		$to = $row->email;
		
		$notificationEmail = "<html>";
		$notificationEmail .= "<body>";
		$notificationEmail .= $notificationText;
		$notificationEmail .= "</body>";
		$notificationEmail .= "</html>";
		
		$headers = "MIME-Version: 1.0\r\n"; 
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
		$headers .= "From: " . $config->readValue('siteEmailAddress') . "\r\n";
		$headers .= "Reply-To: " . $config->readValue('siteEmailAddress') . "\r\n";
		
		mail($to, $subject, $notificationEmail, $headers);
		
	}
	
	$returnURL = unsanitize_string($returnURL);
	header("Location: $returnURL");
	exit;
	
} 

if (trim($reply) != "") {
	
	$body = getReplyText($reply);
	
}

$subject = htmlentities(unsanitize_string($subject));
$toUser = htmlentities(unsanitize_string($toUser));
$body = htmlentities(unsanitize_string($body));
$htmlReturnURL = htmlentities(unsanitize_string($returnURL));
$showMessage = "<div id=\"message_box\" onClick=\"$('#message_box').hide();\"><b>There was an error processing your request, please check the following:</b><br>$errorMessage</div>";

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/composeMessage.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/composeMessage.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			$showMessage
			<div class="subheader_title">Compose Message</div>
			<div class="editor_box_container">	
				<form id="compose_message" method="post" action="processComposeMessage.php">
					<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>To:</td><td width="100%" align="left"><div class="to_user">$toUser</div></td></tr>
					<tr valign="center"><td nowrap>Subject:</td><td width="100%" align="left"><input type="text" id="subject" name="subject" style="width:99%;" value="$subject"></td></tr>
					<tr valign="center"><td colspan="2"><textarea id="body" name="body" rows="16" style="width:99%">$body</textarea></td></tr>
					<tr valign="center"><td colspan="2"><input type="submit" id="submit" value="Send"> <input type="button" id="cancel" value="Cancel" onClick="window.location='$returnURL';"></td></tr>
					<input type="hidden" id="reply" name="reply" value="$reply">
					<input type="hidden" id="toUser" name="toUser" value="$toUser">
					<input type="hidden" id="returnURL" name="returnURL" value="$htmlReturnURL">
					</table>
				</form>
			</div>
EOF;

$site_container->showSiteContainerBottom();

function getReplyText($id) {
	
	$result = mysql_query("SELECT dateSent, fromUser, subject, body FROM messages WHERE id = '{$id}' AND toUser = '{$_SESSION['username']}'");
	$row = mysql_fetch_object($result);
	
	$subject = htmlentities($row->subject);
	$fromUser = htmlentities($row->fromUser);
	$dateSent = date("F j, Y g:i A", $row->dateSent);
	$body = "&#13;&#13;&#13;----\nFrom: $fromUser\nSent: $dateSent\nSubject: $subject\n\n" . htmlentities($row->body);
	
	return($body);
	
}

?>