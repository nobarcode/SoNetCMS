<?

//load user's personal details: showPersonalDetails([profile title text], [imageSize-X], [imageSize-Y])
$showPersonalDetails = showPersonalDetails($row, 'Profile', 270, 370);

//load user's about me info: showAboutMe($row, '[top title text]')
$showAboutMe = showAboutMe($row, 'About Me');

//load user's interests: showInterests($row, '[top title text]')
$showInterests = showInterests($row, 'My Interests');

//load user's groups: showGroups('[top title text]')
$showGroups = showGroups('My Groups');

//load user's friends: showFriends('[top title text]')
$showFriends = showFriends('My Friends');

//load user's latest blogs: showBlog('[top title text]')
$showBlog = showBlog('My Blog');

//load user's profile comments: showComments('[top title text]')
$showComments = showComments($row, 'Comments');

print <<< EOF
$showPersonalDetails
$showAboutMe
$showInterests
$showFriends
<div class="clear_right"></div>
$showGroups
$showBlog 
<div class="clear_right"></div>
$showComments 
EOF;

?>