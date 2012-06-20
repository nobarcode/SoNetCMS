<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_site_container.php");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$type = sanitize_string($_REQUEST['type']);
$notification = $_SESSION['status'][0];
$object = $_SESSION['status'][1];
$owner = $_SESSION['status'][2];

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/status.css");
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, '');

$site_container->showSiteContainerTop();

include("assets/core/layout/status/layout_status.php");

$site_container->showSiteContainerBottom();

?>