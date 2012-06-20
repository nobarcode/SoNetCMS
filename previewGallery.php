<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");

if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

$body = $_REQUEST['htmlData'];

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>

<style>
@import url("/assets/core/resources/css/main/global.css");
@import url("/assets/core/resources/css/main/showGallery.css");
@import url("/assets/core/resources/css/main/custom.css");
</style>

<title>PREVIEW</title>

</head>

<body>
<div style="width:100%; padding:10px 0px 10px 0px; text-align:center; font-weight:bold; color:#ffffff; background:#dd4a4a;">PREVIEW</div>
<div id="body_container">
	<div id="body_inner">
		<div id="main_content_container" style="margin-bottom:18px;">
			<div class="document_image_container" style="margin-bottom:10px;"><table border="0" cellspacing="0" cellpadding="0" style="width:100%; height:450px; text-align:center; font-weight:bold; color:#ffffff; background:#dd4a4a;"><tr><td valign="center">IMAGE</td></tr></table></div>
			<div class="document_body">
$body
			</div>
		</div>
		<div class="clear_both"></div>
		<table border="0" cellspacing="0" cellpadding="0" style="width:864px; height:158px; margin-left:auto; margin-right:auto; text-align:center; font-weight:bold; color:#ffffff; background:#dd4a4a;"><tr><td valign="center">THUMBNAIL IMAGES</td></tr></table>
	</div>
</div>
<div class="clear_both"></div>
<div id="footer_container">
	<div id="footer_inner">
		FOOTER
	</div>
</div>
</body>

</html>
EOF;

?>