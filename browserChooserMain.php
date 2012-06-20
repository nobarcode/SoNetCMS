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
</head>
<frameset rows="30,*" framespacing="0" frameborder="no" border="0" scrolling="no" noresize>
	<frame src="/browserChooser.php?CKEditor=$CKEditor&CKEditorFuncNum=$CKEditorFuncNum&langCode=$langCode" name="chooser" framespacing="0" frameborder="no" border="0" scrolling="no" noresize />
	<frame src="/selectDocument.php?CKEditor=$CKEditor&CKEditorFuncNum=$CKEditorFuncNum&langCode=$langCode" name="browser" frameborder="0" />
</frameset>
<noframes>
<body>
Your browser does not support frame-based websites. Please use Mozilla Firefox if you continue to experience viewing problems. We are working to resolve this issue.
</body>
</noframes>
</html>
EOF;

?>
