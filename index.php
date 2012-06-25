<?php

error_reporting(E_ERROR);

include("requestVariableSanitizer.inc");

$submit = sanitize_string($_REQUEST['submit']);
$servername = sanitize_string($_REQUEST['servername']);
$database = sanitize_string($_REQUEST['database']);
$dbusername = sanitize_string($_REQUEST['dbusername']);
$dbpassword = sanitize_string($_REQUEST['dbpassword']);
$emailaddress = $_REQUEST['emailaddress'];
$ourwebsite = htmlentities($_REQUEST['ourwebsite']);
$ourlocation = htmlentities($_REQUEST['ourlocation']);
$mailto = htmlentities($_REQUEST['mailto']);
$street = htmlentities($_REQUEST['street']);
$citystatezip = htmlentities($_REQUEST['citystatezip']);
$timezone = $_REQUEST['timezone'];
$url1 = $_REQUEST['url1'];
$url2 = $_REQUEST['url2'];
$url3 = $_REQUEST['url3'];
$urlText1 = htmlentities($_REQUEST['urlText1']);
$urlText2 = htmlentities($_REQUEST['urlText2']);
$urlText3 = htmlentities($_REQUEST['urlText3']);

$timezones = array('', 'America/Puerto_Rico', 'America/New_York', 'America/Chicago', 'America/Boise', 'America/Phoenix', 'America/Los_Angeles', 'America/Juneau', 'Pacific/Honolulu', 'Pacific/Guam', 'Pacific/Samoa', 'Pacific/Wake');
$timezoneLabels = array('', 'AST', 'EDT', 'CDT', 'MDT', 'MST', 'PDT', 'AKDT', 'HST', 'ChST', 'SST', 'WAKT');

//get the full path to the current script
$script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));

for ($x = 0; $x < count($timezones); $x++) {
	
	if ($timezones[$x] != $timezone) {
		
		$timezoneOptions .= "<option value=\"" . $timezones[$x] . "\">" . $timezoneLabels[$x] . "</option>";
		
	} else {
		
		$timezoneOptions .= "<option value=\"" . $timezones[$x] . "\" selected>" . $timezoneLabels[$x] . "</option>";
		
	}
	
}

if (trim($submit) != "") {
	
	if (trim($servername) == "") {$error = 1; $serverNameMessage = "<span style=\"margin-left:10px; color:#660000;\">Invalid server name.</span>";}
	if (trim($database) == "" || !preg_match("/^[0-9a-z_]+$/i", $database)) {$error = 1; $dbNameMessage = "<span style=\"margin-left:10px; color:#660000;\">Invalid database name.</span>";}
	if (trim($dbusername) == "") {$error = 1; $dbUsernameMessage = "<span style=\"margin-left:10px; color:#660000;\">Invalid database username.</span>";}
	if (trim($dbpassword) == "") {$error = 1; $dbPasswordMessage = "<span style=\"margin-left:10px; color:#660000;\">Invalid database password.</span>";}
	if (trim($emailaddress) == "") {$error = 1; $emailAddressMessage = "<span style=\"margin-left:10px; color:#660000;\">Please provide a global e-mail address.</span>";}
	if (trim($ourwebsite) == "") {$error = 1; $ourWebsiteMessage = "<span style=\"margin-left:10px; color:#660000;\">Please provide a website or company name.</span>";}
	if (trim($ourlocation) == "") {$error = 1; $ourLocationMessage = "<span style=\"margin-left:10px; color:#660000;\">Please provide your legal location.</span>";}
	if (trim($timezone) == "") {$error = 1; $timezoneMessage = "<span style=\"margin-left:10px; color:#660000;\">Please select a timezone.</span>";}
	
}

