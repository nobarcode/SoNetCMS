<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$jb = sanitize_string($_REQUEST['jb']);
$return_url = sanitize_string($_REQUEST['return_url']);

$jb = unsanitize_string($jb);
$jbHtml = htmlentities($jb);
$jb = urlencode($jb);
$return_url = htmlentities(unsanitize_string($return_url));

//read config file
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('displaySignUpAgreementLink') == 'true') {
	
	$signUpAgreementLink = "<a href=\"" . $config->readValue('signUpAgreementLinkUrl') . "\" onclick=\"window.open(this.href, 'ControlPanelOverview', 'resizable=yes,status=no,location=no,toolbar=yes,menubar=no,fullscreen=no,scrollbars=yes,dependent=no,width=900,height=767'); return false;\">" . $config->readValue('signUpAgreementLinkLabel') . "</a>";
	$agreementCheckbox = "\n<br/>\n<input type=\"checkbox\" id=\"terms\" name=\"terms\" value=\"1\"> I agree to $signUpAgreementLink.\n<br/>";
	
}

$showSignUpForm =<<< EOF
	<div id="sign_up_form">
		<form id="signUpForm" name="signUpForm" action="/processSignUp.php" method="post" enctype="multipart/form-data">
		Please provide your name:
		<br/><input type="text" id="name" name="name" style="width:230px">
		<br/>
		<br/>Enter your e-mail addresss:
		<br/><input type="text" id="email" name="email" style="width:230px">
		<br/>
		<br/>Confirm your e-mail address:
		<br/><input type="text" id="confirmEmail" name="confirmEmail" style="width:230px">
		<br/>
		<br/>Choose a Username:
		<br/><input type="text" id="username" name="username" style="width:230px">
		<br/>
		<br/>Enter a password:
		<br/><input type="password" id="password" name="password" style="width:230px">
		<br/>
		<br/>Confirm your password:
		<br/><input type="password" id="confirmPassword" name="confirmPassword" style="width:230px">
		<br/>
		<br/><table border="0" cellspacing="0" cellpadding="2" width="100%">
		<tr valign="center"><td colspan="2"><iframe id="captcha" src="captcha.php" width="100" height="28" noresize scrolling="no" frameborder="0" marginwidth="0" marginheight="0"></iframe><a href="javascript:reloadCaptcha();"><img style="margin-left:5px;" src="/assets/core/resources/images/reload_captcha.png" border="0" alt="Click here to get a different security code." title="Click here to get a different code."></a></td></tr>
		<tr valign="center"><td colspan="2"><input type="text" id="code" name="code" style="width:94px"> Enter the code.</td></tr>
		</table>$agreementCheckbox
		<br/><input type="submit" value="Sign me up!">
		<input type="hidden" id="jb" name="jb" value="$jbHtml">
		</form>
	</div>
EOF;

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/signUp.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/signUp.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("assets/core/layout/signup/layout_signup.php");

$site_container->showSiteContainerBottom();

?>