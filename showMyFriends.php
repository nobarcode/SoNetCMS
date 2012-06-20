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

$result = mysql_query("SELECT owner FROM friends WHERE friend = '{$_SESSION['username']}' AND status = 'pending' ORDER BY weight");
$totalRows = mysql_num_rows($result);

$_css_load =<<< EOF
@import url("/assets/core/resources/css/main/showMyFriends.css");
EOF;

$_javascript_load =<<< EOF
<script language="javascript" src="/assets/core/resources/javascript/jquery-ui.js"></script>
<script language="javascript" src="/assets/core/resources/javascript/showMyFriends.js"></script>
<script language="javascript">
last_s = 0;
</script>
EOF;

$site_container = new SiteContainer($category, $jb);

$site_container->showSiteHeader(false, '', $_css_load, $_javascript_load);

$site_container->showSiteContainerTop();

print <<< EOF
			<div class="subheader_title">My Friends</div>
			<div id="message_box" style="display:none;" onClick="$(this).hide();"></div>
			<div id="friends_list_container"></div>
			<div id="editor_options">
				<a class="button" href="showMyPendingFriends.php" onclick="this.blur();"><span>$totalRows Pending Requests</span></a>
			</div>
EOF;

$site_container->showSiteContainerBottom();

?>