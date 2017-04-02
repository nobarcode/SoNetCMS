<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$d = sanitize_string($_REQUEST['d']);
$username = sanitize_string($_REQUEST['username']);
$name = sanitize_string($_REQUEST['name']);
$email = sanitize_string($_REQUEST['email']);
$company = sanitize_string($_REQUEST['company']);
$profession = sanitize_string($_REQUEST['profession']);
$city = sanitize_string($_REQUEST['city']);
$state = sanitize_string($_REQUEST['state']);
$radius = sanitize_string($_REQUEST['radius']);
$zip = sanitize_string($_REQUEST['zip']);
$country = sanitize_string($_REQUEST['country']);
$minAge = sanitize_string($_REQUEST['minAge']);
$maxAge = sanitize_string($_REQUEST['maxAge']);
$race = sanitize_string($_REQUEST['race']);
$gender = sanitize_string($_REQUEST['gender']);
$minHeightFeet = sanitize_string($_REQUEST['minHeightFeet']);
$minHeightInches = sanitize_string($_REQUEST['minHeightInches']);
$maxHeightFeet = sanitize_string($_REQUEST['maxHeightFeet']);
$maxHeightInches = sanitize_string($_REQUEST['maxHeightInches']);
$bodyType = sanitize_string($_REQUEST['bodyType']);
$orientation = sanitize_string($_REQUEST['orientation']);
$religion = sanitize_string($_REQUEST['religion']);
$smoke = sanitize_string($_REQUEST['smoke']);
$drink = sanitize_string($_REQUEST['drink']);
$hereFor = sanitize_string($_REQUEST['hereFor']);
$about = sanitize_string($_REQUEST['about']);
$interests = sanitize_string($_REQUEST['interests']);
$orderBy = sanitize_string($_REQUEST['orderBy']);

//read config file and determine if people finder requires authentication, if it does and the user is not logged in, exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('findPeopleAuthentication') == 'true' && trim($_SESSION['username']) == "") {
	
	exit;
	
}

//zipcode search classes
require_once("BoundaryWizard.php");
require_once("DistanceWizard.php");

changeDirection($s, $d, $username, $name, $email, $company, $profession, $city, $state, $radius, $zip, $country, $minAge, $maxAge, $race, $gender, $minHeightFeet, $minHeightInches, $maxHeightFeet, $maxHeightInches, $bodyType, $orientation, $religion, $smoke, $drink, $hereFor, $about, $interests, $orderBy);

