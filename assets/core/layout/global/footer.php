<?php

print "<div id=\"footer_container\">\n";
print "	<div id=\"footer_inner\">\n";
print "		&#169; " . date("Y", time()) . " " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " All rights reserved.\n";
print "	</div>\n";
print "</div>\n";

//creates the flyout menu
print "<script>$('ul#menu').superfish({delay: 1000});</script>\n";

?>