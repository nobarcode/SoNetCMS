<?php

include("connectDatabase.inc");
include("part_session.php");
include("requestVariableSanitizer.inc");
include("class_file_user_group_validator.php");
	
$load = sanitize_string($_REQUEST['load']);
$w = sanitize_string($_REQUEST['w']);
$h = sanitize_string($_REQUEST['h']);
$thumbs = sanitize_string($_REQUEST['thumbs']);

//get file system path and file security
$result = mysql_query("SELECT fsPath, owner, security FROM fileManager WHERE wwwPath = '{$load}'");
$row = mysql_fetch_object($result);
$fsPath = $row->fsPath;
$owner = $row->owner;
$security = $row->security;

loadFileData($fsPath, $owner, $security, $w, $h, $thumbs);

function returnMIMEType($load) {
	
	//get just the extension
	preg_match("|\.([a-z0-9]{2,4})$|i", $load, $ext);

	switch(strtolower($ext[1])) {
		
		case "jpg" :
		case "jpeg" :
		case "jpe" :
			return "image/jpeg";
		
		case "png" :
		case "gif" :
		case "bmp" :
		case "tiff" :
			return "image/".strtolower($ext[1]);
		
		case "xml" :
			return "application/xml";
		
		case "doc" :
			return "application/msword";
		
		case "docx" :
			return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
		
		case "xls" :
		case "xlt" :
		case "xlm" :
		case "xld" :
		case "xla" :
		case "xlc" :
		case "xlw" :
		case "xll" :
			return "application/vnd.ms-excel";
		
		case "xlsx" :
			return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
		
		case "ppt" :
		case "pps" :
			return "application/vnd.ms-powerpoint";
		
		case "pptx" :
			return "application/vnd.openxmlformats-officedocument.presentationml.presentation";
			
		case "rtf" :
			return "application/rtf";
		
		case "pdf" :
			return "application/pdf";
		
		case "html" :
		case "htm" :
			return "text/html";
		
		case "js" :
			return "application/x-javascript";
		
		case "css" :
			return "text/css";
		
		case "json" :
			return "application/json";
		
		case "php" :
			return "text/x-php";
		
		case "txt" :
			return "text/plain";
		
		case "mp3" :
			return "audio/mpeg3";
		
		case "wav" :
			return "audio/wav";
		
		case "aiff" :
		case "aif" :
			return "audio/aiff";
		
		case "swf" :
		return "application/x-shockwave-flash";
		
		case "mpeg" :
		case "mpg" :
		case "mpe" :
			return "video/mpeg";
		
		case "flv" :
			return "video/x-flv";
		
		case "avi" :
			return "video/msvideo";
		
		case "wmv" :
			return "video/x-ms-wmv";
		
		case "mov" :
			return "video/quicktime";
		
		case "zip" :
			return "application/zip";
		
		case "tar" :
			return "application/x-tar";
		
		default :
			return "unknown/" . trim($ext[0], ".");
		
	}
	
}

function loadFileData($load, $owner, $security, $w, $h, $thumbs) {
	
	$mime = returnMIMEType($load);
	
	//if this is an image
	if (stristr($mime, "image/")) {
		
		//check security
		if ($security == "private" && $owner != $_SESSION['username']) {
			
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: " . gmdate("D, d M Y H:i:s", time() - 86400) . " GMT");
			header("Content-Type: image/jpeg");
			header("Content-Disposition: inline; filename=\"" . basename($load) . "\"");
			echo placeHolderImage($load, $w, $h, $thumbs, $security);
			exit;
			
		} elseif ($security == "authenticated" && trim($_SESSION['username']) == "") {
			
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: " . gmdate("D, d M Y H:i:s", time() - 86400) . " GMT");
			header("Content-Type: image/jpeg");
			header("Content-Disposition: inline; filename=\"" . basename($load) . "\"");
			echo placeHolderImage($load, $w, $h, $thumbs, $security);
			exit;
			
		} elseif ($security == "friends") {
			
			$result = mysql_query("SELECT friend FROM friends WHERE owner = '{$owner}' && friend = '{$_SESSION['username']}'");
			
			if (mysql_num_rows($result) < 1 && $owner != $_SESSION['username']) {
				
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: " . gmdate("D, d M Y H:i:s", time() - 86400) . " GMT");
				header("Content-Type: image/jpeg");
				header("Content-Disposition: inline; filename=\"" . basename($load) . "\"");
				echo placeHolderImage($load, $w, $h, $thumbs, $security);
				exit;
				
			}
			
		}
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($load));
		if (!$userGroup->allowRead()) {
			
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: " . gmdate("D, d M Y H:i:s", time() - 86400) . " GMT");
			header("Content-Type: image/jpeg");
			header("Content-Disposition: inline; filename=\"" . basename($load) . "\"");
			echo placeHolderImage($load, $w, $h, $thumbs, 'private');
			exit;
			
		}
		
		//display image if all security checks pass
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: " . gmdate("D, d M Y H:i:s", time() - 86400) . " GMT");
		header("Content-Type: image/jpeg");
		header("Content-Disposition: inline; filename=\"" . basename($load) . "\"");
		echo resize($load, $w, $h, $thumbs);
		exit;
		
	//if this flash load it up without forcing a download or anything else fancy
	} elseif (stristr($mime, "x-shockwave-flash")) {
		
		//display file contents if all security checks pass
		header("Content-Type: $mime");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: public");
		header("Content-Disposition: inline; filename=\"" . basename($load) . "\"");
		header("Content-length: " . filesize($load));
		ob_clean();
		flush();
		return readfile($load);
		
	//if this any other type of file, try to force a download
	} else {
				
		//check security
		if ($security == "private" && $owner != $_SESSION['username']) {
			
			$_SESSION['status'] = array('private', $load, $owner);
			header("Location: /status.php?type=file");
			exit;
			
		} elseif ($security == "authenticated" && trim($_SESSION['username']) == "") {
			
			$_SESSION['status'] = array('authenticated', $load, $owner);
			header("Location: /status.php?type=file");
			exit;
			
		} elseif ($security == "friends") {
			
			$result = mysql_query("SELECT friend FROM friends WHERE owner = '{$owner}' && friend = '{$_SESSION['username']}'");
			
			if (mysql_num_rows($result) < 1 && $owner != $_SESSION['username']) {
				
				$_SESSION['status'] = array('friends', $load, $owner);
				header("Location: /status.php?type=file");
				exit;
				
			}
			
		}
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($load));
		if (!$userGroup->allowRead()) {
			
			$_SESSION['status'] = array('user group access', $load, $owner);
			header("Location: /status.php?type=file");
			exit;
			
		}
		
		//display file contents if all security checks pass
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment; filename=\"" . basename($load) . "\"");
		header("Content-Transfer-Encoding: binary");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: public");
		header("Content-length: " . filesize($load));
		ob_clean();
		flush();
		return readfile($load);
		
	}
	
}