if (trim($submit) != "" && $error != 1) {
	
	$message .= "<br><div style=\"padding:5px; background:#ddffdd; border:1px solid #008800;\">\n";
	
	if ($link = @mysql_connect("$servername", "$dbusername", "$dbpassword")) {
		
		$query = "CREATE DATABASE IF NOT EXISTS $database";
		
		if (@mysql_query("$query")) {
			
			$message .= "<div style=\"font-weight:bold; color:#006600;\">Database created successfully.</div>\n";
			$success++;
			
			@mysql_select_db("$database") or die("<div style=\"font-weight:bold; color:#660000;\">FATAL ERROR! Requested database not found:</div>$database<br><br>Did the installer fail to create the database?<br>");
			
			$handle = @fopen("sonet.sql", "r");
			
			if ($handle) {
				
				$checksum = 0;
				
				while (!feof($handle)) {
				
					$query = fgets($handle);
					if (@mysql_query("$query")) {
						
						$checksum++;
						
					} else {
						
						die("<div style=\"font-weight:bold; color:#660000;\">FATAL ERROR! Error creating tables:</div> ". mysql_error());
						
					}
					
				}
				
				fclose($handle);
				
				if ($checksum == 34) {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">All tables created successfully.</div>\n";
					$success++;
					
				} else {
				
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error creating tables. [checksum: $checksum]</div>\n";
					
				}
				
			}
			
			//check if zip code data already exists, if it doesn't then load it
			$result = mysql_query("SELECT ZIPCode FROM ZIPCodes LIMIT 1");
			if (mysql_num_rows($result) == 0) {
			
				$handle = @fopen("zipcodes.sql", "r");
				
				if ($handle) {
					
					$checksum = 0;
					
					while (!feof($handle)) {
						
						$query = fgets($handle);
						if (@mysql_query("$query")) {
							
							$checksum++;
							
						} else {
							
							$message .= "<div style=\"font-weight:bold; color:#660000;\">Error loading zip code data: " . mysql_error() . "</div>\n";
							
						}
						
					}
					
					fclose($handle);
					
					if ($checksum == 33178) {
						
						$message .= "<div style=\"font-weight:bold; color:#006600;\">All zip code data loaded successfully.</div>\n";
						$success++;
						
					} else {
					
						$message .= "<div style=\"font-weight:bold; color:#660000;\">Error loading zip code data. [checksum: $checksum]</div>\n";
						
					}
					
				}
				
			}
			
			//change contectDatabase.inc
			if ($file = file_get_contents("$script_directory/connectDatabase.inc")) {
				
				$file = str_replace("[servername]", $servername, $file);
				$file = str_replace("[database]", $database, $file);
				$file = str_replace("[dbusername]", $dbusername, $file);
				$file = str_replace("[dbpassword]", $dbpassword, $file);
				
				if (!file_put_contents("$script_directory/connectDatabase.inc", $file)) {
					
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error saving connectDatabase.inc</div>\n";
					
				} else {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">connectDatabase.inc modified successfully.</div>\n";
					$success++;
					
				}
				
			} else {
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Error opening connectDatabase.inc for modification.</div>\n";
				
			}
			
			//change privacypolicy.html
			if ($file = file_get_contents("$script_directory/assets/core/config/part_set_timezone.php")) {
				
				$file = str_replace("[timezone]", $timezone, $file);
				
				if (!file_put_contents("$script_directory/assets/core/config/part_set_timezone.php", $file)) {
					
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error saving part_set_timezone.php.</div>\n";
					
				} else {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">part_set_timezone.php modified successfully.</div>\n";
					$success++;
					
				}
				
			} else {
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Error opening part_set_timezone.php for modification.</div>\n";
				
			}
			
			//change config.properties
			if ($file = file_get_contents("$script_directory/assets/core/config/config.properties")) {
				
				$file = str_replace("[siteemailaddress]", $emailaddress, $file);
				
				if (!file_put_contents("$script_directory/assets/core/config/config.properties", $file)) {
					
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error saving config.properties</div>\n";
					
				} else {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">config.properties modified successfully.</div>\n";
					$success++;
					
				}
				
			} else {
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Error opening config.properties for modification.</div>\n";
				
			}
			
			//change contact.properties
			if ($file = file_get_contents("$script_directory/assets/core/config/scripts/contact/contact.properties")) {
				
				$file = str_replace("[siteemailaddress]", $emailaddress, $file);
				
				if (!file_put_contents("$script_directory/assets/core/config/scripts/contact/contact.properties", $file)) {
					
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error saving contact.properties</div>\n";
					
				} else {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">contact.properties modified successfully.</div>\n";
					$success++;
					
				}
				
			} else {
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Error opening contact.properties for modification.</div>\n";
				
			}
			
			//change processForm.properties
			if ($file = file_get_contents("$script_directory/assets/core/config/scripts/processForm/processForm.properties")) {
				
				$file = str_replace("[siteemailaddress]", $emailaddress, $file);
				
				if (!file_put_contents("$script_directory/assets/core/config/scripts/processForm/processForm.properties", $file)) {
					
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error saving processForm.properties</div>\n";
					
				} else {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">processForm.properties modified successfully.</div>\n";
					$success++;
					
				}
				
			} else {
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Error opening processForm.properties for modification.</div>\n";
				
			}
			
			//change privacypolicy.html
			if ($file = file_get_contents("$script_directory/privacypolicy.php")) {
				
				$file = str_replace("[OUR_WEBSITE]", $ourwebsite, $file);
				$file = str_replace("[OUR_LOCATION]", $ourlocation, $file);
				
				if (!file_put_contents("$script_directory/privacypolicy.php", $file)) {
					
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error saving privacypolicy.php</div>\n";
					
				} else {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">privacypolicy.php modified successfully.</div>\n";
					$success++;
					
				}
				
			} else {
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Error opening privacypolicy.php for modification.</div>\n";
				
			}
			
			//change termsandconditions.html
			if ($file = file_get_contents("$script_directory/termsandconditions.php")) {
				
				$file = str_replace("[OUR_WEBSITE]", $ourwebsite, $file);
				$file = str_replace("[OUR_LOCATION]", $ourlocation, $file);
				
				if (!file_put_contents("$script_directory/termsandconditions.php", $file)) {
					
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error saving termsandconditions.php</div>\n";
					
				} else {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">termsandconditions.php modified successfully.</div>\n";
					$success++;
					
				}
				
			} else {
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Error opening termsandconditions.php for modification.</div>\n";
				
			}
			
			//change layout_contact.php
			if ($file = file_get_contents("$script_directory/assets/core/layout/contact/layout_contact.php")) {
				
				if (trim($mailto) != "" || trim($street) != "" || trim($citystatezip) != "" || (trim($url1) != "" && trim($urlText1) != "") || (trim($url2) != "" && trim($urlText2) != "") || (trim($url3) != "" && trim($urlText3) != "")) {
					
					$contactInfo .= "<div class=\"alternative_contact_info\">";
					
					if (trim($mailto) != "") {
						
						$contactInfo .= "<b>Mailing Address:</b><br>$mailto";
						
					}
					
					if (trim($street) != "") {
						
						$contactInfo .= "<br>$street";
						
					}
					
					if (trim($citystatezip) != "") {
						
						$contactInfo .= "<br>$citystatezip";
						
					}
					
					if ((trim($url1) != "" && trim($urlText1) != "") || (trim($url2) != "" && trim($urlText2) != "") || (trim($url3) != "" && trim($urlText3) != "")) {
						
						$contactInfo .= "<br><br>You can also find us on ";
						
						if (trim($url1) != "" && trim($urlText1) != "") {
							
							$contactInfo .= "<a href=\"$url1\">$urlText1</a>";
							
						}
						
						if (trim($url2) != "" && trim($urlText2) != "") {
							
							if (trim($url3) != "" && trim($urlText3) != "") {
								
								$separator = ", ";
								
							} else {
								
								$separator = " and ";
								
							}
							
							$contactInfo .= "$separator<a href=\"$url2\">$urlText2</a>";
							
						}
						
						if (trim($url3) != "" && trim($urlText3) != "") {
							
							$contactInfo .= " and <a href=\"$url3\">$urlText3</a>";
							
						}
						
						$contactInfo .= ".";
						
					}
					
					$contactInfo .= "</div>";
					
				}
				
				$file = str_replace("[ALTERNATIVE_CONTACT_INFO]", $contactInfo, $file);
				
				if (!file_put_contents("$script_directory/assets/core/layout/contact/layout_contact.php", $file)) {
					
					$message .= "<div style=\"font-weight:bold; color:#660000;\">Error saving layout_contact.php</div>\n";
					
				} else {
					
					$message .= "<div style=\"font-weight:bold; color:#006600;\">layout_contact.php modified successfully.</div>\n";
					$success++;
					
				}
				
			} else {
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Error opening layout_contact.php for modification.</div>\n";
				
			}
			
			//setup .htaccess
			if(file_exists(".htaccess")) {
				
				unlink(".htaccess");
				
			}
			
			//rename main htaccess
			if(rename("_htaccess", ".htaccess")) {
				
				$message .= "<div style=\"font-weight:bold; color:#006600;\">.htaccess file setup successfully.</div>\n";
				$success++;
				
			} else {	
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Unable to setup .htaccess file.</div>\n";
				
			}
			
			//rename cms_users htaccess
			if(rename("$script_directory/cms_users/_htaccess", "$script_directory/cms_users/.htaccess")) {
				
				$message .= "<div style=\"font-weight:bold; color:#006600;\">cms_users .htaccess file setup successfully.</div>\n";
				$success++;
				
			} else {	
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Unable to setup cms_users .htaccess file.</div>\n";
				
			}
			
			//rename cms_groups htaccess
			if(rename("$script_directory/cms_groups/_htaccess", "$script_directory/cms_groups/.htaccess")) {
				
				$message .= "<div style=\"font-weight:bold; color:#006600;\">cms_groups .htaccess file setup successfully.</div>\n";
				$success++;
				
			} else {	
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Unable to setup cms_groups .htaccess file.</div>\n";
				
			}
			
			//rename repository htaccess
			if(rename("$script_directory/assets/repository/_htaccess", "$script_directory/assets/repository/.htaccess")) {
				
				$message .= "<div style=\"font-weight:bold; color:#006600;\">repository .htaccess file setup successfully.</div>\n";
				$success++;
				
			} else {	
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Unable to setup repository .htaccess file.</div>\n";
				
			}
			
			//rename logs htaccess
			if(rename("$script_directory/assets/logs/_htaccess", "$script_directory/assets/logs/.htaccess")) {
				
				$message .= "<div style=\"font-weight:bold; color:#006600;\">logs .htaccess file setup successfully.</div>\n";
				$success++;
				
			} else {	
				
				$message .= "<div style=\"font-weight:bold; color:#660000;\">Unable to setup logs .htaccess file.</div>\n";
				
			}
			
		} else {
			
			$message .= "<div style=\"font-weight:bold; color:#660000;\">Error creating database: ". mysql_error() . "</div>";
			
		}
		
	} else {
		
		$message .= "<div style=\"font-weight:bold; color:#660000;\">Unable to connect to database server: $servername</div>";
		
	}
	
	$message .= "</div>";
	
	if ($success == 16) {
		
		$message .= "<div style=\"padding:5px; background:#006600; color:#ffffff; border-right:1px solid #006600; border-bottom:1px solid #006600; border-left:1px solid #006600;\">Your SoNet setup appears to have completed successfully! <a style=\"color:#ffffff;\" href=\"assets/_delete_me.php\">Click here</a> to choose an administrative username and password and initiate the installation clean-up process. Please note that you must manually delete the file: <i>_delete_me.php</i> from your server.</div>";
		
	}
	
}

