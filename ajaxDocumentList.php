<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("part_word_limiter.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$listCategory = sanitize_string($_REQUEST['listCategory']);
$listSubcategory = sanitize_string($_REQUEST['listSubcategory']);
$listSubject = sanitize_string($_REQUEST['listSubject']);
$listAuthor = sanitize_string($_REQUEST['listAuthor']);

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$orderBy = sanitize_string($_REQUEST['orderBy']);
$orderDirection = sanitize_string($_REQUEST['orderDirection']);

$userGroup = new CategoryUserGroupValidator();
$excludeDocumentCategories = $userGroup->viewCategoryExclusionList('documents');
$excludeBlogCategories = $userGroup->viewCategoryExclusionList('blogs');

if (trim($listCategory) != "") {
	
	$filter .= " AND category = '$listCategory'";
	
}

if (trim($listSubcategory) != "") {
	
	$filter .= " AND subcategory = '$listSubcategory'";
	
}

if (trim($listSubject) != "") {
	
	$filter .= " AND subject = '$listSubject'";
	
}

if (trim($listAuthor) != "") {
	
	$filterDocuments .= " AND author = '$listAuthor'";
	$filterBlogs .= " AND usernameCreated = '$listAuthor'";
	
}

mysql_query("CREATE TEMPORARY TABLE tempTable (id bigint, shortcut varchar(128), rating varchar(4), title varchar(128), datePublished datetime, author varchar(128), summary text, keywords text, showToolbar tinyint(1), docType varchar(40), score int, upVotes int, downVotes int)");
mysql_query("INSERT INTO tempTable SELECT documents.id, documents.shortcut, documents.rating, documents.title, documents.datePublished, documents.author, documents.summary, documents.keywords, documents.showToolbar, 'document' AS docType, ROUND(SUM(documentVotes.voteYes) / COUNT(documentVotes.parentId) * 100) AS voteScore, SUM(documentVotes.voteYes) AS upVotes, SUM(documentVotes.voteNo) AS downVotes FROM documents LEFT JOIN documentVotes ON documentVotes.parentId = documents.id AND documentVotes.type = 'document' WHERE documents.publishState = 'published'$filter$filterDocuments$excludeDocumentCategories AND documents.component != 1 GROUP BY documents.id");
mysql_query("INSERT INTO tempTable SELECT blogs.id, '' AS shortcut, blogs.rating, blogs.title, blogs.dateCreated, blogs.usernameCreated AS author, blogs.summary, blogs.keywords, '1' AS showToolbar, 'blog' AS docType, ROUND(SUM(documentVotes.voteYes ) / COUNT(documentVotes.parentId) * 100) AS voteScore, SUM(documentVotes.voteYes) AS upVotes, SUM(documentVotes.voteNo) AS downVotes FROM blogs LEFT JOIN documentVotes ON documentVotes.parentId = blogs.id AND documentVotes.type = 'blog' WHERE blogs.publishState = 'published'$filter$filterBlogs$excludeBlogCategories GROUP BY blogs.id");

changeDirection($s, $d, $orderBy, $orderDirection);

function changeDirection($s, $d, $orderBy, $orderDirection) {
	
	include("assets/core/config/part_ratings.php");
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	//order type
	if ($orderBy == "votes") {
		
		$queryOrder = " ORDER BY score";
		
	} elseif ($orderBy == "title") {
		
		$queryOrder = " ORDER BY title";
		
	} elseif ($orderBy == "author") {
		
		$queryOrder = " ORDER BY author";
		
	} elseif ($orderBy == "date") {
		
		$queryOrder = " ORDER BY datePublished";
		
	} elseif ($orderBy == "rating") {
		
		$queryOrder = " ORDER BY CAST(rating as UNSIGNED)";
		
	} else {
		
		$queryOrder = " ORDER BY datePublished";
		
	}

	//order direction
	if ($orderDirection == "desc") {
		
		$queryOrder .= " DESC";
		
	} elseif ($orderDirection == "asc") {
		
		$queryOrder .= " ASC";
		
	} else {
		
		$queryOrder .= " DESC";
		
	}			
	
	$result = mysql_query("SELECT id FROM tempTable WHERE id IS NOT NULL");
	$totalRows = mysql_num_rows($result);

	$showTotalPages = ceil($totalRows / $max_per_page);

	if ($d == "b") {

		$s -= $max_per_page;

		if ($s < 0) {

			$s = 0;

		}

	}

	if ($d == "n") {

		if ($s + $max_per_page < $totalRows) {

			$s += $max_per_page;

		}

	}

	if ($totalRows > 0) {

		$showCurrentPage = floor($s / $max_per_page) + 1;

	} else {

		$showCurrentPage = 0;

	}

	$result = mysql_query("SELECT id, shortcut, rating, title, author, DATE_FORMAT(datePublished, '%M %d, %Y %h:%i %p') AS showDate, summary, showToolbar, docType, score, upVotes, downVotes FROM tempTable WHERE id IS NOT NULL $queryOrder LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {

		changeDirection($s, 'b', $search, $orderBy, $orderDirection);

	} else {
		
		if ($count > 0) {
			
			$x = 0;
			
			while ($row = mysql_fetch_object($result)) {
				
				$x++;
				
				if ($x < $count) {
					
					$style = " document_row_separator";
					
				} else {
					
					$style = "";
					
				}
				
				$title = htmlentities($row->title);
				$showAuthor = "$row->author";
				$showDate = "$row->showDate";
				
				if ($row->docType == "document") {
					
					$elementName = "document_summary_$row->id";
					$linkTo = "/documents/open/$row->shortcut";
					$summary = word_limiter($row->summary, 60, "document_summary_$row->id", $row->id, 'document');
					
				} elseif ($row->docType == "blog") {
					
					$elementName = "blog_summary_$row->id";
					$linkTo = "/blogs/id/$row->id";
					$summary = word_limiter($row->summary, 60, "blog_summary_$row->id", $row->id, 'blog');
					
				}
				
				if ($row->showToolbar == 1) {
					
					if (trim($row->upVotes) != "") {
						
						$upVotes = "+$row->upVotes";
						
					} else {
						
						$upVotes = "+0";
						
					}
					
					if (trim($row->downVotes) != "") {
						
						$downVotes = "-$row->downVotes";
						
					} else {
						
						$downVotes = "-0";
						
					}
					
					if (trim($row->upVotes) != "" || trim($row->downVotes) != "") {
						
						$votes = "<div class=\"votes\"><div class=\"up\">$upVotes</div><div class=\"down\">$downVotes</div></div>";
						
					} else {
						
						$votes = "<div class=\"votes\">no votes</div>";
						
					}
					
					if (trim($row->score) != "") {
	
						$voteScore = $row->score . "%";
	
					} else {
	
						$voteScore = "";
	
					}
					
					$score = "$votes<div class=\"score\">$voteScore</div>";
					
				}
				
				print "<div class=\"document_container$style\">\n";
				print "	<div class=\"document_summary_container\">\n";
				print "		<div class=\"document_title\"><a href=\"$linkTo\">$title</a></div>\n";
				print "		<div class=\"document_info_container\">\n";
				print "			<div class=\"document_date\">$showDate</div><div class=\"document_author\">$showAuthor</div>\n";
					
				if ($row->showToolbar == 1) {
				
					print $score;
					
				}
				
				print "		</div>\n";	
				print "		<div id=\"$elementName\" class=\"document_summary\">$summary</div>\n";
				
				if (trim($row->rating) != "") {
					
					print "<div class=\"rating_container\">\n";
					print "	<div class=\"rating_graphic rating" . $row->rating . "\"></div>\n";
					print "	<div class=\"rating_text\">" . $ratingOptions[$row->rating] . "</div>\n";
					print "</div>\n";
					
				}
				
				print "	</div>\n";
				print "</div>\n";
				
			}
			
		} else {
			
			print "No documents available.";
			
		}
			
		print "<div id=\"editor_navigation\">";
		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		print "<tr valign=\"center\">";
		print "<td>$totalRows Documents</td><td align=\"right\" nowrap>Page: $showCurrentPage of $showTotalPages <a href=\"javascript:regenerateList($s, 'b');\" title=\"Previous Results\">Previous</a> | <a href=\"javascript:regenerateList($s, 'n');\" title=\"Next Results\">Next</a></td>";
		print "</tr>";
		print "</table>";
		print "</div>";
		
	}
	
}

?>