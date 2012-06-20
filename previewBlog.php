<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_component_loader.php");

if (trim($_SESSION['username']) == "") {
	
	exit;
	
}

class DynamicProperties { }
$row = new DynamicProperties;

$row->documentType = $_REQUEST['documentType'];
$row->category = $_REQUEST['category'];
$row->subcategory = $_REQUEST['subcategory'];
$row->subject = $_REQUEST['subject'];
$row->author = $_SESSION['username'];
$row->title = $_REQUEST['title'];
$row->rating = $_REQUEST['rating'];
$body = $_REQUEST['customHeader'];
$body .= $_REQUEST['htmlData'];

//create component loader class object and callback array that contains a reference to the object and the desired method
$componentLoader = new ComponentLoader();
$loadAttributes = array($componentLoader, 'loadAttributes');

//document attributes
$body = preg_replace_callback("/\[attribute type=\"(.*?)\"\]/i", $loadAttributes, $body);

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>

<style>
@import url("/assets/core/resources/css/main/global.css");
@import url("/assets/core/resources/css/main/showBlog.css");
@import url("/assets/core/resources/css/main/custom.css");
</style>

<title>PREVIEW</title>

</head>

<body>
<div style="width:100%; padding:10px 0px 10px 0px; text-align:center; font-weight:bold; color:#ffffff; background:#dd4a4a;">PREVIEW</div>
<div id="body_container">
	<div id="body_inner">
		<div id="left_column_container">
			<div class="document_body">
$body
			</div>
		</div>
		<div id="right_column_container">
			<div id="calendar_container"><table border="0" cellspacing="0" cellpadding="0" style="width:100%; height:249px; text-align:center; font-weight:bold; color:#ffffff; background:#dd4a4a;"><tr><td valign="center">CALENDAR</td></tr></table></div>
			<div id="blog_list"><table border="0" cellspacing="0" cellpadding="0" style="width:100%; height:249px; text-align:center; font-weight:bold; color:#ffffff; background:#dd4a4a;"><tr><td valign="center">BLOG LIST</td></tr></table></div>
		</div>
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