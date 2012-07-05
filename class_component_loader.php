<?php

require_once("class_category_user_group_validator.php");
require_once("class_group_membership_validator.php");

class ComponentLoader {
	
	function loadComponent($matches) {
		
		//get component ID
		$shortcut = $matches[1];
		
		//load component information
		$result = mysql_query("SELECT id, category, body FROM documents WHERE shortcut = '{$shortcut}' AND publishState = 'Published' AND component = '1' LIMIT 1");
		$row = mysql_fetch_object($result);
		
		$userGroup = new CategoryUserGroupValidator();
		$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
		
		if ((($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3) || ($_SESSION['userLevel'] == 4 && $publishState == "Unpublished")) && $userGroup->allowEditing()) {
			
			$componentMouseOver = " onMouseOver=\"$('#component-$shortcut').show();\" onMouseOut=\"$('#component-$shortcut').hide();\"";
			$componentEditOption .= "<div id=\"component-$shortcut\" class=\"component_edit\" style=\"display:none;\"><a class=\"button\" href=\"/documentEditor.php?id=$row->id&componentJb=" . $GLOBALS['jb'] . "\" onclick=\"this.blur();\"><span>Edit Component</span></a></div>\n";
			
		}
		
		$return .= "<div$componentMouseOver>";
		$return .= $row->body;
		$return .= "$componentEditOption</div>\n";
		$return .= "<div class=\"clear_both\"></div>\n";
		
		return($return);
		
	}
	
	function displayAuthenticatedContent($matches) {
		
		$content = $matches[1];
		
		if (trim($_SESSION['username']) != "") {
			
			$return = "$content";
			
		}
		
		return($return);
		
	}
	
	function displayGroupContent($matches) {
		
		$groups = $matches[1];
		$content = $matches[2];
		
		$keywords = preg_split("/[,;]\s*/", $groups);
		
		for ($x = 0; $x < count($keywords); $x++) {
			
			$result = mysql_query("SELECT userGroupsMembers.username FROM userGroups INNER JOIN userGroupsMembers ON userGroupsMembers.groupId = userGroups.id WHERE userGroupsMembers.username = '{$_SESSION['username']}' AND userGroups.name = '{$keywords[$x]}' AND userGroups.restrictViewing = '1'");
			
			if (mysql_num_rows($result) > 0) {
				
				return($content);
				
			}
			
		}
		
		return;
		
	}
	
