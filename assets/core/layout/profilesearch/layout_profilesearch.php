<?

//access the profile search field properties from the global config file using: $profileField[race], $profileField[gender], $profileField[height], $profileField[bodytype], $profileField[orientation], $profileField[religion], $profileField[vices], $profileField[herefor]

print <<< EOF
			<div class="search_options_container">
				<form id="profile_search" method="get" action="ajaxProfileSearch.php">
				<div style="width:290px; float:left;">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Username:</td><td width="100%"><input type="text" id="username" name="username" size="32" value="$username"></td></tr>
					<tr valign="center"><td nowrap>Name:</td><td width="100%"><input type="text" id="name" name="name" size="32" value="$name"></td></tr>
					<tr valign="center"><td nowrap>E-mail:</td><td width="100%"><input type="text" id="email" name="email" size="32" value="$email"></td></tr>
					<tr valign="center"><td nowrap>Company:</td><td width="100%"><input type="text" id="company" name="company" size="32" value="$company" size="32"></td></tr>
					<tr valign="center"><td nowrap>Profession:</td><td width="100%"><input type="text" id="profession" name="profession" size="32" value="$profession" size="32"></td></tr>
					<tr valign="center"><td nowrap>City:</td><td width="100%"><input type="text" id="city" name="city" size="32" value="$city"></td></tr>
					<tr valign="center"><td nowrap>State:</td><td width="100%"><input type="text" id="state" name="state" size="32" value="$state"></td></tr>
					<tr valign="center"><td nowrap>Zip:</td><td width="100%"><select id="radius" name="radius"><option value="5">5</option><option value="15">15</option><option value="25">25</option><option value="50">50</option><option value="100">100</option><option value="250">250</option><option value="500">500</option><option value="1000">1000</option></select> miles of: <input type="text" id="zip" name="zip" size="9" value="$zip"></td></tr>
					<tr valign="center"><td nowrap>Country:</td><td width="100%"><select name="country"><option value=""></option>$country</select></td></tr>
					<tr valign="center"><td nowrap>Order By:</td><td width="100%"><input type="radio" name="orderBy" value="lastLogin" checked> Last Login <input type="radio" name="orderBy" value="username"> Username</td></tr>
				</table>
				</div>
				<div style="width:290px; float:left; margin-left:10px;">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<tr valign="center"><td nowrap>Age:</td><td><input type="text" id="minAge" name="minAge" size="2" value="$minAge"> to <input type="text" id="maxAge" name="maxAge" size="2" value="$maxAge"></td></tr>
EOF;
						if ($profileField[race] == 'true') {print "<tr valign=\"center\"><td nowrap>Race:</td><td width=\"100%\"><select name=\"race\" name=\"race\"><option value=\"\"></option>$race</select></td></tr>";}
						if ($profileField[gender] == 'true') {print "<tr valign=\"center\"><td nowrap>Gender:</td><td width=\"100%\"><select name=\"gender\" name=\"gender\"><option value=\"\"></option>$gender</select></td></tr>";}
						if ($profileField[height] == 'true') {print "<tr valign=\"center\"><td nowrap>Height:</td><td width=\"100%\"><select id=\"minHeightFeet\" name=\"minHeightFeet\"><option value=\"\"></option>$heightFeet</select> <select id=\"minHeightInches\" name=\"minHeightInches\"><option value=\"\"></option>$heightInches</select> to <select id=\"maxHeightFeet\" name=\"maxHeightFeet\"><option value=\"\"></option>$heightFeet</select> <select id=\"maxHeightInches\" name=\"maxHeightInches\"><option value=\"\"></option>$heightInches</select></td></tr>";}
						if ($profileField[bodytype] == 'true') {print "<tr valign=\"center\"><td nowrap>Body Type:</td><td width=\"100%\"><select name=\"bodyType\" name=\"bodyType\"><option value=\"\"></option>$bodyType</select></td></tr>";}
						if ($profileField[orientation] == 'true') {print "<tr valign=\"center\"><td nowrap>Orientation:</td><td width=\"100%\"><select name=\"orientation\" name=\"orientation\"><option value=\"\"></option>$orientation</select></td></tr>";}
						if ($profileField[religion] == 'true') {print "<tr valign=\"center\"><td nowrap>Religion:</td><td width=\"100%\"><select id=\"religion\" name=\"religion\"><option value=\"\"></option>$religion</select></td></tr>";}
						if ($profileField[vices] == 'true') {print "<tr valign=\"top\"><td nowrap>Vices:</td><td width=\"100%\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"form_options\">Smoke?</td><td class=\"form_options form_sub_option_spacer\"><select id=\"smoke\" name=\"smoke\"><option value=\"\"></option>$smoke</select></td></tr><tr><td>Drink?</td><td class=\"form_sub_option_spacer\"><select id=\"drink\" name=\"drink\"><option value=\"\"></option>$drink</select></td></tr></table></td></tr>";}
print <<< EOF
				</table>
				</div>
				<div style="width:290px; float:left; margin-left:10px;">
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
EOF;
				if ($profileField[herefor] == 'true') {print "<tr valign=\"top\"><td nowrap>Here For:</td><td width=\"100%\">$hereFor</td></tr>";}
print <<< EOF

					<tr valign="center"><td nowrap>About:</td><td width="100%"><input type="text" id="about" name="about" size="32" value="$about"></td></tr>
					<tr valign="center"><td nowrap>Interests:</td><td width="100%"><input type="text" id="interests" name="interests" size="32" value="$interests"></td></tr>
				</table>
				</div>
				<div class="form_buttons"><input type="submit" id="submit" value="Search"></div>
				</form>
			</div>
EOF;

?>