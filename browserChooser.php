<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");

$CKEditor = $_REQUEST['CKEditor'];
$CKEditorFuncNum = $_REQUEST['CKEditorFuncNum'];
$langCode = $_REQUEST['langCode'];
$externalCallback = $_REQUEST['externalCallback'];

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Content Browser</title>

<style>

body {
	
	margin:0;
	padding:5px;
	font-family:Arial;
	font-size:15px;
	color:#ffffff;
	background:#666666;
	
}

a {
	
	font-weight:bold;
	color:#ffffff;
	
}

</style>

</head>
<body>

<a href="selectDocument.php?CKEditor=$CKEditor&CKEditorFuncNum=$CKEditorFuncNum&langCode=$langCode&externalCallback=$externalCallback" target="browser">Documents</a> | <a href="/assets/core/resources/filemanager/index.html?CKEditor=$CKEditor&CKEditorFuncNum=$CKEditorFuncNum&langCode=$langCode&externalCallback=$externalCallback" target="browser">Files</a>

</body>
</html>
EOF;

?>