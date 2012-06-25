<?php

//image settings
$backgroundColor = array(216, 216, 216);
$messageTextColor = array(64, 64, 64);
$font = '/assets/core/resources/fonts/Microsoft_Sans_Serif.ttf';

//when an image is detected and the image cannot be displayed due to its security settings, a message is displayed in place of the detected image
if ($security == "private" || $security == "authenticated") {
	
	if ($width <= 300) {
		
		$fontSize = 8;
		$messageHorizontalOffset = -48;
		$messageVerticalOffset = 3;
		
	}
	
	if ($width > 300) {
		
		$fontSize = 12;
		$messageHorizontalOffset = -68;
		$messageVerticalOffset = 4;
		
	}
	
	if ($width > 600) {
		
		$fontSize = 16;
		$messageHorizontalOffset = -88;
		$messageVerticalOffset = 6;
		
	}
	
	$message = "Image Unavailable";
	
} elseif ($security == "friends") {
	
	if ($width <= 300) {
		
		$fontSize = 8;
		$messageHorizontalOffset = -34;
		$messageVerticalOffset = 3;
		
	}
	
	if ($width > 300) {
		
		$fontSize = 12;
		$messageHorizontalOffset = -54;
		$messageVerticalOffset = 4;
		
	}
	
	if ($width > 600) {
		
		$fontSize = 16;
		$messageHorizontalOffset = -78;
		$messageVerticalOffset = 6;
		
	}
	
	$message = "Friends Only";
	
}

?>