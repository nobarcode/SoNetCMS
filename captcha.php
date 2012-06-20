<?php

//start the session and use to store the captcha
session_start();

$characterChoices = array("2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "g", "h", "i", "k", "m", "n", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"); 

for ($i = 0; $i < 5; $i++) {
	
	$string[$i] = $characterChoices[rand(0,(count($characterChoices)-1))]; 
	$captcha_string .= $string[$i]; 
	
}

//encrypt and store the key inside a session
$_SESSION['captchaKey'] = md5($captcha_string);

//use existing image for a background 100 x 28 px
$captcha = imagecreatefrompng("./assets/core/resources/images/captcha.png");

//tt font
$font = '/assets/core/resources/fonts/Microsoft_Sans_Serif.ttf';

//create a series of random dots all over the background image 1000 times
for ($i = 0; $i < 1000; $i++) {
	
	//random x/y position
	$x = rand(0, 100);
	$y = rand(0, 28);
	
	//random color
	$red = round(rand(63, 255));
	$grn = round(rand(63, 255));
	$blu = round(rand(63, 255));
	$color = imagecolorallocate($captcha, $red, $grn, $blu);
	
	//set dot
	imagesetpixel($captcha, round($x),round($y), $color);
	
}

imagefilter($captcha, IMG_FILTER_GAUSSIAN_BLUR);

//set color
$black = imagecolorallocate($captcha, 0, 0, 0);

//set the default x position of the text
$x = 4;

//write randomly generated string to the image
for ($i = 0; $i < 5; $i++ ) {
	
	//random x/y positions
	$x += round(rand(2, 6));
	$y = round(rand(18, 22));
	$angle = round(rand(-24, 24));
	
	//print the first character from the string to the image
	imagettftext($captcha, 16, $angle, $x, $y, $black, $font, $string[$i]);
	
	$x+= 12;
	
}

//output the image
header("Content-type: image/png");
imagepng($captcha);

?>