<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$subject = sanitize_string($_REQUEST['subject']);
$category = sanitize_string($_REQUEST['category']);
$returnUrl = sanitize_string($_REQUEST['returnUrl']);

//read config file and determine if the contact form is enabled, if not then exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/scripts/contact/contact.properties');

if ($config->readValue('enableContactForm') != 'true') {
	
	exit;
	
}

if (trim($returnUrl) != "") {
	
	$cancelAction = unsanitize_string($returnUrl);
	
} else {
	
	$cancelAction = "/";
	
}

$subject = htmlentities(unsanitize_string($subject));
$htmlCategory = htmlentities(unsanitize_string($category));
$htmlReturnUrl = htmlentities(unsanitize_string($returnUrl));

$showCode .= "\n<tr valign=\"center\"><td colspan=\"2\"><iframe id=\"captcha\" src=\"captcha.php\" width=\"100\" height=\"28\" noresize scrolling=\"no\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\"></iframe><a href=\"javascript:reloadCaptcha();\"><img style=\"margin-left:5px;\" src=\"/assets/core/resources/images/reload_captcha.png\" border=\"0\" alt=\"Click here to get a different security code.\" title=\"Click here to get a different code.\"></a></td></tr>\n";
$showCode .= "<tr valign=\"center\"><td colspan=\"2\"><input type=\"text\" id=\"code\" name=\"code\" style=\"width:94px\"> Enter the code.</td></tr>\n";

$showContactForm =<<< EOF
			<div id="contact_input">
				<form id="compose_message" method="post" action="processContact.php">
					<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Name:</td><td width="100%"><input type="text" id="name" name="name" size="32" value=""></td></tr>
					<tr valign="center"><td nowrap>E-mail:</td><td width="100%"><input type="text" id="email" name="email" size="32" value=""></td></tr>
					<tr valign="center"><td nowrap>Phone:</td><td width="100%"><input type="text" id="phone" name="phone" size="32" value=""></td></tr>
					<tr valign="center"><td nowrap>Subject:</td><td width="100%"><input type="text" id="subject" name="subject" value="$subject" style="width:99%;"></td></tr>
					<tr valign="center"><td colspan="2"><textarea id="body" name="body" rows="16" style="width:99%"></textarea></td></tr>$showCode
					<tr valign="center"><td colspan="2"><input type="submit" id="submit" value="Send"> <input type="button" id="cancel" value="Cancel" onClick="window.location='$cancelAction';"></td></tr>
					<input type="hidden" id="category" name="category" value="$htmlCategory">
					<input type="hidden" id="returnUrl" name="returnUrl" value="$htmlReturnUrl">
					</table>
				</form>
			</div>
EOF;

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/contact.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/contact.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("assets/core/layout/contact/layout_contact.php");

$site_container->showSiteContainerBottom();

?>