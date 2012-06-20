<?php

//The "unsanitize_string" function should be used in place of PHP < 6.x's
//"stripslashes()" function, as there is no way to know how it will work in
//PHP v6, or if it will even be available for that matter.
//The "sanitize_string" function can be used in place of PHP's
//"addslashes()" function.

function sanitize_string($string) {
	
	//If this is an "array in array" run this function again on the array inside the array (i.e. "form element arrays")
	if(is_array($string)) {
		
		return array_map('sanitize_string', $string);
		
	}
	
	$search = array("\\","\0","\n","\r","\x1a","'","\"");
	$replace = array("\\\\","\\0","\\n","\\r","\Z","\'","\\\"");
	
	return str_replace($search, $replace, $string);
	
}

function unsanitize_string($string) {
	
	//If this is an "array in array" run this function again on the array inside the array (i.e. "form element arrays")
	if(is_array($string)) {
		
		return array_map('unsanitize_string', $string);
		
	}
	
	$search = array("\\\\","\\0","\\n","\\r","\Z","\'","\\\"");
	$replace = array("\\","\0","\n","\r","\x1a","'","\"");
	
	return str_replace($search, $replace, $string);
	
}

//automatically build global variables from request/post/get data is disabled for obvious reasons - but it's here if needed
//$_REQUEST = array_map('sanitize_string', $_REQUEST);
//$_GET = array_map('sanitize_string', $_GET);
//$_POST = array_map('sanitize_string', $_POST);
//$_COOKIE = array_map('sanitize_string', $_COOKIE);
//$_FILES = array_map('sanitize_string', $_FILES);

?>