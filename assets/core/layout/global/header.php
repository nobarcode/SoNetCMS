<?php

print $showAdminOptions;

print "<div id=\"header_container\">";

print "<div id=\"header_logo\">";
print "	<a href=\"/\"><img src=\"/assets/core/resources/images/logo.jpg\" border=\"0\"></a>";
print "</div>";

print "	<div id=\"search_bar\">";
print "		<form action=\"/documentSearch.php\" method=\"get\"><input type=\"text\" id=\"top_search\" name=\"top_search\" size=\"32\"> <input id=\"search_submit\" type=\"image\" src=\"/assets/core/resources/images/top_search_submit.gif\"></form>";
print "		<div id=\"other_searches\"><div class=\"people\"><a href=\"/profileSearch.php\">find people</a></div><div class=\"groups\"><a href=\"/groupSearch.php\">find groups</a></div></div>";
print "	</div>";

print "<div class=\"clear_both\"></div>\n";

print "<div id=\"menu_container\">\n";
print "	<div id=\"menu_inner\">\n";

$this->showMenu();

print "	</div>\n";
print "</div>\n";


print "</div>";

print "<div class=\"clear_both\"></div>\n";

$this->showToolbar();

print "<div class=\"clear_both\"></div>\n";
print "<div id=\"body_container\">\n";
print "	<div id=\"body_inner\">\n";

?>