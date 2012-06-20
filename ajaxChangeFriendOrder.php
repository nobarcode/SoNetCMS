<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

$s = sanitize_string($_REQUEST['s']);
$friend = sanitize_string($_REQUEST['friend']);

if (trim($_SESSION['username']) == "" || trim($s) == "" || trim($friend[0]) == "") {$error = 1;}

parse_str($friend);

//grab the weight of the first friend in the list
$result = mysql_query("SELECT * FROM friends WHERE owner = '{$_SESSION['username']}' AND status = 'approved' ORDER BY weight LIMIT $s, 1");
$row = mysql_fetch_object($result);

$y = $row->weight;

if ($error != 1) {
	
	for ($x = 0; $x < count($friend); $x++) {
		
		mysql_query("UPDATE friends INNER JOIN users ON users.id = '$friend[$x]' SET weight = '{$y}' WHERE friends.owner = '{$_SESSION['username']}' AND friends.status = 'approved' AND friends.friend = users.username");
		
		$y++;
		
	}
	
}

?>