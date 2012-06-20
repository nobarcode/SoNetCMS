<?php

/**
 *	Filemanager PHP connector configuration
 *
 *	filemanager.config.php
 *	config for the filemanager.php connector
 *
 *	@license	MIT License
 *	@author		Riaan Los <mail (at) riaanlos (dot) nl>
 *	@author		Simon Georget <simon (at) linea21 (dot) com>
 *	@copyright	Authors
 */

/**
 *	Language settings
 */
$config['culture'] = 'en';

/**
 *	PHP date format
 *	see http://www.php.net/date for explanation
 */
$config['date'] = 'm/d/Y h:i A';

/**
 *	Icons settings
 */
$config['icons']['path'] = 'images/fileicons/';
$config['icons']['directory'] = '_Open.png';
$config['icons']['default'] = 'default.png';

/**
 *	Used to tell the script to display image thumbnails instead of icons.
 */
$config['images'] = array('jpg', 'jpeg','gif','png'); // Anything in this array will display a thumbnail where applicable.

/**
 *	Files and folders to excluded from filtree
 */
$config['unallowed_files']= array('.htaccess');
$config['unallowed_dirs']= array('_thumbs');

/**
 *	File system and http/www root path settings
 */
$config['alwaysRefreshFileManagerDatabase'] = true; //setting this to true will cause the filemanager to always check if a file has entry in the database; if it doesn't one is created with the default setting of: owner = current user; security = public; group = NULL
$config['sys_root'] = $_SESSION['sysRootPath']; // This is the file system root. Use either session variables (as shown) to set the file system root path on a per-user basis, or provide a static string such as "/var/www/html/assets/user_files/user1". This is used for internal file operations. No end slash.
$config['www_root'] = $_SESSION['wwwRootPath']; // This is the http or www root. Use either session variables (as shown) to set the root path on a per-user basis, or provide a static string such as "/assets/user_files/user1". This is used to reference images and files when the value is passed back to editors/callback scripts. No end slash.

/**
 *	Upload and disk operation settings
 */
$config['upload']['overwrite'] = false; // True or false; Check if filename exists. If false, index will be added
$config['upload']['size'] = false; // Integer or false; maximum file size in Mb; please note that every server has got a maximum file upload size as well.
$config['upload']['maxdisk'] = $_SESSION['maxDiskSpace']; // Integer (measured in MB) or false; This is the maximum disk space allotted for all files in all subdirectories under the sys_root directory. It is checked before final file upload operations complete. Setting it to false means there is no limit. Use session variables here to set it on a per-user basis.
//$config['upload']['allowedExtensions'] = array("avi","css","csv","doc","docx","flv","gif","htm","html","jpg","jpeg","mov","pdf","png","ppt","rtf","swf","txt","wmv","xls","xml"); //This is an array containing allowed file extensions. Supplying an asterisk (*) in the array makes all file extensions valid.

//Set allowed file extensions based on a session variable containing the user's "user level". You can also just hardcode it like the commented-out example above.
if($_SESSION['userLevel'] != 1 && $_SESSION['userLevel'] != 2 && $_SESSION['userLevel'] != 3 && $_SESSION['userLevel'] != 4) {
	
	$config['upload']['allowedExtensions'] = array("avi","css","csv","doc","docx","flv","gif","htm","html","jpg","jpeg","mov","pdf","png","ppt","rtf","swf","txt","wmv","xls","xlsx", "xml");
	
} else {
	
	$config['upload']['allowedExtensions'] = array("*");
	
}

/**
 *	Authentication settings for Vhost or outside files & folders
 */
$config['require_authentication'] = true; // true or false; If set to true, this will cause filemanager to check if $_SESSION['isLoggedIn'] is set to true.

?>