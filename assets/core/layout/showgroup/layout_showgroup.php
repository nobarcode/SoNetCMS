<?

//load group info: showGroupInfo($groupId, $jb, [imageSize-X], [imageSize-Y])
$showGroupInfo = showGroupInfo($groupId, $jb, 450, 450);

//load events: showEventList($groupId, '[top title text]')
$showEventList = showEventList($groupId, 'Upcoming Events');

//load latest conversations: showLatestConversations($groupId, '[top title text]')
$showLatestConversations = showLatestConversations($groupId, 'Member Chatter');

//load latest blogs: showLatestBlogs($groupId, '[top title text]')
$showLatestBlogs = showLatestBlogs($groupId, 'Latest Member Blogs');

//load gallery images: showcaseGallery($groupId, '[top title text]')
$showcaseGallery = showcaseGallery($groupId, 'Our Gallery');

print <<< EOF
$showGroupInfo
<div id="left_column_container">
$showLatestConversations
</div>

<div id="right_column_container">
$showEventList
$showLatestBlogs
$showcaseGallery
</div>
EOF;

?>