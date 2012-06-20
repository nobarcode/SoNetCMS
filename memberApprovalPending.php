<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//only display this page if the user just recently signed up
if ($_SESSION['sign_up_pending'] != true) {exit;}

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/memberApprovalPending.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/memberApprovalPending.js"></script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

include("assets/core/layout/memberapprovalpending/layout_memberapprovalpending.php");

$site_container->showSiteContainerBottom();

?>