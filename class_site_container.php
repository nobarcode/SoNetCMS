<?php

class SiteContainer {
	
	var $category;
	var $jb;
	
	function __construct($category, $jb) {
		
		$this->category = $category;
		$this->jb = $jb;
		
	}
	
	function showSiteHeader($showMetaData, $id, $_css_load, $_javascript_load) {
		
		print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n";
		print "\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";

		print "<html>\n";
		print "<head>\n";
		
		print "<style>\n";
		print "@import url(\"/assets/core/resources/css/main/global.css\");\n";
		print $_css_load . "\n";
		print "@import url(\"/assets/core/resources/css/main/custom.css\");\n";
		print "</style>\n";
		
		print "<script language=\"javascript\" src=\"/assets/core/resources/javascript/jquery.js\"></script>\n";
		print "<script language=\"javascript\" src=\"/assets/core/resources/javascript/jquery-ui.js\"></script>\n";
		print "<script language=\"javascript\" src=\"/assets/core/resources/javascript/superfish/js/superfish.js\"></script>\n";
		print $_javascript_load . "\n";

		if ($showMetaData === true) {
			
			print $this->showMetaData($id);
			
		} else {
			
			print "<title>" . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . "</title>\n";
			
		}
		
		print "</head>\n";
		print "<body>\n";
		
	}
	
	function showSiteContainerTop() {

		if (trim($_SESSION['username']) != "" && ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4)) {

			$showAdminOptions = "<div id=\"admin_options_container\"><div class=\"admin_options_menu\"><b>Logged in as:</b> ". $this->displayUserLevelType() . "<span class=\"admin_menu_item\"><a href=\"/controlPanel.php\">Control Panel</a></span><span class=\"admin_menu_item\"><a href=\"/adminSwapToStandard.php?jb=" . $GLOBALS['jb'] . "\">Visitor Mode</a></span></div></div><div class=\"clear_both\"></div>\n";

		}
		
		include("assets/core/layout/global/header.php");

	}
	
	function showMenu() {
		
		$result = mysql_query("SELECT * FROM categories ORDER BY weight ASC");
		
		$userGroupMenu = new CategoryUserGroupValidator();
		
		print "<ul id=\"menu\" class=\"menu\">";
		
		while ($row = mysql_fetch_object($result)) {
			
			$userGroupMenu->loadCategoryUserGroups(sanitize_string($row->category));
			
			if($userGroupMenu->allowRead()) {
				
				if ($row->hidden != 1) {
					
					$showCategory = htmlentities($row->category);
					$urlCategory = urlencode($row->category);
					
					if ($row->category != $this->category) {
						
						if ($row->useAlternateClass != 1) {
							
							$menuStyle = "menu_item";
							
						} else {
							
							$menuStyle = "menu_item_alternate";
							
						}
						
					} else {
						
						if ($row->useAlternateClass != 1) {
							
							$menuStyle = "menu_item_selected";
							
						} else {
							
							$menuStyle = "menu_item_alternate_selected";
							
						}
						
					}
					
					if (trim($row->flyoutContent) == "") {
						
						$flyoutContent = "";
						
					} else {
						
						$flyoutContent = "<ul><li>$row->flyoutContent</li></ul>";
						
					}
					
					print "			<li><div class=\"$menuStyle\"><a href=\"$row->defaultUrl\">$showCategory</a></div>$flyoutContent</li>\n";
					
				}
				
			}

		}
		
		print "</ul>";

	}
	
	function showToolbar() {

		if ($_SESSION['username'] == "") {

			if(trim($this->jb) != "") {

				print "<div id=\"toolbar\"><div id=\"toolbar_inner\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" height=\"16\"><tr valign=\"middle\"><td align=\"left\"><a href=\"/signIn.php?jb=$this->jb\">Sign in</a> or <a href=\"/signUp.php?jb=$this->jb\">Sign up!</a></td><td align=\"right\"><image id=\"loading\" style=\"float:right; margin:0px; display:none;\" src=\"/assets/core/resources/images/loading_small.gif\" border=\"0\"></td></tr></table></div></div>\n";

			} else {

				print "<div id=\"toolbar\"><div id=\"toolbar_inner\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" height=\"16\"><tr valign=\"middle\"><td align=\"left\"><a href=\"/signIn.php\">Sign in</a> or <a href=\"/signUp.php\">Sign up!</a></td><td align=\"right\"><image id=\"loading\" style=\"float:right; margin:0px; display:none;\" src=\"/assets/core/resources/images/loading_small.gif\" border=\"0\"></td></tr></table></div></div>\n";

			}

		} else {

			print "<div id=\"toolbar\"><div id=\"toolbar_inner\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" height=\"16\"><tr valign=\"middle\"><td align=\"left\"><div class=\"my_profile\"><a href=\"/profileEditor.php\">" . $_SESSION['username'] . "</a></div><div class=\"my_messages\">"  . $this->showMessageCount() . "</div><div class=\"my_friends\"><a href=\"/showMyFriends.php\">Friends</a></div><div class=\"my_groups\"><a href=\"/showMyGroups.php\">Groups</a></div><div class=\"my_images\"><a href=\"/showMyGalleryEditor.php\">Images</a></div><div class=\"my_blog\"><a href=\"/showMyBlog.php\">Blog</a></div><div class=\"my_signout\"><a href=\"/signOut.php\">Sign out</a></div></td><td align=\"right\"><image id=\"loading\" style=\"float:right; margin:0px; display:none;\" src=\"/assets/core/resources/images/loading_small.gif\" border=\"0\"></td></tr></table></div></div>\n";

		}

	}
	
	function showSiteContainerBottom() {

		print "	</div>\n";
		print "</div>\n";
		print "<div class=\"clear_both\"></div>\n";
				
		include("assets/core/layout/global/footer.php");
		
		print "</body>\n";
		print "</html>\n";

	}
	
	function showMetaData($id) {
		
		$query = "SELECT title, summary, keywords FROM documents WHERE id = '$id'";
		
		$result = mysql_query($query);
		$row = mysql_fetch_object($result);

		if(trim($row->summary) != "") {

			$summary = preg_replace("/\\n/", " ", $row->summary);
			$summary = htmlentities($summary);
			$return .= "<meta name=\"description\" content=\"$summary\"/>\n";

		}
		
		if(trim($row->keywords) != "") {

			$keywords = preg_replace("/\\n/", " ", $row->keywords);
			$keywords = htmlentities($keywords);
			$return .= "<meta name=\"keywords\" content=\"$keywords\"/>\n";

		}
		
		if(trim($row->title) != "") {

			$return .= "<title>" . htmlentities($row->title) . "</title>\n";

		} else {

			$return .= "<title>" . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . "</title>\n";

		}
		
		return($return);
		
	}

	function displayUserLevelType() {
		
		include("assets/core/config/part_user_levels.php");
		
		return($userLevelOptions[$_SESSION['userLevel']]);
		
	}
	
	function showMessageCount() {

		$result = mysql_query("SELECT id FROM messages WHERE toUser = '{$_SESSION['username']}' AND status = 'unread'");
		$count = mysql_num_rows($result);

		if ($count > 0) {
			
			if ($count < 2) {
				
				$messageText = "message";
				
			} else {
				
				$messageText = "messages";
				
			}
			
			$return = "<a href=\"/showMyMessages.php\"><span id=\"toolbar_unread_message_count\"><span style=\"font-weight:bold;\">$count unread $messageText</span></span></a>";

		} else {

			$return = "<a href=\"/showMyMessages.php\"><span id=\"toolbar_unread_message_count\">$count unread messages</span></a>";

		}

		return ($return);

	}
	
}

?>