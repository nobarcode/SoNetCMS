<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_jump_back.php");
include("part_session_check.php");
include("part_content_provider_check.php");
include("requestVariableSanitizer.inc");
include("class_config_reader.php");

$documentType = sanitize_string($_REQUEST['documentType']);

print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Template Browser</title>

<script language="javascript" src="/assets/core/resources/javascript/jquery.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/documentTemplateBrowser.js"></script>

<style>
@import url("/assets/core/resources/css/admin/documentTemplateBrowser.css");
</style>

</head>
<body>
	<div id="body_inner">
		<div id="template_list">
		</div>
	</div>
</body>
</html>
EOF;

?>