<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$toUser = sanitize_string($_REQUEST['toUser']);
$reply = sanitize_string($_REQUEST['reply']);
$returnURL = sanitize_string($_REQUEST['returnURL']);

//if the reply id is set and not referencing an id that was sent to the currently logged in user, exit.
if (trim($reply) != "") {
	
	$result = mysql_query("SELECT dateSent, fromUser, subject, body, system FROM messages WHERE id = '{$reply}' AND toUser = '{$_SESSION['username']}'");
	$row = mysql_fetch_object($result);
	
	$formSubject = "Re: " . htmlentities($row->subject);
	$subject = htmlentities($row->subject);
	$toUser = htmlentities($row->fromUser);
	$dateSent = date("F j, Y g:i A", $row->dateSent);
	
	//remove all html tags etc (system messages such as comment notifications use html)
	$body = preg_replace("/<br>|<\/div>/", "\n", $row->body);
	$body = htmlentities(strip_tags($body));
	$body = "&#13;&#13;&#13;----\nFrom: $toUser\nSent: $dateSent\nSubject: $subject\n\n" . $body;
	
} else {
	
	$toUser = htmlentities(unsanitize_string($toUser));
	
}

$htmlReturnURL = htmlentities(unsanitize_string($returnURL));

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
			<div class="subheader_title">Compose Message</div>
			<div class="editor_box_container">	
				<form id="compose_message" method="post" action="processComposeMessage.php">
					<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>To:</td><td width="100%" align="left"><div class="to_user">$toUser</div></td></tr>
					<tr valign="center"><td nowrap>Subject:</td><td width="100%" align="left"><input type="text" id="subject" name="subject" value="$formSubject" style="width:99%;"></td></tr>
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

?>