function changeDirection($s, $d, $username, $name, $email, $company, $profession, $city, $state, $radius, $zip, $country, $minAge, $maxAge, $race, $gender, $minHeightFeet, $minHeightInches, $maxHeightFeet, $maxHeightInches, $bodyType, $orientation, $religion, $smoke, $drink, $hereFor, $about, $interests, $orderBy) {
	
	$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	
	$max_per_page = 25;
	
	if (trim($s) == "") {

		$s = 0;

	}
	
	//build query
	if (trim($radius) != "" && trim($zip) != "") {
		
		$unit = Measurement::MILES;

		//check if zipcode exists, if it does create zipcode query string.
		$result = mysql_query("SELECT Latitude, Longitude FROM ZIPCodes WHERE ZIPCode = '{$zip}' LIMIT 1");

		if (mysql_num_rows($result) > 0) {
			
			// ZIP code exists, now find the nearby zip codes.
			$row = mysql_fetch_array($result);
			$latitude = $row['Latitude'];
			$longitude = $row['Longitude'];
			
			// Construct the BoundaryWizard object.
			// It will perform the calculation to get our boundary.
			$boundaryWizard = new BoundaryWizard();
			
			// Create the coordinate of the origin ZIP code.
			$originCoord = new Coordinate($latitude, $longitude);
			
			// Calculate.
			$boundary = $boundaryWizard->CalculateBoundary($originCoord, $radius, $unit);
			
			// Retrieve our bounds.
			$northern = $boundary->North();
			$southern = $boundary->South();
			$eastern = $boundary->East();
			$western = $boundary->West();
			
			/*
		    Prepare to find some of the nearby users.

		    NOTE:
		    Regarding the BETWEEN clauses, the lower (smaller) value should be
		    first, and the value after the AND should be higher (larger).
		    Consequently, this query only works for the western part
		    of the northern hemisphere. For other parts of the world,
		    you'll need to order these properly in your query!

		    */
			
			$searchQuery .= " INNER JOIN ZIPCodes AS zips ON users.zip = zips.ZIPCode WHERE (Latitude BETWEEN $southern AND $northern AND Longitude BETWEEN $western AND $eastern AND Latitude != 0 AND Longitude != 0) ";
			$zipLatLong = ", zips.Latitude, zips.Longitude";
			
		} else {
			
			$searchQuery .= " WHERE 1=1";
			
			print "<div id=\"editor_navigation\">";
			print "	<div class=\"totals\">0 Profiles Found</div><div class=\"navigation\"><div class=\"pages\">Page: 0 of 0</div><div class=\"previous\"><a href=\"javascript:regenerateList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
			print "</div>";
			
		}
		
	} else {
		
		$searchQuery .= " WHERE 1=1";
		
	}
	
	if (trim($username) != "") {
		
		$searchQuery .= " AND username LIKE '%$username%'";
		
	}
	
	if (trim($name) != "") {
		
		$searchQuery .= " AND (name LIKE '%$name%' AND showName = 1)";
		
	}
	
	if (trim($email) != "") {
		
		$searchQuery .= " AND email LIKE '%$email%'";
		
	}
	
	if (trim($company) != "") {
		
		$searchQuery .= " AND company LIKE '%$company%'";
		
	}
	
	if (trim($profession) != "") {
		
		$searchQuery .= " AND profession LIKE '%$profession%'";
		
	}
	
	if (trim($city) != "") {
		
		$searchQuery .= " AND city LIKE '%$city%'";
		
	}
	
	if (trim($state) != "") {
		
		$searchQuery .= " AND state LIKE '%$state%'";
		
	}
	
	if (trim($country) != "") {
		
		$searchQuery .= " AND country LIKE '%$country%'";
		
	}
	
	if (trim($minAge) != "") {
		
		$searchQuery .= " AND (FLOOR(DATEDIFF(CURDATE(), dateOfBirth) / 365) >= $minAge AND showAge = 1)";
		
	}
	
	if (trim($maxAge) != "") {
		
		$searchQuery .= " AND (FLOOR(DATEDIFF(CURDATE(), dateOfBirth) / 365) <= $maxAge AND showAge = 1)";
		
	}
	
	if (trim($race) != "") {
		
		$searchQuery .= " AND race = '$race'";
		
	}
	
	if (trim($gender) != "") {
		
		$searchQuery .= " AND gender = '$gender'";
		
	}
	
	if (trim($minHeightFeet) != "" && trim($minHeightInches) != "") {
		
		$minHeight = ($minHeightFeet * 12) + $minHeightInches;
		
		$searchQuery .= " AND ((heightFeet * 12) + heightInches) >= $minHeight";
		
	}
	
	if (trim($maxHeightFeet) != "" && trim($maxHeightInches) != "") {
		
		$maxHeight = ($maxHeightFeet * 12) + $maxHeightInches;
		
		$searchQuery .= " AND ((heightFeet * 12) + heightInches) <= $maxHeight";
		
	}
	
	if (trim($bodyType) != "") {
		
		$searchQuery .= " AND bodyType = '$bodyType'";
		
	}
	
	if (trim($orientation) != "") {
		
		$searchQuery .= " AND orientation = '$orientation'";
		
	}
	
	if (trim($religion) != "") {
		
		$searchQuery .= " AND religion = '$religion'";
		
	}
	
	if (trim($smoke) != "") {
		
		$searchQuery .= " AND smoke = '$smoke'";
		
	}
	
	if (trim($drink) != "") {
		
		$searchQuery .= " AND drink = '$drink'";
		
	}
	
	if (count($hereFor) > 0) {
		
		$searchQuery .= " AND (";
		
		for ($x = 0; $x < count($hereFor); $x++) {
			
			$searchQuery .= "hereFor LIKE '%" . $hereFor[$x]. "%'";
			
			if ($x < count($hereFor)-1) {
				
				$searchQuery .= " OR ";
				
			}
			
		}
		
		$searchQuery .= ")";
		
	}
		
	if (trim($about) != "") {
		
		$searchQuery .= " AND about LIKE '%$about%'";
		
	}
	
	if (trim($interests) != "") {
		
		$searchQuery .= " AND interests LIKE '%$interests%'";
		
	}
	
	//order type
	if ($orderBy == "lastLogin") {
		
		$queryOrder = " ORDER BY users.lastLogin DESC";
		
	} elseif ($orderBy == "username") {
		
		$queryOrder = " ORDER BY users.username ASC";
		
	}
	
	$result = mysql_query("SELECT username FROM users$searchQuery");
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
	
	$result = mysql_query("SELECT users.id AS userId, users.username, users.lastLogin, users.imageUrl$zipLatLong FROM users $searchQuery$queryOrder LIMIT $s, $max_per_page");
	$count = mysql_num_rows($result);
		
	if ($count < 1 && $totalRows > 0 && $s > 0) {

		changeDirection($s, $d, $username, $name, $email, $company, $profession, $city, $state, $radius, $zip, $country, $minAge, $maxAge, $race, $gender, $minHeightFeet, $minHeightInches, $maxHeightFeet, $maxHeightInches, $bodyType, $orientation, $religion, $smoke, $drink, $hereFor, $about, $interests, $orderBy);

	} else {
				
		// Now let's go through each ZIP code and find out how
		// far they are from the origin coordinate and display it.
		$distanceWizard = new DistanceWizard();
		
		// Our array is declared here for scope. (Read about this below.)
		$dataset;
		
		// Loop through each result
		$i = 0;
		
		while ($rowZIP = mysql_fetch_array($result)) {
			
			//if the user has entered a zip code get coordinates and distance
			if (trim($radius) != "" && trim($zip) != "") {
				
				// Get the relative ZIP code's coordinate.
				$relativeCoord = new Coordinate($rowZIP['Latitude'], $rowZIP['Longitude']);

				// Calculate the distance.
				$distance = $distanceWizard->CalculateDistance(
					$originCoord,
					$relativeCoord,
					$unit
				);

				// This next "if" statement is necessary because of the box/circle model.
				// (See the documentation.) A boundary is a square but distances are calculated
				// using a radius (on a circle). This circle fits within that square and so
				// there are 'corners' that aren't quite within the desired distance.
				if ($distance <= $radius) {

					// We want to sort this by distance ASC, so the closest cities are displayed
					// first. Let's put the City & Zip & Distance values in an array for this example.
					// Normally you will use whichever values you need and in the order that
					// you need them.

					$dataset[$i]['userId'] = $rowZIP['userId'];
					$dataset[$i]['username'] = $rowZIP['username'];
					$dataset[$i]['lastLogin'] = $rowZIP['lastLogin'];
					$dataset[$i]['imageUrl'] = $rowZIP['imageUrl'];
					$dataset[$i]['Distance'] = $distance;
					$i++;
					
				}
				
			//otherwise, if the user has not entered a zip code, just fill in the dataset
			} else {
				
				$dataset[$i]['userId'] = $rowZIP['userId'];
				$dataset[$i]['username'] = $rowZIP['username'];
				$dataset[$i]['lastLogin'] = $rowZIP['lastLogin'];
				$dataset[$i]['imageUrl'] = $rowZIP['imageUrl'];
				$dataset[$i]['Distance'] = $distance;
				$i++;
				
			}
			
		}
		
		if (trim($radius) != "" && trim($zip) != "" && $count > 0) {
			
			//perform sort
			sort_2d_array_asc($dataset, "Distance", $orderBy);
			
		}
		
		//display results
		$x = 0;
		$count_total_so_far = 0;
		
		if ($dataset) {
			
			foreach ($dataset as $resultset) {
			
				if(is_file($script_directory . $resultset['imageUrl'])) {
					
					$showImage = "<img src=\"file.php?load=" . $resultset['imageUrl'] . "&thumbs=true\" border=\"0\">";
					
				} else {
					
					$showImage = "<img style=\"margin-top:17px;\" src=\"/assets/core/resources/images/member_no_image_small.jpg\" border=\"0\">";
					
				}
				
				//if the user entered a zip code, show how many miles each result is from the supplied zip code
				if (trim($radius) != "" && trim($zip) != "") {
					
					$showMiles = "<div class=\"user_distance\">" . round($resultset['Distance'], 2) . " miles</div>";
					
				}
				
				$x++;
				
				if ($x % 5 != 0) {
					
					print "<div id=\"user_" . $resultset['userId'] . "\" class=\"user_container profile_column_separator\">";
					
				} else {
					
					print "<div id=\"user_" . $resultset['userId'] . "\" class=\"user_container\">";
					
				}
				
				print "<div class=\"user_image\"><a href=\"/showProfile.php?username=" . $resultset['username'] . "\">$showImage</a></div><div class=\"user_details\"><a href=\"/showProfile.php?username=" . $resultset['username'] . "\">" . $resultset['username'] . "</a>$showMiles</div>";
				print "</div>";
				print "</div>";
				print "</div>";
				
			}
			
		}
		
		print "<div id=\"editor_navigation\">";
		print "	<div class=\"totals\">$totalRows Profiles Found</div><div class=\"navigation\"><div class=\"pages\">Page: $showCurrentPage of $showTotalPages</div><div class=\"previous\"><a href=\"javascript:regenerateList($s, 'b');\" title=\"Previous Results\">Previous</a></div><div class=\"next\"><a href=\"javascript:regenerateList($s, 'n');\" title=\"Next Results\">Next</a></div></div>";
		print "</div>";
		
	}
	
}

// Sorts the array by distance in ASC order and also sort by the selectable sort option (by passing this function the "$orderBy" variable)
function sort_2d_array_asc(&$array, $innerkey, $orderBy) {
	
	$dim = array();
	
	foreach ($array as $innerarray) {
		
		$distance[] = $innerarray[$innerkey];
		
		//order type
		if ($orderBy == "lastLogin") {
			
			$selectableSort[] = $innerarray['lastLogin'];
			$sortOrder = SORT_DESC;

		} elseif ($orderBy == "username") {
			
			$selectableSort[] = $innerarray['username'];
			$sortOrder = SORT_ASC;

		}
		
	}
	
	//sort it
	array_multisort($distance, SORT_ASC, $selectableSort, $sortOrder, $array);
	
}

?>
