<?php

include("assets/core/config/part_set_timezone.php");
include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_category_user_group_validator.php");
include("class_config_reader.php");

//read config file and determine if the forms processor is enabled, if not then exit
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/scripts/processForm/processForm.properties');

if ($config->readValue('enableFormsProcessor') != 'true') {
	
	exit;
	
}

if (trim($_REQUEST['subject']) != "") {
	
	$subjectEmail = unsanitize_string($_REQUEST['subject']);
	
} else {
	
	$subjectEmail = preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . " forms processor results";
	
}

//assemble a message using the values passed by the form
foreach ($_REQUEST as $var => $value) {
	
	$messageBody .= "<tr><td>" . htmlentities(unsanitize_string($var)) . "</td><td>" . htmlentities(unsanitize_string($value)) . "</td></tr>";
	
}

$to = $config->readValue('formsProcessorRecipients');

$messageEmail = "<html>";
$messageEmail .= "<body>";
$messageEmail = "The following message was processed via a form on: " . preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']) . "<br><br><table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">$messageBody</table>";
$messageEmail .= "</body>";
$messageEmail .= "</html>";

$headers = "MIME-Version: 1.0\r\n"; 
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
$headers .= "From: " . $config->readValue('fromEmailAddress') . "\r\n";
$headers .= "Reply-To: " . $config->readValue('fromEmailAddress') . "\r\n";

mail($to, $subjectEmail, $messageEmail, $headers);

header("location:" . $_REQUEST['returnURL']);

?>