$servername = htmlentities(unsanitize_string($servername));
$database = htmlentities(unsanitize_string($database));
$dbusername = htmlentities(unsanitize_string($dbusername));

if (trim($submit) == "") {
	
	if (trim($servername) == "") {$servername = "localhost";}
	if (trim($database) == "") {$database = "sonet";}

	
}
//display default setup screen
print <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<title>SoNet Setup</title>

<style>

body {
	
	font-family:Arial;
	font-size:10pt;
	margin:20px;
	background:#ffffff;
	color:#000000;
	
}

td {
	
	font-family:Arial;
	font-size:10pt;
	
}

input {
	
	font-family:Arial;
	font-size:10pt;
	
}

.logo img {
	
	display:block;
	
}

</style>

</head>
<body>
	<div style="width:627px; margin-left:auto; margin-right:auto;">
		<div class="logo"><img src="sonet_setup.jpg" border="0"></div>
		$message <!-- $success -->
		<br><b>Thank you for using SoNet!</b>
		<br>
		<br>SoNet is a unique content management system that combines the consumable content model with various aspects of the user-generated content model. This is accomplished by exposing visitors to new content published by your organization, and then encouraging feedback and discussion while steering those contributions towards the overall theme of the website.
		<br>
		<br>This system provides a variety of interactive features for your members to enjoy, such as: user profiles, blogs, commenting, voting, image galleries, and friending. SoNet will even allow users to create their own groups that contain message boards, which other members can then join and post topics and messages to, further contributing relevant content to your website.
		<br>
		<br><b>System Requirements:</b>
		<ul>
		<li>PHP 5.2 with Zend Optimizer
		<li>MySQL 5.0
		<li>Ability to add/overwrite/customize your .htaccess file
		<li>Your php.ini file must have auto register globals and magic quotes set to off.
		</ul>
		To install SoNet you must supply the name of an existing MySQL database along with a corresponding database username and password for a database user with full privileges. (i.e. the user you supply should be able to create tables and temp tables, as well as perform various insert, update, and delete operations)
		<br>
		<br>
		<div style="background:#dbdbdb; border:1px solid #8a8a8a;">
			<form style="margin:0;" action="index.php" method="post">
				<table cellpadding="5" cellspacing="0" border="0">
					<tr><td colspan="2"><b>Please provide the details for an existing database that SoNet can use:</b></td></tr>
					<tr><td nowrap>Database Server:</td><td width="100%"><input type="text" name="servername" value="$servername">$serverNameMessage</tr>
					<tr><td nowrap>Database Name:</td><td width="100%"><input type="text" name="database" value="$database">$dbNameMessage</tr>
					<tr><td nowrap>Database Username:</td><td width="100%"><input type="text" name="dbusername" value="$dbusername">$dbUsernameMessage</td></tr>
					<tr><td nowrap>Database Password:</td><td width="100%"><input type="password" name="dbpassword">$dbPasswordMessage</td></tr>
					<tr><td colspan="2"></td></tr>
					<tr><td colspan="2"><b>Please select a timezone:</b></b></td></tr>
					<tr><td nowrap>Timezone:</td><td width="100%"><select name="timezone">$timezoneOptions</select>$timezoneMessage</td></tr>
					<tr><td colspan="2"></td></tr>
					<tr><td colspan="2"><b>Please provide an e-mail address that SoNet can use for both incoming and outgoing messages:</b></td></tr>
					<tr><td nowrap>Site E-mail Address:</td><td width="100%"><input type="text" name="emailaddress" value="$emailaddress">$emailAddressMessage</tr>
					<tr><td colspan="2"></td></tr>
					<tr><td colspan="2"><b>Please provide your company or website name and legal location for the terms and conditions:</b></b></td></tr>
					<tr><td nowrap>Website or Company:</td><td width="100%"><input type="text" name="ourwebsite" value="$ourwebsite">$ourWebsiteMessage</td></tr>
					<tr><td nowrap>Legal Location:</td><td width="100%"><input type="text" name="ourlocation" value="$ourlocation">$ourLocationMessage</td></tr>
					<tr><td colspan="2"></td></tr>
					<tr><td colspan="2"><b>Please provide a mailing address for the contact form (optional):</b></b></td></tr>
					<tr><td nowrap>Mail to:</td><td width="100%"><input type="text" name="mailto" value="$mailto"></td></tr>
					<tr><td nowrap>Street:</td><td width="100%"><input type="text" name="street" value="$street"></td></tr>
					<tr><td nowrap>City, State and Zip:</td><td width="100%"><input type="text" name="citystatezip" value="$citystatezip"></td></tr>
					<tr><td colspan="2"></td></tr>
					<tr><td colspan="2"><b>Please provide some alternative URLs for your organization such as social networking profiles, bookmark sites, etc. This is also displayed on the contact form and is optional:</b></b></td></tr>
					<tr><td nowrap>Alternate URL 1:</td><td width="100%">URL: <input type="text" name="url1" value="$url1"> Text:<input type="text" name="urlText1" value="$urlText1"></td></tr>
					<tr><td nowrap>Alternate URL 2:</td><td width="100%">URL: <input type="text" name="url2" value="$url2"> Text:<input type="text" name="urlText2" value="$urlText2"></td></tr>
					<tr><td nowrap>Alternate URL 2:</td><td width="100%">URL: <input type="text" name="url3" value="$url3"> Text:<input type="text" name="urlText3" value="$urlText3"></td></tr>
					<tr><td colspan="2"><input type="submit" name="submit" value="Run Setup"></td></tr>
				</table>
			</form>
		</div>
	</div>
</body>
</html>
EOF;

?>