	function loadDocumentRcComponent($matches) {
		
		$documentType = $matches[1];
		$category = $matches[2];
		$subcategory = $matches[3];
		$subject = $matches[4];
		$showTitle = $matches[5];
		$showAuthor = $matches[6];
		$showDate = $matches[7];
		$showSummary = $matches[8];
		$maxCharCount = $matches[9];
		$showSummaryLink = $matches[10];
		$showRatingGraphic = $matches[11];
		$showRatingText = $matches[12];
		$showVotes = $matches[13];
		$showScore = $matches[14];
		$showTotalComments = $matches[15];
		$organizedByParameter = $matches[16];
		$showImage = $matches[17];
		$imageWidth = $matches[18];
		$imageHeight = $matches[19];
		$maxDisplay = $matches[20];
		$startAt = $matches[21];
		$maxPerRow = $matches[22];
		$highlightCurrent = $matches[23];
		$skip = $matches[24];
		$noContent = $matches[25];
		
		include("assets/core/config/part_ratings.php");
		
		//date and time stuff
		$date = getdate();
		$month = $date['mon'];
		$day = $date['mday'];
		$year = $date['year'];
		
		$currentDate = date("Y-m-d H:i:s", time());
		$lastWeekMin = date("Y-m-d H:i:s", strtotime("-7 days"));
		$lastTwoWeeksMin = date("Y-m-d H:i:s", strtotime("-14 days"));
		$lastMonth = date("Y-m-d H:i:s", strtotime("-30 days"));
		
		$documentType = preg_replace('/\$_this/', $GLOBALS['documentType'], $documentType);
		$documentType = $this->multiselectSplitter('documents.documentType', $documentType);
		$category = preg_replace('/\$_this/', $GLOBALS['category'], $category);
		$category = $this->multiselectSplitter('documents.category', $category);
		$subcategory = preg_replace('/\$_this/', $GLOBALS['subcategory'], $subcategory);
		$subcategory = $this->multiselectSplitter('documents.subcategory', $subcategory);
		$subject = preg_replace('/\$_this/', $GLOBALS['subject'], $subject);
		$subject = $this->multiselectSplitter('documents.subject', $subject);
		
		if (trim($documentType) != "") {
			
			$restrictDocumentType = " AND " . $documentType;
			
		}
		
		if (trim($category) != "") {
			
			$restrictCategories .= " AND (";
			
			$restrictCategories .= $category;
			
			if (trim($subcategory) != "") {
				
				$restrictCategories .= " AND " . $subcategory;
				
				if (trim($subject) != "") {
					
					$restrictCategories .= " AND " . $subject;
					
				}
				
			}
			
			$restrictCategories .= ")";
			
		}
		
		if (trim($showDate) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showDate, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showDate = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$dateFormat = $dateOptions[2];
					
				} else {
					
					$dateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if ($showVotes == 'true') {
			
			$getVotes = ", (SELECT IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes FROM documentVotes WHERE documentVotes.parentId = documents.id AND documentVotes.type = 'document') AS totalVoteYes, (SELECT IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo FROM documentVotes WHERE documentVotes.parentId = documents.id AND documentVotes.type = 'document') AS totalVoteNo";
			
		}
		
		if ($showScore == 'true') {
			
			$getScore = ", (SELECT IFNULL(ROUND(IFNULL(SUM(documentVotes.voteYes),0) / COUNT(documentVotes.parentId) * 100, 1), 0) AS voteScore FROM documentVotes WHERE documentVotes.parentId = documents.id AND documentVotes.type = 'document') AS voteScore";
			
		}
		
		if ($showTotalComments == 'true') {
			
			$getTotalComments = ", (SELECT COUNT(parentId) FROM commentsDocuments WHERE commentsDocuments.parentId = documents.id AND commentsDocuments.type = 'documentComment') AS totalComments";
			
		}
		
		if (trim($organizedByParameter) != "") {
			
			//try to match for popular type (will have something like: popular,nn)
			preg_match("/(popular)\s*,\s*(\d+?)/i", $organizedByParameter, $popularOptions);
			
			if (count($popularOptions) > 0) {
				
				$organizedBy = $popularOptions[1];
				
				//if no request value was specified, give it a default value of 1
				if (trim($popularOptions[2]) != "") {
					
					$request = $popularOptions[2];
					
				} else {
					
					$request = 1;
					
				}
				
			} else {
				
				$organizedBy = $organizedByParameter;
				
			}
		
		//default to latest if no value was given for this parameter
		} else {
			
			$organizedBy = "latest";
			
		}
		
		if (trim($maxDisplay) == "") {
			
			$maxDisplay = "1";
			
		}
		
		if (trim($startAt) != "") {
			
			$limitStart = "$startAt, ";
			
		}
		
		if (trim($maxPerRow) == "") {
			
			$maxPerRow = "1";
			
		}
		
		if (trim($skip) != "") {	
			
			if (stristr($skip, '$_featured') !== false && $organizedByParameter != "featured") {
				
				$skipFeaturedJoin = " LEFT JOIN featuredDocuments ON documents.id = featuredDocuments.id AND featuredDocuments.activeState = 'Active' AND ((featuredDocuments.dateStarts <= '{$currentDate}' OR featuredDocuments.dateStarts = '0000-00-00 00:00:00') AND (featuredDocuments.dateExpires > '{$currentDate}' OR featuredDocuments.dateExpires = '0000-00-00 00:00:00'))";
				$skipFeaturedWhere = " AND featuredDocuments.id IS NULL";
				
			}
			
			if (stristr($skip, '$_focused') !== false && $organizedByParameter != "focused") {
				
				$skipFocusedJoin = " LEFT JOIN focusedDocuments ON  documents.id = focusedDocuments.id AND focusedDocuments.activeState = 'Active' AND ((focusedDocuments.dateStarts <= '{$currentDate}' OR focusedDocuments.dateStarts = '0000-00-00 00:00:00') AND (focusedDocuments.dateExpires > '{$currentDate}' OR focusedDocuments.dateExpires = '0000-00-00 00:00:00'))";
				$skipFocusedWhere = " AND focusedDocuments.id IS NULL";
				
			}
			
			if (stristr($skip, '$_this') !== false) {
				
				$skipThis = " AND documents.id != '" . $GLOBALS['id'] . "'";
				
			}
			
		}
		
		//create user groups validation object
		$userGroup = new CategoryUserGroupValidator();
		$excludeCategories = $userGroup->viewCategoryExclusionList('documents');
		
		if ($organizedBy == "popular") {
			
			//grab documents from just today
			$dateFilter = " AND (EXTRACT(YEAR FROM documents.datePublished) = '{$year}' AND EXTRACT(MONTH FROM documents.datePublished) = '{$month}' AND EXTRACT(DAY FROM documents.datePublished) = '{$day}')";
			$result = mysql_query("SELECT documents.id FROM documents INNER JOIN categories ON categories.category = documents.category LEFT JOIN documentVotes ON documentVotes.parentId = documents.id AND documentVotes.type = 'document'$skipFeaturedJoin$skipFocusedJoin WHERE documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$dateFilter$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere GROUP BY documents.id LIMIT $limitStart$request");
			
			if (mysql_num_rows($result) < $request) {
				
				//grab documents from the last 7 days
				$dateFilter = " AND (documents.datePublished >= '{$lastWeekMin}' AND documents.datePublished <= '{$currentDate}')";
				$result = mysql_query("SELECT documents.id FROM documents INNER JOIN categories ON categories.category = documents.category LEFT JOIN documentVotes ON documentVotes.parentId = documents.id AND documentVotes.type = 'document'$skipFeaturedJoin$skipFocusedJoin WHERE documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$dateFilter$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere GROUP BY documents.id LIMIT $limitStart$request");
				
				if (mysql_num_rows($result) < $request) {
				
					//grab documents from the last 14 days
					$dateFilter = " AND (documents.datePublished >= '{$lastTwoWeeksMin}' AND documents.datePublished <= '{$currentDate}')";
					$result = mysql_query("SELECT documents.id FROM documents INNER JOIN categories ON categories.category = documents.category LEFT JOIN documentVotes ON documentVotes.parentId = documents.id AND documentVotes.type = 'document'$skipFeaturedJoin$skipFocusedJoin WHERE documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$dateFilter$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere GROUP BY documents.id LIMIT $limitStart$request");
					
					if (mysql_num_rows($result) < $request) {
						
						//grab documents from the last 30 days
						$dateFilter = " AND (documents.datePublished >= '{$lastMonth}' AND documents.datePublished <= '{$currentDate}')";
						$result = mysql_query("SELECT documents.id FROM documents INNER JOIN categories ON categories.category = documents.category LEFT JOIN documentVotes ON documentVotes.parentId = documents.id AND documentVotes.type = 'document'$skipFeaturedJoin$skipFocusedJoin WHERE documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$dateFilter$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere GROUP BY documents.id LIMIT $limitStart$request");
						
						if (mysql_num_rows($result) < $request) {
							
							//grab documents from just this year
							$dateFilter = " AND (EXTRACT(YEAR FROM documents.datePublished) = '{$year}')";
							$result = mysql_query("SELECT documents.id FROM documents INNER JOIN categories ON categories.category = documents.category LEFT JOIN documentVotes ON documentVotes.parentId = documents.id AND documentVotes.type = 'document'$skipFeaturedJoin$skipFocusedJoin WHERE documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$dateFilter$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere GROUP BY documents.id LIMIT $limitStart$request");
							
							//if still nothing, then just grab from everything with no date restriction
							if (mysql_num_rows($result) < $request) {
								
								$dateFilter = "";
								
							}
							
						}
						
					}
					
				}
				
			}
			
			//do the same query as one of the above, but limit by $maxDisplay instead of $result
			$result = mysql_query("SELECT documents.id, documents.shortcut, documents.rating, documents.datePublished, documents.title, documents.author, documents.summaryImage, documents.summary, documents.summaryLinkText, (SELECT IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes FROM documentVotes WHERE documentVotes.parentId = documents.id AND documentVotes.type = 'document') AS totalVoteYes, (SELECT IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo FROM documentVotes WHERE documentVotes.parentId = documents.id AND documentVotes.type = 'document') AS totalVoteNo, (SELECT IFNULL(ROUND(IFNULL(SUM(documentVotes.voteYes),0) / COUNT(documentVotes.parentId) * 100, 1), 0) AS voteScore FROM documentVotes WHERE documentVotes.parentId = documents.id AND documentVotes.type = 'document') AS voteScore$getTotalComments, documents.showToolbar, documents.showComments FROM documents INNER JOIN categories ON categories.category = documents.category$skipFeaturedJoin$skipFocusedJoin WHERE documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$dateFilter$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere$skipThis GROUP BY documents.id ORDER BY voteScore DESC, totalVoteYes DESC, documents.hits DESC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "latest") {
			
			$result = mysql_query("SELECT documents.id, documents.shortcut, documents.rating, documents.datePublished, documents.title, documents.author, documents.summaryImage, documents.summary, documents.summaryLinkText$getVotes$getScore$getTotalComments, documents.showToolbar, documents.showComments FROM documents INNER JOIN categories ON categories.category = documents.category$skipFeaturedJoin$skipFocusedJoin WHERE documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere$skipThis ORDER BY documents.datePublished DESC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "featured") {
			
			$result = mysql_query("SELECT featuredDocuments.id, documents.shortcut, documents.rating, documents.datePublished, documents.title, documents.author, documents.summaryImage, documents.summary, documents.summaryLinkText$getVotes$getScore$getTotalComments, documents.showToolbar, documents.showComments FROM featuredDocuments INNER JOIN documents ON documents.id = featuredDocuments.id INNER JOIN categories ON categories.category = documents.category$skipFeaturedJoin$skipFocusedJoin WHERE featuredDocuments.activeState = 'Active' AND ((featuredDocuments.dateStarts <= '{$currentDate}' OR featuredDocuments.dateStarts = '0000-00-00 00:00:00') AND (featuredDocuments.dateExpires > '{$currentDate}' OR featuredDocuments.dateExpires = '0000-00-00 00:00:00')) AND documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$dateFilter$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere$skipThis ORDER BY featuredDocuments.weight ASC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "focused") {
			
			$result = mysql_query("SELECT focusedDocuments.id, documents.shortcut, documents.rating, documents.datePublished, documents.title, documents.author, documents.summaryImage, documents.summary, documents.summaryLinkText$getVotes$getScore$getTotalComments, documents.showToolbar, documents.showComments FROM focusedDocuments INNER JOIN documents ON documents.id = focusedDocuments.id INNER JOIN categories ON categories.category = documents.category$skipFeaturedJoin$skipFocusedJoin WHERE focusedDocuments.activeState = 'Active' AND ((focusedDocuments.dateStarts <= '{$currentDate}' OR focusedDocuments.dateStarts = '0000-00-00 00:00:00') AND (focusedDocuments.dateExpires > '{$currentDate}' OR focusedDocuments.dateExpires = '0000-00-00 00:00:00')) AND documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$dateFilter$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere$skipThis ORDER BY focusedDocuments.weight ASC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "accessed") {
			
			$result = mysql_query("SELECT documents.id, documents.shortcut, documents.rating, documents.datePublished, documents.title, documents.author, documents.summaryImage, documents.summary, documents.summaryLinkText$getVotes$getScore$getTotalComments, documents.showToolbar, documents.showComments FROM documents INNER JOIN categories ON categories.category = documents.category$skipFeaturedJoin$skipFocusedJoin WHERE documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$restrictDocumentType$restrictCategories$excludeCategories$skipFeaturedWhere$skipFocusedWhere$skipThis ORDER BY documents.lastAccess DESC LIMIT $limitStart$maxDisplay");
			
		}
		
		$total = mysql_num_rows($result);
		
		//if there are no results, return the noContent value
		if ($total == 0) {
			
			return("<div class=\"no_content\">$noContent</div>");
			
		}
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this itteration
			$x++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			if ($highlightCurrent == 'true') {
				
				if ($GLOBALS['id'] == $row->id) {
					
					$highlighter = " highlight";
					
				} else {
					
					$highlighter = "";
					
				}
				
			}
			
			if ($showImage == "true" && trim($row->summaryImage) != "") {
				
				if (trim($imageWidth) == "" && trim($imageHeight) == "") {
					
					$image = "	<div class=\"image\">\n<a href=\"/documents/open/$row->shortcut\"><img src=\"/file.php?load=$row->summaryImage&thumbs=true\" border=\"0\"></a>\n</div>\n";
					
				} else {
					
					$image = "	<div class=\"image\">\n<a href=\"/documents/open/$row->shortcut\"><img src=\"/file.php?load=$row->summaryImage&w=$imageWidth&h=$imageHeight\" border=\"0\"></a>\n</div>\n";
					
				}
				
				$imageOffset = " image_offset";
				
			} else {
				
				$image = "";
				$imageOffset = "";
				
			}
			
			$return .= "<div class=\"item$highlighter$separator\">\n";
			$return .= "$image";
			$return .= "	<div class=\"details$imageOffset\">\n";
			
			if ($showTitle == "true") {
				
				$return .= "		<div class=\"title\"><a href=\"/documents/open/$row->shortcut\">$row->title</a></div>\n";
				
			}
			
			if ($showAuthor == "true") {
				
				$return .= "		<div class=\"author\">$row->author</div>\n";
				
			}
			
			if ($showDate == "true") {
				
				$return .= "		<div class=\"date\">" . date("$dateFormat", strtotime($row->datePublished)) . "</div>\n";
				
			}
			
			if ($showSummary == "true") {
				
				if (trim($maxCharCount) != "" && (strlen($row->summary) > $maxCharCount)) {
					
					$summary = substr($row->summary, 0, strrpos(substr($row->summary, 0, $maxCharCount), ' ')) . '...';
					
				} else {
					
					$summary = $row->summary;
					
				}
				
				$summary = preg_replace("/\\n/", "<br>", htmlentities($summary));
				
				$return .= "		<div class=\"summary\">$summary</div>\n";
				
			}
			
			if ($showSummaryLink == "true") {
				
				$return .= "		<div class=\"link\"><a href=\"/documents/open/$row->shortcut\">$row->summaryLinkText</a></div>\n";
				
			}
			
			if (($showRatingGraphic == "true" || $showRatingText == "true") && trim($row->rating) != "") {
				
				$return .= "		<div class=\"component_rating_container\">\n";
				
				if ($showRatingGraphic == "true") {
					
					if (trim($row->rating) != "") {
						
						$return .= "		<div class=\"component_rating_graphic rating" . $row->rating . "\"></div>";
						
					}
					
				}
				
				if ($showRatingText == "true") {
					
					
					if (trim($row->rating) != "") {
						
						$return .= "		<div class=\"component_rating_text\">" . $ratingOptions[$row->rating] . "</div>";
						
					}
					
				}
				
				$return .= "		</div>\n";
				
			}
			
			if ($row->showToolbar == '1') {
				
				$return .= "		<div class=\"vote_bar\">";
				
				if ($showVotes == 'true') {
					
					$return .= "			<div class=\"votes\"><div class=\"yes\">$row->totalVoteYes</div><div class=\"no\">$row->totalVoteNo</div></div>\n";
					
				}
				
				if ($showScore == 'true') {
					
					$return .= "			<div class=\"score\">$row->voteScore%</div>\n";
					
				}
				
				$return .= "		</div>";
				
			}
			
			if ($showTotalComments == 'true' && $row->showComments == '1') {
				
				$return .= "		<div class=\"total_comments\">$row->totalComments</div>\n";
				
			}
			
			$return .= "	</div>\n";
			$return .= "</div>\n";
			
			//keep track of total displayed so far
			$count++;
			
			//insert row separator?
			if ($x == $maxPerRow && $count < $total) {

				$return .= "<div class=\"row_separator\"></div>\n";
				$x = 0;

			}

		}
		
		return($return);
		
	}
	
	function loadBlogRcComponent($matches) {
		
		$author = $matches[1];
		$documentType = $matches[2];
		$category = $matches[3];
		$subcategory = $matches[4];
		$subject = $matches[5];
		$showTitle = $matches[6];
		$showAuthor = $matches[7];
		$showDate = $matches[8];
		$showSummary = $matches[9];
		$maxCharCount = $matches[10];
		$showRatingGraphic = $matches[11];
		$showRatingText = $matches[12];
		$showVotes = $matches[13];
		$showScore = $matches[14];
		$showTotalComments = $matches[15];
		$organizedByParameter = $matches[16];
		$showImage = $matches[17];
		$imageWidth = $matches[18];
		$imageHeight = $matches[19];
		$maxDisplay = $matches[20];
		$startAt = $matches[21];
		$maxPerRow = $matches[22];
		$skip = $matches[23];
		$noContent = $matches[24];
		
		include("assets/core/config/part_ratings.php");
		
		if (trim($author) != "") {
			
			$restrictAuthor = " AND blogs.usernameCreated = '" . sanitize_string($author) . "'";
			
		}
		
		$documentType = preg_replace('/\$_this/', $GLOBALS['documentType'], $documentType);
		$documentType = $this->multiselectSplitter('blogs.documentType', $documentType);
		$category = preg_replace('/\$_this/', $GLOBALS['category'], $category);
		$category = $this->multiselectSplitter('blogs.category', $category);
		$subcategory = preg_replace('/\$_this/', $GLOBALS['subcategory'], $subcategory);
		$subcategory = $this->multiselectSplitter('blogs.subcategory', $subcategory);
		$subject = preg_replace('/\$_this/', $GLOBALS['subject'], $subject);
		$subject = $this->multiselectSplitter('blogs.subject', $subject);
		
		if (trim($documentType) != "") {
			
			$restrictDocumentType = " AND " . $documentType;
			
		}
		
		if (trim($category) != "") {
			
			$restrictCategories .= " AND (";
			
			$restrictCategories .= $category;
			
			if (trim($subcategory) != "") {
				
				$restrictCategories .= " AND " . $subcategory;
				
				if (trim($subject) != "") {
					
					$restrictCategories .= " AND " . $subject;
					
				}
				
			}
			
			$restrictCategories .= ")";
			
		}
		
		if (trim($showDate) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showDate, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showDate = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$dateFormat = $dateOptions[2];
					
				} else {
					
					$dateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if ($showVotes == 'true') {
			
			$getVotes = ", (SELECT IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes FROM documentVotes WHERE documentVotes.parentId = blogs.id AND documentVotes.type = 'blog') AS totalVoteYes, (SELECT IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo FROM documentVotes WHERE documentVotes.parentId = blogs.id AND documentVotes.type = 'blog') AS totalVoteNo";
			
		}
		
		if ($showScore == 'true') {
			
			$getScore = ", (SELECT IFNULL(ROUND(IFNULL(SUM(documentVotes.voteYes),0) / COUNT(documentVotes.parentId) * 100, 1), 0) AS voteScore FROM documentVotes WHERE documentVotes.parentId = blogs.id AND documentVotes.type = 'blog') AS voteScore";
			
		}
		
		if ($showTotalComments == 'true') {
			
			$getTotalComments = ", (SELECT COUNT(parentId) FROM commentsDocuments WHERE commentsDocuments.parentId = blogs.id AND commentsDocuments.type = 'blogComment') AS totalComments";
			
		}
		
		if (trim($organizedByParameter) != "") {
			
			//try to match for popular type (will have somethign like: popular,nn)
			preg_match("/(popular)\s*,\s*(\d+?)/i", $organizedByParameter, $popularOptions);
			
			if (count($popularOptions) > 0) {
				
				$organizedBy = $popularOptions[1];
				
				//if no request value was specified, give it a default value of 1
				if (trim($popularOptions[2]) != "") {
					
					$request = $popularOptions[2];
					
				} else {
					
					$request = 1;
					
				}
				
			} else {
				
				$organizedBy = $organizedByParameter;
				
			}
		
		//default to latest if no value was given for this parameter
		} else {
			
			$organizedBy = "latest";
			
		}
		
		if (trim($maxDisplay) == "") {
			
			$maxDisplay = "1";
			
		}
		
		if (trim($startAt) != "") {
			
			$limitStart = "$startAt, ";
			
		}
		
		if (trim($maxPerRow) == "") {
			
			$maxPerRow = "1";
			
		}
		
		if (trim($skip) != "") {	
			
			$skipBlogIds = $this->skipSplitter('blogs.id', $skip);
			
		}
		
		if ($organizedBy == "popular") {
			
			//date and time stuff
			$date = getdate();
			$month = $date['mon'];
			$day = $date['mday'];
			$dayOfWeek = $date['weekday'];
			$year = $date['year'];
			
			$currentDate = date("Y-m-d H:i:s", time());
			$lastWeekMin = date("Y-m-d H:i:s", strtotime("-7 days"));
			$lastTwoWeeksMin = date("Y-m-d H:i:s", strtotime("-14 days"));
			$lastMonth = date("Y-m-d H:i:s", strtotime("-30 days"));
			
			//create user groups validation object
			$userGroup = new CategoryUserGroupValidator();
			$excludeCategories = $userGroup->viewCategoryExclusionList('blogs');
			
			//grab documents from just today
			$dateFilter = " AND (EXTRACT(YEAR FROM blogs.dateCreated) = '{$year}' AND EXTRACT(MONTH FROM blogs.dateCreated) = '{$month}' AND EXTRACT(DAY FROM blogs.dateCreated) = '{$day}')";
			$result = mysql_query("SELECT blogs.id FROM blogs INNER JOIN categories ON categories.category = blogs.category LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.publishState = 'Published'$dateFilter$restrictAuthor$restrictDocumentType$restrictCategories$excludeCategories$skipBlogIds GROUP BY blogs.id LIMIT $limitStart$request");
			
			if (mysql_num_rows($result) < $request) {
				
				//grab documents from the last 7 days
				$dateFilter = " AND (blogs.dateCreated >= '{$lastWeekMin}' AND blogs.dateCreated <= '{$currentDate}')";
				$result = mysql_query("SELECT blogs.id FROM blogs INNER JOIN categories ON categories.category = blogs.category LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.publishState = 'Published'$dateFilter$restrictAuthor$restrictDocumentType$restrictCategories$excludeCategories$skipBlogIds GROUP BY blogs.id LIMIT $limitStart$request");
				
				if (mysql_num_rows($result) < $request) {
					
					//grab documents from the last 14 days
					$dateFilter = " AND (blogs.dateCreated >= '{$lastTwoWeeksMin}' AND blogs.dateCreated <= '{$currentDate}')";
					$result = mysql_query("SELECT blogs.id FROM blogs INNER JOIN categories ON categories.category = blogs.category LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.publishState = 'Published'$dateFilter$restrictAuthor$restrictDocumentType$restrictCategories$excludeCategories$skipBlogIds GROUP BY blogs.id LIMIT $limitStart$request");
					
					if (mysql_num_rows($result) < $request) {
						
						//grab documents from the last 30 days
						$dateFilter = " AND (blogs.dateCreated >= '{$lastMonth}' AND blogs.dateCreated <= '{$currentDate}')";
						$result = mysql_query("SELECT blogs.id FROM blogs INNER JOIN categories ON categories.category = blogs.category LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.publishState = 'Published'$dateFilter$restrictAuthor$restrictDocumentType$restrictCategories$excludeCategories$skipBlogIds GROUP BY blogs.id LIMIT $limitStart$request");
						
						if (mysql_num_rows($result) < $request) {
							
							//grab documents from just this year
							$dateFilter = " AND (EXTRACT(YEAR FROM blogs.dateCreated) = '{$year}')";
							$result = mysql_query("SELECT blogs.id FROM blogs INNER JOIN categories ON categories.category = blogs.category LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.publishState = 'Published'$dateFilter$restrictAuthor$restrictDocumentType$restrictCategories$excludeCategories$skipBlogIds GROUP BY blogs.id LIMIT $limitStart$request");
							
							//if still nothing, then just grab from everything with no date restriction
							if (mysql_num_rows($result) < $request) {
								
								$dateFilter = "";
								
							}
							
						}
						
					}
					
				}
				
			}
			
			//do the same query as one of the above, but limit by $maxDisplay instead of $result
			$result = mysql_query("SELECT blogs.id, blogs.rating, blogs.dateCreated, blogs.customHeader, blogs.title, blogs.usernameCreated, blogs.summaryImage, blogs.summary, (SELECT IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes FROM documentVotes WHERE documentVotes.parentId = blogs.id AND documentVotes.type = 'blog') AS totalVoteYes, (SELECT IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo FROM documentVotes WHERE documentVotes.parentId = blogs.id AND documentVotes.type = 'blog') AS totalVoteNo, (SELECT IFNULL(ROUND(IFNULL(SUM(documentVotes.voteYes),0) / COUNT(documentVotes.parentId) * 100, 1), 0) AS voteScore FROM documentVotes WHERE documentVotes.parentId = blogs.id AND documentVotes.type = 'blog') AS voteScore$getTotalComments$getTotalComments FROM blogs INNER JOIN categories ON categories.category = blogs.category WHERE blogs.publishState = 'Published'$dateFilter$restrictAuthor$restrictDocumentType$restrictCategories$excludeCategories$skipBlogIds GROUP BY blogs.id ORDER BY voteScore DESC, totalVoteYes DESC, blogs.hits DESC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "latest") {
			
			$result = mysql_query("SELECT blogs.id, blogs.rating, blogs.dateCreated, blogs.customHeader, blogs.title, blogs.usernameCreated, blogs.summaryImage, blogs.summary$getVotes$getScore$getTotalComments FROM blogs INNER JOIN categories ON categories.category = blogs.category WHERE blogs.publishState = 'Published'$restrictAuthor$restrictCategories$excludeCategories$skipBlogIds ORDER BY blogs.dateCreated DESC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "accessed") {
			
			$result = mysql_query("SELECT blogs.id, blogs.rating, blogs.dateCreated, blogs.customHeader, blogs.title, blogs.usernameCreated, blogs.summaryImage, blogs.summary$getVotes$getScore$getTotalComments FROM blogs INNER JOIN categories ON categories.category = blogs.category WHERE blogs.publishState = 'Published'$restrictAuthor$restrictCategories$excludeCategories$skipBlogIds ORDER BY blogs.lastAccess DESC LIMIT $limitStart$maxDisplay");
			
		}
		
		$total = mysql_num_rows($result);
		
		//if there are no results, return the noContent value
		if ($total == 0) {
			
			return("<div class=\"no_content\">$noContent</div>");
			
		}
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this itteration
			$x++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			if ($showImage == "true" && trim($row->summaryImage) != "") {
				
				if (trim($imageWidth) == "" && trim($imageHeight) == "") {
					
					$image = "	<div class=\"image\">\n<a href=\"/blogs/id/$row->id\" border=\"0\"><img src=\"/file.php?load=$row->summaryImage&thumbs=true\" border=\"0\"></a>\n</div>\n";
					
				} else {
					
					$image = "	<div class=\"image\">\n<a href=\"/blogs/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&w=$imageWidth&h=$imageHeight\" border=\"0\"></a>\n</div>\n";
					
				}
				
				$imageOffset = " image_offset";
				
			} else {
				
				$image = "";
				$imageOffset = "";
				
			}
			
			$return .= "<div class=\"item$separator\">\n";
			$return .= "$image";
			$return .= "	<div class=\"details$imageOffset\">\n";
			
			if ($showTitle == "true") {
				
				$return .= "		<div class=\"title\"><a href=\"/blogs/id/$row->id\">" . htmlentities($row->title) . "</a></div>\n";
				
			}
			
			if ($showAuthor == "true") {
				
				$return .= "		<div class=\"author\">$row->usernameCreated</div>\n";
				
			}
			
			if ($showDate == "true") {
				
				$return .= "		<div class=\"date\">" . date("$dateFormat", strtotime($row->dateCreated)) . "</div>\n";
				
			}
			
			if ($showSummary == "true") {
				
				if (trim($maxCharCount) != "" && (strlen($row->summary) > $maxCharCount)) {
					
					$summary = substr($row->summary, 0, strrpos(substr($row->summary, 0, $maxCharCount), ' ')) . '...';
					
				} else {
					
					$summary = $row->summary;
					
				}
				
				$summary = preg_replace("/\\n/", "<br>", htmlentities($summary));
				
				$return .= "		<div class=\"summary\">$summary</div>\n";
				
			}
			
			if (($showRatingGraphic == "true" || $showRatingText == "true") && trim($row->rating) != "") {
				
				$return .= "		<div class=\"component_rating_container\">\n";
				
				if ($showRatingGraphic == "true") {
					
					if (trim($row->rating) != "") {
						
						$return .= "		<div class=\"component_rating_graphic rating" . $row->rating . "\"></div>";
						
					}
					
				}
				
				if ($showRatingText == "true") {
					
					
					if (trim($row->rating) != "") {
						
						$return .= "		<div class=\"component_rating_text\">" . $ratingOptions[$row->rating] . "</div>";
						
					}
					
				}
				
				$return .= "		</div>\n";
				
			}
			
			if ($showVotes == 'true' || $showScore == 'true') {
				
				$return .= "		<div class=\"vote_bar\">";
				
				if ($showVotes == 'true') {
					
					$return .= "			<div class=\"votes\"><div class=\"yes\">$row->totalVoteYes</div><div class=\"no\">$row->totalVoteNo</div></div>\n";
					
				}
				
				if ($showScore == 'true') {
					
					$return .= "			<div class=\"score\">$row->voteScore%</div>\n";
					
				}
				
				$return .= "		</div>";
				
			}
			
			if ($showTotalComments == 'true') {
				
				$return .= "		<div class=\"total_comments\">$row->totalComments</div>\n";
				
			}
				
			$return .= "	</div>\n";
			$return .= "</div>\n";
			
			//keep track of total displayed so far
			$count++;
			
			//row separator
			if ($x == $maxPerRow && $count < $total) {
				
				$return .= "<div class=\"row_separator\"></div>\n";
				$x = 0;
				
			}
			
		}
		
		return($return);
		
	}
	
	function loadEventRcComponent($matches) {
		
		$category = $matches[1];
		$subcategory = $matches[2];
		$subject = $matches[3];
		$showTitle = $matches[4];
		$showDate = $matches[5];
		$showAuthor = $matches[6];
		$showSummary = $matches[7];
		$maxCharCount = $matches[8];
		$showImage = $matches[9];
		$imageWidth = $matches[10];
		$imageHeight = $matches[11];
		$showTotalComments = $matches[12];
		$organizedBy = $matches[13];
		$maxDisplay = $matches[14];
		$startAt = $matches[15];
		$maxPerRow = $matches[16];
		$noContent = $matches[17];
		
		$category = preg_replace('/\$_this/', $GLOBALS['category'], $category);
		$category = $this->multiselectSplitter('events.category', $category);
		$subcategory = preg_replace('/\$_this/', $GLOBALS['subcategory'], $subcategory);
		$subcategory = $this->multiselectSplitter('events.subcategory', $subcategory);
		$subject = preg_replace('/\$_this/', $GLOBALS['subject'], $subject);
		$subject = $this->multiselectSplitter('events.subject', $subject);
		
		if (trim($category) != "") {
			
			$restrictCategories .= " AND (";
			
			$restrictCategories .= $category;
			
			if (trim($subcategory) != "") {
				
				$restrictCategories .= " AND " . $subcategory;
				
				if (trim($subject) != "") {
					
					$restrictCategories .= " AND " . $subject;
					
				}
				
			}
			
			$restrictCategories .= ")";
			
		}
		
		if (trim($showDate) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showDate, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showDate = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$dateFormat = $dateOptions[2];
					
				} else {
					
					$dateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if ($showTotalComments == 'true') {
			
			$getTotalComments = ", (SELECT COUNT(parentId) FROM commentsDocuments WHERE commentsDocuments.parentId = events.id AND commentsDocuments.type = 'eventComment') AS totalComments";
			
		}
		
		if (trim($organizedBy) == "") {
			
			$organizedBy = "unexpired";
			
		}
		
		if (trim($maxDisplay) == "") {
			
			$maxDisplay = "1";
			
		}
		
		if (trim($startAt) != "") {
			
			$limitStart = "$startAt, ";
			
		}
		
		if (trim($maxPerRow) == "") {
			
			$maxPerRow = "1";
			
		}
		
		//create user groups validation object
		$userGroup = new CategoryUserGroupValidator();
		$excludeCategories = $userGroup->viewCategoryExclusionList('events');
		
		if ($organizedBy == "unexpired") {
			
			$todaysDate = getdate();
			
			$month = $todaysDate['mon'];
			$day = $todaysDate['mday'];
			$year = $todaysDate['year'];
			
			$getDate = $todaysDate['year'] . "-" . $todaysDate['mon'] . "-" . $todaysDate['mday'] . " 00:00:00";
			
			$result = mysql_query("SELECT events.id, events.usernameCreated, events.category, events.title, events.startDate, events.expireDate, events.summaryImage, events.summary, events.summaryLinkText$getTotalComments, events.showComments FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE events.startDate >= '{$getDate}' AND events.publishState = 'Published' $restrictCategories$excludeCategories AND ((events.groupId IS NOT NULL AND events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.groupId IS NULL OR (events.groupId IS NOT NULL AND events.private = '0'))) AND events.publishState = 'Published' GROUP BY events.id ORDER BY events.startDate ASC, events.title ASC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "accessed") {
			
			$result = mysql_query("SELECT events.id, events.usernameCreated, events.category, events.title, events.startDate, events.expireDate, events.summaryImage, events.summary, events.summaryLinkText$getTotalComments, events.showComments FROM events LEFT JOIN groupsMembers ON events.groupId = groupsMembers.parentId WHERE events.publishState = 'Published' $restrictCategories$excludeCategories AND ((events.groupId IS NOT NULL AND events.private = '1' AND groupsMembers.parentId = events.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (events.groupId IS NULL OR (events.groupId IS NOT NULL AND events.private = '0'))) AND events.publishState = 'Published' GROUP BY events.id ORDER BY events.lastAccess DESC LIMIT $limitStart$maxDisplay");
			
		}
		
		$total = mysql_num_rows($result);
		
		//if there are no results, return the noContent value
		if ($total == 0) {
			
			return("<div class=\"no_content\">$noContent</div>");
			
		}
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this itteration
			$x++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			if ($showImage == "true" && trim($row->summaryImage) != "") {
				
				if (trim($imageWidth) == "" && trim($imageHeight) == "") {
					
					$image = "	<div class=\"image\">\n<a href=\"/events/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&thumbs=true\" border=\"0\"></a>\n</div>\n";
					
				} else {
					
					$image = "	<div class=\"image\">\n<a href=\"/events/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&w=$imageWidth&h=$imageHeight\" border=\"0\"></a>\n</div>\n";
					
				}
				
				$imageOffset = " image_offset";
				
			} else {
				
				$image = "";
				$imageOffset = "";
				
			}
			
			$return .= "<div class=\"item$separator\">\n";
			$return .= "$image";
			$return .= "	<div class=\"details$imageOffset\">\n";
			
			if ($showTitle == "true") {
				
				$return .= "		<div class=\"title\"><a href=\"/events/id/$row->id\">" . htmlentities($row->title) . "</a></div>\n";
				
			}
			
			if ($showDate == "true") {
				
				$return .= "		<div class=\"date\"><div class=\"start\">" . date("$dateFormat", strtotime($row->startDate)) . "</div><div class=\"expire\">" . date("$dateFormat", strtotime($row->expireDate)) . "</div></div>\n";
				
			}
			
			if ($showAuthor == "true") {
				
				$return .= "		<div class=\"author\">$row->usernameCreated</div>\n";
				
			}
			
			if ($showSummary == "true") {
				
				if (trim($maxCharCount) != "" && (strlen($row->summary) > $maxCharCount)) {
					
					$summary = substr($row->summary, 0, strrpos(substr($row->summary, 0, $maxCharCount), ' ')) . '...';
					
				} else {
					
					$summary = $row->summary;
					
				}
				
				$summary = preg_replace("/\\n/", "<br>", htmlentities($summary));
				
				$return .= "		<div class=\"summary\">$summary</div>\n";
				
			}
			
			if ($showTotalComments == 'true' && $row->showComments == '1') {
				
				$return .= "		<div class=\"total_comments\">$row->totalComments</div>\n";
				
			}
			
			$return .= "	</div>\n";
			$return .= "</div>\n";
			
			//keep track of total displayed so far
			$count++;
			
			//row separator
			if ($x == $maxPerRow && $count < $total) {
				
				$return .= "<div class=\"row_separator\"></div>\n";
				$x = 0;
				
			}
			
		}
		
		return($return);
		
	}
	
	function loadAnnouncementRcComponent($matches) {
		
		$toggleId = $matches[1];
		$showDate = $matches[2];
		$showLink = $matches[3];
		$togglerStyle = $matches[4];
		$maxDisplay = $matches[5];
		$startAt = $matches[6];
		$maxPerRow = $matches[7];
		$noContent = $matches[8];
		
		if (trim($showDate) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showDate, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showDate = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$dateFormat = $dateOptions[2];
					
				} else {
					
					$dateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if (trim($maxDisplay) == "") {
			
			$maxDisplay = "1";
			
		}
		
		if (trim($startAt) != "") {
			
			$limitStart = "$startAt, ";
			
		}
		
		if (trim($maxPerRow) == "") {
			
			$maxPerRow = "1";
			
		}
		
		$time = getdate();
		$expireDate = $time['year'] . "-" . $time['mon'] . "-" . $time['mday'] . " " . $time['hours'] . ":" . $time['minutes'] . ":" . $time['seconds'];
		
		$result = mysql_query("SELECT *, announcements.dateCreated FROM announcements WHERE (announcements.dateExpires > '{$expireDate}' OR announcements.dateExpires = '0000-00-00 00:00:00') AND announcements.publishState = 'Published' ORDER BY announcements.dateCreated DESC LIMIT $limitStart$maxDisplay");
		$total = mysql_num_rows($result);
		
		//if there are no results, return the noContent value
		if ($total == 0) {
			
			return("<div class=\"no_content\">$noContent</div>");
			
		}
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this itteration
			$x++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			$return .= "<div class=\"item$separator\">\n";
			
			$return .= "	<div id=\"{$toggleId}_$count\" class=\"title\"><a href=\"javascript:toggler('{$toggleId}_$count', '{$toggleId}_{$count}_content', 'title', 'title_active', '$togglerStyle');\">$row->title</a></div>\n";
			$return .= "	<div id=\"{$toggleId}_{$count}_content\" class=\"details\" style=\"display:none;\">\n";
			
			if ($showDate == "true") {
				
				$return .= "		<div class=\"date\">" . date("$dateFormat", strtotime($row->dateCreated)) . "</div>\n";
				
			}
			
			$body = preg_replace("/\\n/", "<br>", $row->body);
			
			$return .= "<div class=\"body\">$body</div>\n";
			
			if ($showLink == "true" && (trim($row->linkUrl) != "" && trim($row->linkText) != "")) {
				
				$return .= "		<div class=\"link\"><a href=\"$row->linkUrl\">$row->linkText</a></div>\n";
				
			}
			
			$return .= "	</div>\n";
			$return .= "</div>\n";
			
			//keep track of total displayed so far
			$count++;
			
			//row separator
			if ($x == $maxPerRow && $count < $total) {
				
				$return .= "<div class=\"row_separator\"></div>\n";
				$x = 0;
				
			}
			
		}
		
		return($return);
		
	}
	
	function loadConversationRcComponent($matches) {
		
		$showTitle = $matches[1];
		$showDate = $matches[2];
		$showAuthor = $matches[3];
		$showBody = $matches[4];
		$maxCharCount = $matches[5];
		$maxDisplay = $matches[6];
		$startAt = $matches[7];
		$maxPerRow = $matches[8];
		$noContent = $matches[9];
		
		//load site group validator
		$groupValidator = new GroupValidator();
		
		if (trim($showDate) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showDate, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showDate = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$dateFormat = $dateOptions[2];
					
				} else {
					
					$dateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if (trim($maxDisplay) == "") {
			
			$maxDisplay = "1";
			
		}
		
		if (trim($startAt) != "") {
			
			$limitStart = "$startAt, ";
			
		}
		
		if (trim($maxPerRow) == "") {
			
			$maxPerRow = "1";
			
		}
		
		$result = mysql_query("SELECT DISTINCT conversationsPosts.parentId, conversationsPosts.id, conversationsPosts.body, conversationsPosts.author, conversationsPosts.dateCreated, conversations.title, conversations.restricted FROM conversationsPosts INNER JOIN conversations ON conversations.id = conversationsPosts.parentId INNER JOIN groups ON groups.id = conversations.groupId LEFT JOIN groupsMembers ON conversations.groupId = groupsMembers.parentId WHERE conversationsPosts.id = (SELECT MAX(conversationsPosts.id) FROM conversationsPosts WHERE conversationsPosts.parentId = conversations.id) AND conversationsPosts.parentId = conversations.id AND ((conversations.restricted = '1' AND groupsMembers.parentId = conversations.groupId AND groupsMembers.username = '{$_SESSION['username']}' AND groupsMembers.status = 'approved') OR (conversations.restricted = '0')) ORDER BY conversationsPosts.dateCreated DESC LIMIT $limitStart$maxDisplay");
		$total = mysql_num_rows($result);
		
		//if there are no results, return the noContent value
		if ($total == 0) {
			
			return("<div class=\"no_content\">$noContent</div>");
			
		}
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		$bbcode = new ProcessBbcode();
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this itteration
			$x++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $row->restricted != 1 || ($row->restricted == 1 && $groupValidator->isGroupMember($row->groupId, $_SESSION['username']))) {
				
				$return .= "<div class=\"item$separator\">\n";
				$return .= "	<div class=\"details\">\n";
				
				if ($showTitle == "true") {
					
					$return .= "		<div class=\"title\"><a href=\"/showGroupConversation.php?parentId=$row->parentId&findId=$row->id\">" . htmlentities($row->title) . "</a></div>\n";
					
				}
				
				if ($showDate == "true") {
					
					$return .= "		<div class=\"date\">" . date("$dateFormat", strtotime($row->dateCreated)) . "</div>\n";
					
				}
				
				if ($showAuthor == "true") {
					
					$return .= "		<div class=\"author\">$row->author</div>\n";
					
				}
				
				if ($showBody == "true") {
					
					$body = $bbcode->strip($row->body);
					
					//clear any extra line breaks at the beginning
					$body = preg_replace("/^[\s]*/", "", $body);
					
					if (trim($maxCharCount) != "" && (strlen($body) > $maxCharCount)) {
						
						$body = substr($body, 0, strrpos(substr($body, 0, $maxCharCount), ' ')) . '...';
						
					}
					
					$return .= "		<div class=\"summary\">$body</div>\n";
					
				}
				
				$return .= "	</div>\n";
				$return .= "</div>\n";
				
				//keep track of total displayed so far
				$count++;
				
				//row separator
				if ($x == $maxPerRow && $count < $total) {
					
					$return .= "<div class=\"row_separator\"></div>\n";
					$x = 0;
					
				}
				
			}
			
		}
		
		return($return);
		
	}
	
	function loadMemberRcComponent($matches) {
		
		$showImage = $matches[1];
		$imageWidth = $matches[2];
		$imageHeight = $matches[3];
		$showOnlineNow = $matches[4];
		$onlineLabel = $matches[5];
		$offlineLabel = $matches[6];
		$showUsername = $matches[7];
		$showLastActive = $matches[8];
		$lastActiveLabel = $matches[9];
		$showLastLogin = $matches[10];
		$lastLoginLabel = $matches[11];
		$showMemberSince = $matches[12];
		$memberSinceLabel = $matches[13];
		$hasImage = $matches[14];
		$noImageUrl = $matches[15];
		$organizedBy = $matches[16];
		$maxDisplay = $matches[17];
		$startAt = $matches[18];
		$maxPerRow = $matches[19];
		$separatorCharacter = $matches[20];
		$noContent = $matches[21];
		
		if (trim($showLastActive) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showLastActive, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showLastActive = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$lastActiveDateFormat = $dateOptions[2];
					
				} else {
					
					$lastActiveDateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if (trim($showLastLogin) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showLastLogin, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showLastLogin = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$lastLoginDateFormat = $dateOptions[2];
					
				} else {
					
					$lastLoginDateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if (trim($showMemberSince) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showMemberSince, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showMemberSince = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$memberSinceDateFormat = $dateOptions[2];
					
				} else {
					
					$memberSinceDateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if (trim($organizedBy) == "") {
			
			$organizedBy = "active";
			
		}
		
		if ($hasImage == "true") {
			
			$filterByImage = " AND imageUrl != ''";
			
		}
		
		if (trim($maxDisplay) == "") {
			
			$maxDisplay = "1";
			
		}
		
		if (trim($startAt) != "") {
			
			$limitStart = "$startAt, ";
			
		}
		
		if (trim($maxPerRow) == "") {
			
			$maxPerRow = "1";
			
		}
		
		if ($organizedBy == "active") {
			
			$result = mysql_query("SELECT users.username, users.dateCreated, users.lastLogin, users.lastActive, users.signedOut, users.imageUrl FROM users WHERE status != 'pending'$filterByImage ORDER BY lastActive DESC, lastLogin DESC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "loggedIn") {
			
			$result = mysql_query("SELECT users.username, users.dateCreated, users.lastLogin, users.lastActive, users.signedOut, users.imageUrl FROM users WHERE status != 'pending'$filterByImage ORDER BY lastLogin DESC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "signedUp") {
			
			$result = mysql_query("SELECT users.username, users.dateCreated, users.lastLogin, users.lastActive, users.signedOut, users.imageUrl FROM users WHERE status != 'pending'$filterByImage ORDER BY dateCreated DESC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "accessed") {
			
			$result = mysql_query("SELECT users.username, users.dateCreated, users.lastLogin, users.lastActive, users.signedOut, users.imageUrl FROM users WHERE status != 'pending'$filterByImage ORDER BY lastAccess DESC LIMIT $limitStart$maxDisplay");
			
		} elseif ($organizedBy == "random") {
			
			$result = mysql_query("SELECT * FROM (SELECT @cnt := COUNT(*) + 1, @lim := $maxDisplay FROM users) vars STRAIGHT_JOIN (SELECT users.username, users.dateCreated, users.lastLogin, users.lastActive, users.signedOut, users.imageUrl, @lim := @lim - 1 FROM users WHERE status != 'pending'$filterByImage AND (@cnt := @cnt - 1) AND RAND() < @lim / @cnt) i LIMIT $limitStart$maxDisplay");
			
		}
		
		$total = mysql_num_rows($result);
		
		//if there are no results, return the noContent value
		if ($total == 0) {
			
			return("<div class=\"no_content\">$noContent</div>");
			
		}
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		//last active time range
		$lastActiveTime = time() - (60 * 20);
		
		$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this row's column itteration
			$x++;
			
			//keep track of total displayed so far
			$count++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			if ($showImage == "true") {
				
				if (trim($noImageUrl) != "") {
					
					$showNoImage = "<img src=\"$noImageUrl\" border=\"0\">";
					
				}
				
				//if height and width are not specified, use the filemanager thumbnails
				if (trim($imageWidth) == "" && trim($imageHeight) == "") {
					
					if(is_file($script_directory . $row->imageUrl)) {
						
						$image = "	<div class=\"image\" onClick=\"window.location='/showProfile.php?username=$row->username'\"><img src=\"/file.php?load=$row->imageUrl&thumbs=true\" border=\"0\"></div>\n";
						
					} else {
						
						$image = "	<div class=\"no_image\" onClick=\"window.location='/showProfile.php?username=$row->username'\">$showNoImage</div>\n";
						
					}
					
				} else {
					
					if(is_file($script_directory . $row->imageUrl)) {
						
						$image = "	<div class=\"image\" onClick=\"window.location='/showProfile.php?username=$row->username'\"><img src=\"/file.php?load=$row->imageUrl&w=$imageWidth&h=$imageHeight\" border=\"0\"></div>\n";
						
					} else {
						
						$image = "	<div class=\"no_image\" onClick=\"window.location='/showProfile.php?username=$row->username'\">$showNoImage</div>\n";
						
					}
					
				}
					
				$imageOffset = " image_offset";
				
			} else {
					
				$image = "";
				$imageOffset = "";
				
			}
			
			$timeTest = strtotime($row->lastActive);
			
			$return .= "<div class=\"item$separator\">\n";
			$return .= $image;
			$return .= "	<div class=\"details$imageOffset\">\n";
			
			//check to see if the user has had any activity (regsitered using the part_sesssion.php file) within the last 20 minutes
			if ($showOnlineNow == "true" && (strtotime($row->lastActive) >= $lastActiveTime)) {
				
				$return .= "	 <div class=\"online\">$onlineLabel</div>";
				
			} elseif ($showOnlineNow == "true" && (strtotime($row->lastActive) < $lastActiveTime)) {
				
				$return .= "		<div class=\"offline\">$offlineLabel</div>";
				
			}
			
			if ($showUsername == "true") {
				
				$return .= "		<div class=\"username\"><a href=\"/showProfile.php?username=$row->username\">$row->username</a></div>\n";
				
			}
			
			if ($showLastActive == "true") {
				
				if ($row->lastActive > 0) {
					
					$lastActiveDate = strtotime($row->lastActive);
					
				} else {
					
					$lastActiveDate = strtotime($row->signedOut);
					
				}
				
				$return .= "		<div class=\"last_active\"><div class=\"label\">$lastActiveLabel</div>" . date("$lastActiveDateFormat", $lastActiveDate) ."</div>\n";
				
			}
			
			if ($showLastLogin == "true") {
				
				$return .= "		<div class=\"last_login\"><div class=\"label\">$lastLoginLabel</div>" . date("$lastLoginDateFormat", strtotime($row->lastLogin)) ."</div>\n";
				
			}
			
			if ($showMemberSince == "true") {
				
				$return .= "		<div class=\"member_since\"><div class=\"label\">$memberSinceLabel</div>" . date("$memberSinceDateFormat", strtotime($row->dateCreated)) . "</div>\n";
				
			}
			
			$return .= "	</div>\n";
			
			if (trim($separatorCharacter) != "" && $count < $total) {
				
				$return .= "	<div class=\"separator_character\">$separatorCharacter</div>\n";
				
			}
			
			$return .= "</div>\n";
			
			//row separator
			if ($x == $maxPerRow && $count < $total) {
				
				$return .= "			<div class=\"row_separator\"></div>\n";
				$x = 0;
				
			}
			
		}
		
		return($return);
		
	}
	
	function loadCommentRcComponent($matches) {
		
		$category = $matches[1];
		$subcategory = $matches[2];
		$subject = $matches[3];
		$showTitle = $matches[4];
		$showDate = $matches[5];
		$showUsername = $matches[6];
		$showBody = $matches[7];
		$maxCharCount = $matches[8];
		$showVotes = $matches[9];
		$showScore = $matches[10];
		$organizedBy = $matches[11];
		$maxDisplay = $matches[12];
		$startAt = $matches[13];
		$maxPerRow = $matches[14];
		$noContent = $matches[15];
		
		if ($category == '$_this') {
			
			$category = $GLOBALS['category'];
			
		}
		
		if ($subcategory == '$_this') {
			
			$subcategory = $GLOBALS['subcategory'];
			
		}
		
		if ($subject == '$_this') {
			
			$subject = $GLOBALS['subject'];
			
		}
		
		if (trim($category) != "") {
			
			$restrictCategories .= " AND (";
			
			$restrictCategories .= "category = '" . sanitize_string($category) . "'";
			
			if (trim($subcategory) != "") {
				
				$restrictCategories .= " AND subcategory = '" . sanitize_string($subcategory) . "'";
				
				if (trim($subject) != "") {
					
					$restrictCategories .= " AND subject = '" . sanitize_string($subject) . "'";
					
				}
				
			}
			
			$restrictCategories .= ")";
			
		}
		
		if (trim($showDate) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showDate, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showDate = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$dateFormat = $dateOptions[2];
					
				} else {
					
					$dateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if ($showVotes == 'true') {
			
			$getVotesDocuments = ", (SELECT IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'documentComment') AS totalVoteYes, (SELECT IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'documentComment') AS totalVoteNo";
			$getVotesBlogs = ", (SELECT IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'blogComment') AS totalVoteYes, (SELECT IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'blogComment') AS totalVoteNo";
			$getVotesEvents = ", (SELECT IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment') AS totalVoteYes, (SELECT IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment') AS totalVoteNo";
			$getVotesDocumentImages = ", (SELECT IFNULL(SUM(documentVotes.voteYes),0) AS totalVoteYes FROM documentVotes WHERE documentVotes.parentId = commentsImages.id AND documentVotes.type = 'documentImageComment') AS totalVoteYes, (SELECT IFNULL(SUM(documentVotes.voteNo),0) AS totalVoteNo FROM documentVotes WHERE documentVotes.parentId = commentsImages.id AND documentVotes.type = 'documentImageComment') AS totalVoteNo";
			
		}
		
		if ($showScore == 'true') {
			
			$getScoreDocuments = ", (SELECT IFNULL(ROUND(IFNULL(SUM(documentVotes.voteYes),0) / COUNT(documentVotes.parentId) * 100, 1), 0) AS voteScore FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'documentComment') AS voteScore";
			$getScoreBlogs = ", (SELECT IFNULL(ROUND(IFNULL(SUM(documentVotes.voteYes),0) / COUNT(documentVotes.parentId) * 100, 1), 0) AS voteScore FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'blogComment') AS voteScore";
			$getScoreEvents = ", (SELECT IFNULL(ROUND(IFNULL(SUM(documentVotes.voteYes),0) / COUNT(documentVotes.parentId) * 100, 1), 0) AS voteScore FROM documentVotes WHERE documentVotes.parentId = commentsDocuments.id AND documentVotes.type = 'eventComment') AS voteScore";
			$getScoreDocumentImages = ", (SELECT IFNULL(ROUND(IFNULL(SUM(documentVotes.voteYes),0) / COUNT(documentVotes.parentId) * 100, 1), 0) AS voteScore FROM documentVotes WHERE documentVotes.parentId = commentsImages.id AND documentVotes.type = 'documentImageComment') AS voteScore";
			
		}
		
		if ($organizedBy == "latest") {
			
			$orderBy = "dateCreated DESC";
			
		} elseif ($organizedBy == "oldest") {
			
			$orderBy = "dateCreated ASC";
			
		} elseif ($organizedBy == "popular") {
			
			$orderBy = "voteScore DESC, totalVoteYes DESC, totalVoteNo ASC";
			
		} elseif ($organizedBy == "unpopular") {
			
			$orderBy = "voteScore ASC, totalVoteNo DESC, documentVotes.voteYes ASC";
			
		} else {
			
			$orderBy = "dateCreated ASC";
			
		}
		
		if (trim($maxDisplay) == "") {
			
			$maxDisplay = "1";
			
		}
		
		if (trim($startAt) != "") {
			
			$limitStart = "$startAt, ";
			
		}
		
		if (trim($maxPerRow) == "") {
			
			$maxPerRow = "1";
			
		}
		
		//create user groups validation object
		$userGroup = new CategoryUserGroupValidator();
		$excludeDocumentCategories = $userGroup->viewCategoryExclusionList('documents');
		$excludeBlogCategories = $userGroup->viewCategoryExclusionList('blogs');
		$excludeEventCategories = $userGroup->viewCategoryExclusionList('events');
		
		$result = mysql_query("SELECT commentsDocuments.parentId, commentsDocuments.username, commentsDocuments.dateCreated, commentsDocuments.body, commentsDocuments.type, documents.shortcut, documents.title$getVotesDocuments$getScoreDocuments FROM commentsDocuments INNER JOIN documents ON documents.id = commentsDocuments.parentId WHERE commentsDocuments.type = 'documentComment' AND documents.publishState = 'Published' AND documents.doNotSyndicate != '1' AND documents.component != '1'$restrictCategories$excludeDocumentCategories UNION ALL SELECT commentsDocuments.parentId, commentsDocuments.username, commentsDocuments.dateCreated, commentsDocuments.body, commentsDocuments.type, blogs.id, blogs.title$getVotesBlogs$getScoreBlogs FROM commentsDocuments INNER JOIN blogs ON blogs.id = commentsDocuments.parentId WHERE commentsDocuments.type = 'blogComment' AND blogs.publishState = 'Published'$restrictBlogCategories$excludeBlogCategories UNION ALL SELECT commentsDocuments.parentId, commentsDocuments.username, commentsDocuments.dateCreated, commentsDocuments.body, commentsDocuments.type, events.id, events.title$getVotesEvents$getScoreEvents FROM commentsDocuments INNER JOIN events ON events.id = commentsDocuments.parentId WHERE commentsDocuments.type = 'eventComment' AND events.publishState = 'Published'$restrictEventCategories$excludeEventCategories UNION ALL SELECT commentsImages.parentId, commentsImages.username, commentsImages.dateCreated, commentsImages.body, commentsImages.type, documents.shortcut, documents.title$getVotesDocumentImages$getScoreDocumentImages FROM commentsImages INNER JOIN documents ON documents.id = commentsImages.parentId WHERE commentsImages.type = 'documentImageComment' $restrictDocumentCategories$excludeDocumentCategories AND documents.publishState = 'Published' ORDER BY $orderBy LIMIT $maxDisplay");
		
		$total = mysql_num_rows($result);
		
		//if there are no results, return the noContent value
		if ($total == 0) {
			
			return("<div class=\"no_content\">$noContent</div>");
			
		}
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this itteration
			$x++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			if ($row->type == "documentComment") {
				
				$linkTo = "/documents/open/$row->shortcut";
				
			} elseif ($row->type == "blogComment") {
				
				$linkTo = "/blogs/id/$row->parentId";
				
			} elseif ($row->type == "documentImageComment") {
				
				$linkTo = "/galleries/id/$row->parentId";
				
			} elseif ($row->type == "eventComment") {
				
				$linkTo = "/events/id/$row->parentId";
				
			}
			
			$return .= "<div class=\"item$separator\">\n";
			$return .= "	<div class=\"details\">\n";
			
			if ($showTitle == "true") {
				
				$return .= "		<div class=\"title\"><a href=\"$linkTo\">" . htmlentities($row->title) . "</a></div>\n";
				
			}
			
			if ($showDate == "true") {
				
				$return .= "		<div class=\"date\">" . date("$dateFormat", strtotime($row->dateCreated)) . "</div>\n";
				
			}
			
			if ($showUsername == "true") {
				
				$return .= "		<div class=\"username\">$row->username</div>\n";
				
			}
			
			if ($showBody == "true") {
				
				if (trim($maxCharCount) != "" && (strlen($row->body) > $maxCharCount)) {
					
					$body = substr($row->body, 0, strrpos(substr($row->body, 0, $maxCharCount), ' ')) . '...';
					
				} else {
					
					$body = $row->body;
					
				}
				
				$body = preg_replace("/\\n/", "<br>", htmlentities($body));
				
				$return .= "		<div class=\"body\">$body</div>\n";
				
			}
			
			if ($showVotes == 'true' || $showScore == 'true') {
				
				$return .= "		<div class=\"vote_bar\">";
				
				if ($showVotes == 'true') {
					
					$return .= "			<div class=\"votes\"><div class=\"yes\">$row->totalVoteYes</div><div class=\"no\">$row->totalVoteNo</div></div>\n";
					
				}
				
				if ($showScore == 'true') {
					
					$return .= "			<div class=\"score\">$row->voteScore%</div>\n";
					
				}
				
				$return .= "		</div>";
				
			}
			
			$return .= "	</div>\n";
			$return .= "</div>\n";
			
			//keep track of total displayed so far
			$count++;
			
			//row separator
			if ($x == $maxPerRow && $count < $total) {
				
				$return .= "<div class=\"row_separator\"></div>\n";
				$x = 0;
				
			}
			
		}
		
		return($return);
		
	}
	
	function loadGroupRcComponent($matches) {
		
		$showName = $matches[1];
		$showOwner = $matches[2];
		$showDate = $matches[3];
		$showAbout = $matches[4];
		$maxCharCount = $matches[5];
		$showLabels = $matches[6];
		$showTotalMembers = $matches[7];
		$showTotalConversations = $matches[8];
		$showTotalEvents = $matches[9];
		$showImage = $matches[10];
		$imageWidth = $matches[11];
		$imageHeight = $matches[12];
		$organizedBy = $matches[13];
		$maxDisplay = $matches[14];
		$startAt = $matches[15];
		$maxPerRow = $matches[16];
		$noContent = $matches[17];
		
		if (trim($showDate) != "") {
			
			//try to match for date type (will have something like: true, %M %d, %Y %h:%i %p)
			preg_match("/(true)\s*,?\s*([^.]+)?/i", $showDate, $dateOptions);
			
			if (count($dateOptions) > 0) {
				
				$showDate = $dateOptions[1];
				
				//if no format value was specified, give it a default value
				if (trim($dateOptions[2]) != "") {
					
					$dateFormat = $dateOptions[2];
					
				} else {
					
					$dateFormat = "F jS, Y h:i A";
					
				}
				
			}
			
		}
		
		if ($organizedBy == "latest") {
			
			$orderBy = "groups.dateCreated DESC";
			
		} elseif ($organizedBy == "oldest") {
			
			$orderBy = "groups.dateCreated ASC";
			
		} elseif ($organizedBy == "accessed") {
			
			$orderBy = "groups.lastAccessed DESC";
			
		} elseif ($organizedBy == "alphabetical") {
			
			$orderBy = "groups.name ASC";
			
		} else {
			
			$orderBy = "groups.dateCreated DESC";
			
		}
		
		if (trim($maxDisplay) == "") {
			
			$maxDisplay = "1";
			
		}
		
		if (trim($startAt) != "") {
			
			$limitStart = "$startAt, ";
			
		}
		
		if (trim($maxPerRow) == "") {
			
			$maxPerRow = "1";
			
		}
		
		$result = mysql_query("SELECT groups.id, groups.name, groups.dateCreated, (SELECT COUNT(parentId) FROM groupsMembers WHERE groupsMembers.parentId = groups.id AND groupsMembers.status = 'approved') AS totalMembers, (SELECT username FROM groupsMembers WHERE groupsMembers.parentId = groups.id AND groupsMembers.memberLevel = '1') AS owner, groups.summary, (SELECT COUNT(groupId) FROM conversations WHERE conversations.groupId = groups.id) AS totalConversations, (SELECT COUNT(groupId) FROM events WHERE events.groupId = groups.id) AS totalEvents FROM groups WHERE 1 ORDER BY $orderBy LIMIT $limitStart$maxDisplay");
		$total = mysql_num_rows($result);
		
		
		
		//if there are no results, return the noContent value
		if ($total == 0) {
			
			return("<div class=\"no_content\">$noContent</div>");
			
		}
		
		$x = 0;
		$count = 0;
		
		//adjust maxPerRow is it's greater than the returned number of objects
		if ($maxPerRow > $total) {
			
			$maxPerRow = $total;
			
		}
		
		while ($row = mysql_fetch_object($result)) {
			
			//count this itteration
			$x++;
			
			//determine if separator class is applied
			if ($x % $maxPerRow != 0) {
				
				$separator = " separator";
				
			} else {
				
				$separator = "";
				
			}
			
			if ($showImage == "true" && trim($row->summaryImage) != "") {
				
				if (trim($imageWidth) == "" && trim($imageHeight) == "") {
					
					$image = "	<div class=\"image\">\n<a href=\"/groups/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&thumbs=true\" border=\"0\"></a>\n</div>\n";
					
				} else {
					
					$image = "	<div class=\"image\">\n<a href=\"/groups/id/$row->id\"><img src=\"/file.php?load=$row->summaryImage&w=$imageWidth&h=$imageHeight\" border=\"0\"></a>\n</div>\n";
					
				}
				
				$imageOffset = " image_offset";
				
			} else {
				
				$image = "";
				$imageOffset = "";
				
			}
			
			$return .= "<div class=\"item$separator\">\n";
			$return .= "$image";
			$return .= "	<div class=\"details$imageOffset\">\n";
			
			if ($showLabels == "true") {
				
				$nameLabel = "<td class=\"name_label\">Name:</td>";
				$dateLabel = "<td class=\"date_label\">Established:</td>";
				$ownerLabel = "<td class=\"owner_label\">Owner:</td>";
				$summaryLabel = "<td class=\"summary_label\">About:</td>";
				
			}
			
			$return .= "		<table>";
			
			if ($showName == "true") {
				
				$return .= "			<tr>$nameLabel<td class=\"name\"><a href=\"/groups/id/$row->id\">" . htmlentities($row->name) . "</a></td></tr>\n";
				
			}
			
			if ($showDate == "true") {
				
				$return .= "			<tr>$dateLabel<td class=\"date\">" . date("$dateFormat", strtotime($row->dateCreated)) . "</td></tr>\n";
				
			}
			
			if ($showOwner == "true") {
				
				$return .= "			<tr>$ownerLabel<td class=\"owner\">$row->owner</td></tr>\n";
				
			}
			
			if ($showAbout == "true") {
				
				if (trim($maxCharCount) != "" && (strlen($row->summary) > $maxCharCount)) {
					
					$summary = substr($row->summary, 0, strrpos(substr($row->summary, 0, $maxCharCount), ' ')) . '...';
					
				} else {
					
					$summary = $row->summary;
					
				}
				
				$summary = preg_replace("/\\n/", "<br>", htmlentities($summary));
				
				$return .= "			<tr>$summaryLabel<td class=\"summary\">$summary</td></tr>\n";
				
			}
			
			$return .= "		</table>";
			
			if ($showTotalMembers == 'true' || $showTotalConversations == 'true' || $showTotalEvents == 'true') {
				
				$return .= "			<div class=\"stats_bar\">";
			
				if ($showTotalMembers == "true") {
					
					$return .= "			<div class=\"members\">$row->totalMembers</div>\n";
					
				}
				
				if ($showTotalConversations == "true") {
					
					$return .= "			<div class=\"conversations\">$row->totalConversations</div>\n";
					
				}
				
				if ($showTotalEvents == "true") {
					
					$return .= "			<div class=\"events\">$row->totalEvents</div>\n";
					
				}
				
				$return .= "			</div>";
				
			}
			
			$return .= "	</div>\n";
			$return .= "</div>\n";
			
			//keep track of total displayed so far
			$count++;
			
			//row separator
			if ($x == $maxPerRow && $count < $total) {
				
				$return .= "<div class=\"row_separator\"></div>\n";
				$x = 0;
				
			}
			
		}
		
		return($return);
		
	}
	
	function convertSmartlinks($matches) {
		
		$activeDocument = $matches[1];
		$cssClass = $matches[2];
		$activeCssClass = $matches[3];
		$linkURL = $matches[4];
		$linkOnActive = $matches[5];
		$linkText = $matches[6];
		
		//strip characters and spaces
		$activeDocument = preg_replace('/[^A-Za-z0-9-,]/', '', $activeDocument);
		
		//convert anything that's not a number to a comma
		$activeDocument = preg_replace('/[^A-Za-z0-9-,]/', ',', $activeDocument);
		
		//remove any extra commas
		$activeDocument = preg_replace('/,{2,}/', ',', $activeDocument);
		
		//break everything apart in an array using a comma as the delimiter
		$activeDocumentList = explode(',', $activeDocument);
		
		//search for any occurrence of $GLOBAL['id'] inside of $activeDocument (e.g. allows comma separated values so that the same menu item will be highlighted for multiple documents
		if (in_array($GLOBALS['shortcut'], $activeDocumentList)) {
			
			if ($linkOnActive != "true") {
				
				$showLinkText = "$linkText";
				
			} else {
				
				$showLinkText = "<a href=\"$linkURL\">$linkText</a>";
				
			}
			
			$return = "<div class=\"$activeCssClass\">$showLinkText</div>";
			
		} else {
		
			$return = "<div class=\"$cssClass\"><a href=\"$linkURL\">$linkText</a></div>";
			
		}
		
		//troubleshooting regex and explode
		//$return = "test";
		
		return($return);
		
	}
	
	function convertToggler($matches) {
		
		$text = $matches[1];
		$toggleId = $matches[2];
		$activeDocument = $matches[3];
		$cssClassLink = $matches[4];
		$activeCssClassLink = $matches[5];
		$cssClassContent = $matches[6];
		$togglerStyle = $matches[7];
		$content = $matches[8];
		
		//strip characters and spaces
		$activeDocument = preg_replace('/[^A-Za-z0-9-,]/', '', $activeDocument);
		
		//convert anything that's not a number to a comma
		$activeDocument = preg_replace('/[^A-Za-z0-9-,]/', ',', $activeDocument);
		
		//remove any extra commas
		$activeDocument = preg_replace('/,{2,}/', ',', $activeDocument);
		
		//break everything apart in an array using a comma as the delimiter
		$activeDocumentList = explode(',', $activeDocument);
		
		//search for any occurrence of $GLOBAL['id'] inside of $activeDocument (e.g. allows comma separated values so that the same menu item will be highlighted for multiple documents
		if (trim($activeDocument) != "" && in_array($GLOBALS['shortcut'], $activeDocumentList)) {
			
			$linkCssState = $activeCssClassLink;
			$displayState = "block";
			
		} else {
		
			$linkCssState = $cssClassLink;
			$displayState = "none";
			
		}
		
		$return = "<div id=\"$toggleId\" class=\"$linkCssState\"><a href=\"javascript:toggler('$toggleId', '{$toggleId}_content', '$cssClassLink', '$activeCssClassLink', '$togglerStyle');\">$text</a></div><div id=\"{$toggleId}_content\" class=\"$cssClassContent\" style=\"display:$displayState;\">$content</div>";
		
		return($return);
		
	}
	
	function loadAttributes($matches) {
		
		include("assets/core/config/part_ratings.php");
		
		//get ID
		$attribute = $matches[1];
		
		preg_match("/(\w+)\s*,?\s*([^.]+)?/i", $attribute, $attributeOptions);
		
		$type = $attributeOptions[1];
		$options = $attributeOptions[2];
		
		//grab the MySQL variable from the calling script
		$row = $GLOBALS['row'];
		
		//validate user group
		$userGroup = new CategoryUserGroupValidator();
		$userGroup->loadCategoryUserGroups(sanitize_string($row->category));
		
		switch($type) {
			
			case "usernameCreated":
				
				if (trim($row->usernameCreated) != "") {
					
					$return = htmlentities($row->usernameCreated);
					$urlUsernameCreated = urlencode($row->usernameCreated);
					$showUsernameCreated = htmlentities($row->usernameCreated);
					$return = "<a href=\"/documentList.php?listAuthor=$urlUsernameCreated\">$showUsernameCreated</a>";
					
					
				} else {
					
					$return = "usernameCreated";
					
				}
				
				break;
				
			case "usernameUpdated":
				
				if (trim($row->usernameUpdated) != "") {
					
					$urlUsernameUpdated = urlencode($row->usernameUpdated);
					$showUsernameUpdated = htmlentities($row->usernameUpdated);
					$return = "<a href=\"/documentList.php?listAuthor=$urlUsernameUpdated\">$showUsernameUpdated</a>";
					
				} else {
					
					$return = "usernameUpdated";
					
				}
				
				break;
				
			case "documentType":
				
				if (trim($row->documentType) != "") {
					
					$return = htmlentities($row->documentType);
					
				} else {
					
					$return = "documentType";
					
				}
				
				break;
				
			case "category":
				
				if (trim($row->category) != "") {
					
					$urlCategory = urlencode($row->category);
					$showCategory = htmlentities($row->category);
					$return = "<a href=\"/documentList.php?listCategory=$urlCategory\">$showCategory</a>";
					
				} else {
					
					$return = "category";
					
				}
				
				break;
				
			case "subcategory":
				
				if (trim($row->subcategory) != "") {
					
					$urlCategory = urlencode($row->category);
					$urlSubcategory = urlencode($row->subcategory);
					$showSubcategory = htmlentities($row->subcategory);
					$return = "<a href=\"/documentList.php?listCategory=$urlCategory&listSubcategory=$urlSubcategory\">$showSubcategory</a>";
					
				} else {
					
					$return = "subcategory";
					
				}
				
				break;
				
			case "subject":
				
				if (trim($row->subject) != "") {
					
					$urlCategory = urlencode($row->category);
					$urlSubcategory = urlencode($row->subcategory);
					$urlSubject = urlencode($row->subject);
					$showSubject = htmlentities($row->subject);
					$return = "<a href=\"/documentList.php?listCategory=$urlCategory&listSubcategory=$urlSubcategory&listSubject=$urlSubject\">$showSubject</a>";
					
				} else {
					
					$return = "subject";
					
				}
				
				break;
				
			case "ratingGraphic":
				
				if (trim($row->rating) != "") {
					
					$return = "<div class=\"rating_graphic rating" . $row->rating . "\"></div>";
					
				} else {
					
					$return = "";
					
				}
				
				break;
				
			case "ratingText":
				
				if (trim($row->rating) != "") {
					
					$return = "<div class=\"rating_text\">" . $ratingOptions[$row->rating] . "</div>";
					
				} else {
					
					$return = "";
					
				}
				
				break;
				
			case "dateCreated":
				
				if (trim($row->dateCreated) != "") {
					
					if (trim($options) != "") {
						
						$dateFormat = $options;
						
					} else {
						
						$dateFormat = "F jS, Y h:i A";
						
					}
					
					$return = htmlentities(date($dateFormat, strtotime($row->dateCreated)));
					
				} else {
					
					$return = "dateCreated";
					
				}
				
				break;
				
			case "dateUpdated":
				
				if (trim($row->dateUpdated) != "") {
					
					if (trim($options) != "") {
						
						$dateFormat = $options;
						
					} else {
						
						$dateFormat = "F jS, Y h:i A";
						
					}
					
					$return = htmlentities(date($dateFormat, strtotime($row->dateUpdated)));
					
				} else {
					
					$return = "dateUpdated";
					
				}
				
				break;
				
			case "datePublished":
				
				if (trim($row->datePublished) != "") {
					
					if (trim($options) != "") {
						
						$dateFormat = $options;
						
					} else {
						
						$dateFormat = "F jS, Y h:i A";
						
					}
					
					$return = htmlentities(date($dateFormat, strtotime($row->datePublished)));
					
				} else {
					
					$return = "datePublished";
					
				}
				
				break;
			
			case "startDate":
				
				if (trim($row->startDate) != "") {
					
					if (trim($options) != "") {
						
						$dateFormat = $options;
						
					} else {
						
						$dateFormat = "F jS, Y h:i A";
						
					}
					
					$return = htmlentities(date($dateFormat, strtotime($row->startDate)));
					
				} else {
					
					$return = "startDate";
					
				}
				
				break;
			
			case "expireDate":
				
				if (trim($row->expireDate) != "") {
					
					if (trim($options) != "") {
						
						$dateFormat = $options;
						
					} else {
						
						$dateFormat = "F jS, Y h:i A";
						
					}
					
					$return = htmlentities(date($dateFormat, strtotime($row->expireDate)));
					
				} else {
					
					$return = "expireDate";
					
				}
				
				break;
			
			case "title":
				
				if (trim($row->title) != "") {
					
					$return = htmlentities($row->title);
					
				} else {
					
					$return = "title";
					
				}
				
				break;
				
			case "author":
				
				if (trim($row->author) != "") {
					
					$urlAuthor = urlencode($row->author);
					$showAuthor = htmlentities($row->author);
					$return = "<a href=\"/documentList.php?listAuthor=$urlAuthor\">$showAuthor</a>";
					
				} else {
					
					$return = "author";
					
				}
				
				break;
				
		}
		
		return($return);
		
	}
	
	function loadComponentFile($matches) {
		
		//get filename
		$filename = $matches[1];
		
		if (is_file($filename)) {
			
			ob_start();
			include $filename;
			$contents = ob_get_contents();
			ob_end_clean();
			return($contents);
			
		}
		
		return(false);
		
	}
	
	function multiselectSplitter($parameter, $data) {
		
		if (trim($data) != "") {
			
			$keywords = preg_split("/[,;]\s*/", $data);
			
			$return .= "(";
			
			for ($x = 0; $x < count($keywords); $x++) {
	
				$return .= "$parameter = '" . sanitize_string($keywords[$x]) . "'";
	
				if ($x < count($keywords)-1) {
	
					$return .= " OR ";
	
				}
	
			}
			
			$return .= ")";
			
		} else {
			
			$return = " ";
			
		}
		
		return($return);
		
	}
	
	function skipSplitter($parameter, $data) {
		
		if (trim($data) != "") {
			
			$keywords = preg_split("/[,;]\s*/", $data);
			
			$return .= " AND (";
			
			for ($x = 0; $x < count($keywords); $x++) {
	
				$return .= "$parameter != '" . sanitize_string($keywords[$x]) . "\"";
	
				if ($x < count($keywords)-1) {
	
					$return .= " AND ";
	
				}
	
			}
			
			$return .= ")";
			
		} else {
			
			$return = " ";
			
		}
		
		return($return);
		
	}

}

?>