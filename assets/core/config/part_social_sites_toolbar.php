<?php

//define variables for web 2.0 links (digg, reddit & stumble)
$currentURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING'];
	
print "				<div class=\"toolbar\">\n";
print "					<div class=\"social_tools\"><a href=\"http://reddit.com/submit?url=$currentURL\" title=\"submit this to reddit.com\"><img src=\"/assets/core/resources/images/icon_reddit.gif\" border=\"0\"></a><a href=\"http://digg.com/submit?url=$currentURL&title=$urlTitle&bodytext=&media=news&topic=people\" title=\"submit this to digg.com\"><img src=\"/assets/core/resources/images/icon_digg.gif\" border=\"0\"></a><a href=\"http://www.stumbleupon.com/submit?url=$currentURL\" title=\"submit this to stumbleupon.com\"><img src=\"/assets/core/resources/images/icon_stumbleupon.gif\" border=\"0\"></a><a href=\"http://delicious.com/save?v=5&noui&jump=close&url=$currentURL&title=$urlTitle\" title=\"submit this to delicious.com\"><img src=\"/assets/core/resources/images/icon_delicious.gif\" border=\"0\"></a></div>";
print "				</div>\n";

?>