function placeHolderImage($loadImage, $w, $h, $thumbs, $security) {
	
	if ($thumbs == "true") {
		
		$path_parts = pathinfo($loadImage);
		$loadImage = $path_parts['dirname'] . "/_thumbs/" . $path_parts['basename'] . ".jpg";
		
		
	}
	
	list($sourceWidth, $sourceHeight) = getimagesize($loadImage);
	
	if ((trim($w) != "" && $sourceWidth > $w) || (trim($h) != "" && $sourceHeight > $h)) {

		$height = ceil($sourceHeight / $sourceWidth * $w);
		
		if (trim($h) != "" && $height > $h) {
			
			$width = ceil($sourceWidth / $sourceHeight * $h);
			$height = $h;
			
		} else {
			
			$width = $w;
			
		}
		
	} else {
		
		$width = $sourceWidth;
		$height = $sourceHeight;
		
	}
	
	include("assets/core/config/file_settings.php");
	
	$x = ($width / 2) + $messageHorizontalOffset;
	$y = ($height / 2) + $messageVerticalOffset;
	
	// Create a blank image and add some text
	$im = imagecreatetruecolor($width, $height);
	imagefilledrectangle($im, 0, 0, $width, $height, imagecolorallocate($im, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]));
	imagettftext($im, $fontSize, 0, $x, $y, imagecolorallocate($im, $messageTextColor[0], $messageTextColor[1], $messageTextColor[2]), $font, $message);
	
	// Output the image
	$showImage = imagejpeg($im, '', 98);
	
	// Free up memory
	imagedestroy($im);
	
	return $showImage;
	
}

function resize($loadImage, $w, $h, $thumbs) {
	
	//check if GD extension is loaded
	if (!extension_loaded('gd') && !extension_loaded('gd2')) {
		
		trigger_error("GD is not loaded", E_USER_WARNING);
		return false;
		
	}
	
	//get image size info
	
	//sourceType:
	//1 => 'GIF',
	//2 => 'JPG',
	//3 => 'PNG',
	//4 => 'SWF',
	//5 => 'PSD',
	//6 => 'BMP',
	//7 => 'TIFF(intel byte order)',
	//8 => 'TIFF(motorola byte order)',
	//9 => 'JPC',
	//10 => 'JP2',
	//11 => 'JPX',
	//12 => 'JB2',
	//13 => 'SWC',
	//14 => 'IFF',
	//15 => 'WBMP',
	//16 => 'XBM'
	
	list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($loadImage);
	
	switch ($sourceType) {

		case 1: $image = imagecreatefromgif($loadImage); break;
		case 2: $image = imagecreatefromjpeg($loadImage); break;
		case 3: $image = imagecreatefrompng($loadImage); break;
		default: trigger_error('Unsupported filetype!', E_USER_WARNING); exit;

	}
	
	if ($thumbs == "true") {
		
		$path_parts = pathinfo($loadImage);
		$getFile = $path_parts['dirname'] . "/_thumbs/" . $path_parts['basename'] . ".jpg";
		
		//if not an image get file contents
		$handle = fopen($getFile, "rb");
		return fpassthru($handle);
		
	}
	
	if ((trim($w) != "" && $sourceWidth > $w) || (trim($h) != "" && $sourceHeight > $h)) {

		$thumbHeight = ceil($sourceHeight / $sourceWidth * $w);
		
		if (trim($h) != "" && $thumbHeight > $h) {
			
			$thumbWidth = ceil($sourceWidth / $sourceHeight * $h);
			$thumbHeight = $h;
			
		} else {
			
			$thumbWidth = $w;
			
		}
		
		$thumbnailImage = imagecreatetruecolor($thumbWidth, $thumbHeight); 
		
		imagecopyresampled($thumbnailImage, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);
		
		$showImage = imagejpeg($thumbnailImage, '', 100); 
		
		
	} else {
		
		$showImage = imagejpeg($image, '', 100);
		
	}
	
	return $showImage;
	
}

?>