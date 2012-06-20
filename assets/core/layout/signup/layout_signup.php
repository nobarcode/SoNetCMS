<?

print <<< EOF
			$showMessage
			<div id="column_container">
				<div id="left_column">
					<div class="subheader_title">
						Welcome to 
EOF;

print preg_replace("/^www\.{1}/i", "", $_SERVER['HTTP_HOST']);

print "!";
print <<< EOF
					</div>
					<div id="main_body">
						<b>It's easy to become a member. Just fill out the fields on the right and you'll be able to:</b>
						<ul>
							<li>Create your own profile.
							<li>Start your own blog.
							<li>Comment on articles and blogs.
							<li>Create your own image gallery.
							<li>View other member profiles.
							<li>View other member image galleries.
							<li>Add members to your friends list.
							<li>Start your own group.
							<li>Much much more!
						</ul>
					</div>
				</div>
				<div id="right_column">
					$showSignUpForm
				</div>
			</div>
EOF;

?>