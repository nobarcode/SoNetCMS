<?

//if the user was bounced here because they attempted to access something that requires a sign-in, display the following message
if ($sr == 1) {
	
	$signInRequired = "<div id=\"sign_in_required\">";
	$signInRequired .= "	<b>You must be signed in to access some areas of this site.</b><ul><li>If you're already a member, please sign in below.<li>If you've forgotten your password, click the &quot;Reset Password&quot; link below.<li>If you don't have an account yet, <a href=\"signUp.php?jb=$jb\"><b>sign up here</b></a> to get started!</ul>";
	$signInRequired .= "</div>";
	
}

//NOTE: The hidden form fields are important and should not be modified, as is the parameter "jb" that is passed in an URLs on this page. Changing or removing these will produce unexpected results.

print <<< EOF
			$signInRequired
			$message
			<div class="subheader_title">Sign In</div>
			<div id="form_container">
				<form id="loginForm" name="loginForm" action="/signIn.php" method="post" enctype="multipart/form-data">
				<table border="0" cellspacing="0" cellpadding="2">
					<tr valign="center">
						<td width="60">Username:</td><td><input type="text" id="username" name="username" value="$username"></td>
					</tr>
					<tr valign="center">
						<td width="60">Password:</td><td><input type="password" id="password" name="password"></td>
					</tr>
					<tr valign="center">
						<td width="60"></td><td><a href="forgotPassword.php?jb=$jb">Reset Password</a></td>
					</tr>
				</table>
				<div class="form_buttons"><input type="submit" value="Login"></div>
				<input type="hidden" name="loginSubmit" value="true">
				<input type="hidden" name="jb" value="$jbHtml">
				<input type="hidden" name="return_url" value="$return_url">
				</form>
			</div>
EOF;

?>