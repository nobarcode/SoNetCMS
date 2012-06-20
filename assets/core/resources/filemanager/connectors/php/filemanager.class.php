<?php
/**
 *	Filemanager PHP class
 *
 *	filemanager.class.php
 *	class for the filemanager.php connector
 *
 *	@license			MIT License
 *	@author				Riaan Los <mail (at) riaanlos (dot) nl>
 *	@author				Simon Georget <simon (at) linea21 (dot) com>
 *	@copyright			Authors
 *
 *	@forked & modified	Nathan Moch <no (dot) barcode (at) gmail (dot) com> 12/2010
 *
 */

class Filemanager {

	protected $config = array();
	protected $language = array();
	protected $get = array();
	protected $post = array();
	protected $properties = array();
	protected $item = array();
	protected $root = '';
	protected $sys_root = '';
	protected $www_root = '';

	public function __construct($config) {
		
		$this->config = $config;
		$this->root = str_replace('connectors'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'filemanager.class.php','',__FILE__);
		$this->properties = array(
			'Date Created'=>null,
			'Date Modified'=>null,
			'Height'=>null,
			'Width'=>null,
			'Size'=>null
		);
		
		$this->setParams();
		
		//load language file
		$this->loadLanguageFile();
		
		//exit if require_authentication is set to true and the isLoggedIn seesion variable is not set to true
		if($this->config['require_authentication'] === true && $_SESSION['isLoggedIn'] !== true) {
			
			$this->error(sprintf($this->lang('AUTHORIZATION_REQUIRED')));
				
		}
		
		//check for sys_root setting. If it's not set then exit with an error.
		if(trim($this->config['sys_root']) != "") {
			
			$this->sys_root = $this->config['sys_root'];
			
		} else {
			
			//exit if no system root was defined in filemanager.config.php
			$this->error(sprintf($this->lang('NO_ROOT_ERROR')));
			
		}
		
		//check for www_root setting. If it's not set, use the sys_root setting. (this won't work well for most shared hosting environments)
		if(isset($this->config['www_root'])) {
			
			$this->www_root = $this->config['www_root'];
			
		} else {
			
			$this->www_root = $this->config['sys_root'];
			
		}
		
	}
	
	private function setParams() {
		
		$tmp = $_SERVER['HTTP_REFERER'];
		$tmp = explode('?',$tmp);
		
		$params = array();
		
		if(isset($tmp[1]) && $tmp[1]!='') {
			
			$params_tmp = explode('&',$tmp[1]);
			
			if(is_array($params_tmp)) {
				
				foreach($params_tmp as $value) {
					
					$tmp = explode('=',$value);
					
					if(isset($tmp[0]) && $tmp[0]!='' && isset($tmp[1]) && $tmp[1]!='') {
						
						$params[$tmp[0]] = $tmp[1];
						
					}
					
				}
				
			}
			
		}
		
		$this->params = $params;
		
	}
	
	public function error($string,$textarea=false) {
		
		$array = array(
		
			'Error'=>$string,
			'Code'=>'-1',
			'Properties'=>$this->properties
			
		);
		
		echo json_encode($array);
		
		die();
		
	}
	
	public function lang($string) {
		
		if(isset($this->language[$string]) && $this->language[$string]!='') {
			
			return $this->language[$string];
			
		} else {
			
			return 'Language string error on ' . $string;
			
		}
		
	}
	
	public function getvar($var) {
		
		if(!isset($_GET[$var]) || trim($_GET[$var]) == '') {
			
			$this->error(sprintf($this->lang('INVALID_VAR'),$var));
			
		} else {
			
			$this->get[$var] = $this->sanitize($_GET[$var]);
			
			return true;
			
		}
	}
	
	public function postvar($var) {
		
		if(!isset($_POST[$var]) || trim($_POST[$var]) == '') {
			
			$this->error(sprintf($this->lang('INVALID_VAR'),$var));
			
		} else {
			
			$this->post[$var] = $_POST[$var];
			
			return true;
			
		}
		
	}
	
	public function getinfo() {
		
		$this->item = array();
		$this->item['properties'] = $this->properties;
		$this->get_file_info();
		
		$full_path = $this->sys_root .$this->get['path'];

		$array = array(
			
			'Path'=> $this->get['path'],
			'WWW Path'=>$this->www_root . $this->get['path'],
			'Filename'=>$this->item['filename'],
			'File Type'=>$this->item['filetype'],
			'Security'=>$this->item['security'],
			'Display Groups'=>$this->item['displayGroups'],
			'Assigned Groups'=>$this->item['assignedGroups'],
			'Edit Groups'=>$this->item['editGroups'],
			'Preview'=>$this->item['preview'],
			'Properties'=>$this->item['properties'],
			'Error'=>"",
			'Code'=>0
			
		);
		
		return $array;
		
	}
	
	public function getfolder() {
		
		$array = array();
		
		$current_path = $this->sys_root . $this->get['path'];
		
		if(!is_dir($current_path)) {
			
			$this->error(sprintf($this->lang('FOLDER_DOES_NOT_EXIST'),$this->lang('display_root_as') . $this->get['path']));
			
		}
		
		if(!$files = scandir($current_path)) {
			
			$this->error(sprintf($this->lang('UNABLE_TO_OPEN_FOLDER'),$this->get['path']));
			
		} else {
			
			natcasesort($files);
			
			foreach($files as $file) {
				
				if($file != "." && $file != ".." && is_dir($current_path . $file)) {
					
					if(!in_array($file, $this->config['unallowed_dirs'])) {
						
						$array[$this->get['path'] . $file .'/'] = array(
							
							'Path'=> $this->get['path'] . $file .'/',
							'WWW Path'=>$this->www_root . $this->get['path'] . $file . '/',
							'Filename'=>$file,
							'File Type'=>'dir',
							'Preview'=> $this->config['icons']['path'] . $this->config['icons']['directory'],
							'Properties'=>array(

								'Date Created'=>null,
								'Date Modified'=>null,
								'Height'=>null,
								'Width'=>null,
								'Size'=>null
								
							),
							'Error'=>"",
							'Code'=>0
						);
						
					}
					
				} else if($file != "." && $file != ".." && !in_array($file, $this->config['unallowed_files'])) {
					
					$this->item = array();
					$this->item['properties'] = $this->properties;
					$this->get_file_info($this->get['path'] . $file);
					$array[$this->get['path'] . $file] = array(
						
						'Path'=>$this->get['path'] . $file,
						'WWW Path'=>$this->www_root . $this->get['path'] . $file,
						'Filename'=>$this->item['filename'],
						'File Type'=>$this->item['filetype'],
						'Security'=>$this->item['security'],
						'Thumbnail'=>$this->item['thumbnail'],
						'Preview'=>$this->item['preview'],
						'Properties'=>$this->item['properties'],
						'Error'=>"",
						'Code'=>0
						
					);
					
					if ($this->config['alwaysRefreshFileManagerDatabase'] === true) {
						
						//update filemanager database link
						$wwwPath = addslashes($this->www_root . $this->get['path'] . $this->item['filename']);
						$fsPath = addslashes($this->sys_root . $this->get['path'] . $this->item['filename']);
						
						$result = mysql_query("SELECT owner FROM fileManager WHERE fsPath = '{$fsPath}'");
						
						if (mysql_num_rows($result) < 1) {
							
							mysql_query("INSERT INTO fileManager (wwwPath, fsPath, owner, security, groupId) VALUES ('{$wwwPath}', '{$fsPath}', '{$_SESSION['username']}', 'public', '')");
							
						}
						
					}
					
				}
				
			}
			
		}
		
		return $array;
		
	}
	
	private function get_file_info($path='',$return=array()) {
	
		if($path == '') {
			
			$path = $this->get['path'];
			
		}
		
		//get file security
		$result = mysql_query("SELECT security FROM fileManager WHERE fsPath = '" . $this->sys_root . $path . "' LIMIT 1");
		$row = mysql_fetch_object($result);
		
		$this->item['security'] = $row->security;
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($this->sys_root . $path));
		if ($userGroup->allowEditing()) {
			
			$this->item['editGroups'] = 'true';
			
		} else {
			
			$this->item['editGroups'] = 'false';
			
		}
		
		//set the "display groups" flag and get file groups if the user is an admin
		if ($_SESSION['userLevel'] == 1 || $_SESSION['userLevel'] == 2 || $_SESSION['userLevel'] == 3 || $_SESSION['userLevel'] == 4) {
			
			$this->item['displayGroups'] = 'true';
			
			$result = mysql_query("SELECT userGroups.name FROM fileManager INNER JOIN userGroups ON userGroups.id = fileManager.groupId WHERE fileManager.fsPath = '" . $this->sys_root . $path . "' ORDER BY userGroups.name ASC");
			
			if (mysql_num_rows($result) > 0) {
				
				while ($row = mysql_fetch_object($result)) {
					
					$this->item['assignedGroups'] .= htmlentities($row->name) . "<br>";
					
				}
				
			} else {
				
				$this->item['assignedGroups'] = "No Groups Assigned";
				
			}
			
		}
		
		$tmp = explode('/',$path);
		
		if(is_dir($this->sys_root . $path)) {
			
			$this->item['filename'] = $tmp[(sizeof($tmp)-2)];
			
		} else {
		
			$this->item['filename'] = $tmp[(sizeof($tmp)-1)];
			
		}

		$tmp = explode('.',$this->item['filename']);
		$this->item['filetype'] = $tmp[(sizeof($tmp)-1)];
		$this->item['filemtime'] = filemtime($this->sys_root . $path);
		$this->item['filectime'] = filectime($this->sys_root . $path);

		$this->item['preview'] = $this->config['icons']['path'] . $this->config['icons']['default'];

		if(is_dir($this->sys_root . $path)) {
			 
			$this->item['preview'] = $this->config['icons']['path'] . $this->config['icons']['directory'];
			 
		} else if(in_array(strtolower($this->item['filetype']),$this->config['images'])) {
			
			$path_parts = pathinfo($path);
			$thumbnailFilename = basename($path_parts['basename'], '.' . $path_parts['extension']);
			
			$this->createThumbnail($this->sys_root . $path_parts['dirname'], $path_parts['basename']);
			
			//don't use "/" when browsing root (prevents double // at the thumbs path below
			if ($path_parts['dirname'] == "/") {
				
				$path_parts['dirname'] = "";
				
			}
			
			//load thumbnail through previewer (alternative direct method below)
			$this->item['thumbnail'] = '/file.php?load=' . $this->www_root . $path . '&thumbs=true';
			//$this->item['thumbnail'] = 'connectors/php/filemanager.php?mode=preview&path=' . $path_parts['dirname'] . "/_thumbs/" . $path_parts['basename'] . ".jpg";
			
			//link thumbnail to actual thumbnail image
			//$this->item['thumbnail'] = $this->www_root . $path_parts['dirname'] . "/_thumbs/" . $path_parts['basename'] . ".jpg";
			
			$this->item['preview'] = '/file.php?load=' . $this->www_root . $path;

			list($width, $height, $type, $attr) = getimagesize($this->sys_root . $path);
			$this->item['properties']['Height'] = $height;
			$this->item['properties']['Width'] = $width;
			$this->item['properties']['Size'] = filesize($this->sys_root . $path);
			 
		} else if(file_exists($this->root . $this->config['icons']['path'] . strtolower($this->item['filetype']) . '.png')) {
			
			$this->item['preview'] = $this->config['icons']['path'] . strtolower($this->item['filetype']) . '.png';
			$this->item['properties']['Size'] = filesize($this->sys_root . $path);
			
		}
		
		$this->item['properties']['Date Modified'] = date($this->config['date'], $this->item['filemtime']);
		
	}
	
	public function copy() {
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($this->sys_root . $this->get['old']));
		if (!$userGroup->allowEditing()) {
			
			$this->error(sprintf($this->lang('GROUP_ACCESS_ERROR')));
			
		}
		
		//if the user clicked on a file when pasting, find the file's path and reassign accordingly
		if(is_file($this->sys_root . $this->get['new'])) {
			
			if (dirname($this->get['new']) != '/') {
				
				$this->get['new'] = dirname($this->get['new']) . '/';
				
			} else {
				
				$this->get['new'] = '/';
				
			}
			
		}
		
		//define the source file's path
		$source = $this->sys_root . $this->get['old'];
		$dest = $this->sys_root . $this->get['new'];
		
		//check is the source is a directory
		if(is_dir($source)) {
			
			//see if the new destination is an existing directory, if it is, return an error notifying the user that the directory already exists
			if(is_dir($dest . '/' . basename($this->get['old']))) {
				
				$this->error(sprintf($this->lang('FOLDER_ALREADY_EXISTS'), $this->get['new'] . basename($this->get['old'])));
				
			}
			
			$source_esc = str_replace("/", "\/", $this->get['old']);
			
			if($source != $dest && preg_match("/^$source_esc/", $this->get['new']) == 0) {
				
				if ($this->copyr($source, $dest . basename($source))) {
					
					$copySuccess = true;
					
				} else {
					
					$copySuccess = false;
					$this->error(sprintf($this->lang('INVALID_FOLDER_OR_FILE')));
					
				}
				
			} else {
				
				$this->error(sprintf($this->lang('COPY_SELF_ERROR')));
				
			}
			
			//mark that this is a directory
			$isDirectory = true;
			
		} else {
			
			//see if the new destination is an existing file, if it is, return an error notifying the user that the file already exists
			if(is_file($dest . '/' . basename($this->get['old']))) {
				
				$this->error(sprintf($this->lang('FILE_ALREADY_EXISTS'), $this->get['new'] . basename($this->get['old'])));
				
			}
			
			if(file_exists($source)) {
				
				if(copy($source, $dest . basename($source))) {
					
					$copySuccess = true;
					
				} else {
					
					$copySuccess = false;
					
				}
				
			} else {
				
				$this->error(sprintf($this->lang('INVALID_FOLDER_OR_FILE')));
				
			}
			
			//mark that this is not a directory
			$isDirectory = false;
			
		}
		
		
		if ($copySuccess === true) {
			
			//update filemanager database link - for files use full absolute path, for folders use full path with wildcard at the end to get everything under that path
			if ($isDirectory !== true) {
				
				$result = mysql_query("SELECT wwwPath, fsPath, owner, security, groupId FROM fileManager WHERE fsPath = '" . $this->sys_root . $this->get['old'] . "'");
				
			} else {
				
				$result = mysql_query("SELECT wwwPath, fsPath, owner, security, groupId FROM fileManager WHERE fsPath LIKE BINARY '" . $this->sys_root . $this->get['old'] . "%'");
				
			}
			
			while ($row = mysql_fetch_object($result)) {
				
				//append the the directory structure to the new path
				if($isDirectory === true) {
					
					//strip the root path from the database entires and the selected directory, leaving just any files or directories under the selected directory
					$updatedWwwPath = str_replace($this->www_root . $this->get['old'], '', $row->wwwPath);
					$updatedFsPath = str_replace($this->sys_root . $this->get['old'], '', $row->fsPath);
					
					//reconsturct the directory structure using the new/destination path
					$updatedWwwPath = $this->www_root . $this->get['new'] . basename($this->get['old']) . '/' . $updatedWwwPath;
					$updatedFsPath = $this->sys_root . $this->get['new'] . basename($this->get['old']) . '/' . $updatedFsPath;
					
				} else {
					
					//if it's just a file
					$updatedWwwPath = $this->www_root . $this->get['new'] . basename($this->get['old']);
					$updatedFsPath = $this->sys_root . $this->get['new'] . basename($this->get['old']);
					
				}
				
				$updatedWwwPath = addslashes($updatedWwwPath);
				$updatedFsPath = addslashes($updatedFsPath);
				
				mysql_query("INSERT INTO fileManager (wwwPath, fsPath, owner, security, groupId) VALUES ('{$updatedWwwPath}', '{$updatedFsPath}', '{$row->owner}', '{$row->security}', '{$row->groupId}')");
				
			}
			
		}
		
		$array = array(
			
			'Error'=>"",
			'Code'=>0,
			'New Path'=>$this->get['new'],
			'New Name'=>basename($source)
			
		);
		
		return $array;
		
	}
	
	public function move() {
		
		//do not allow deletion of repository directory
		if ($this->www_root . $this->get['old'] == "/assets/repository/") {
			
			$this->error(sprintf($this->lang('REPOSITORY_MODIFICATION_ERROR')));
			
		}
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($this->sys_root . $this->get['old']));
		if (!$userGroup->allowEditing()) {
			
			$this->error(sprintf($this->lang('GROUP_ACCESS_ERROR')));
			
		}
		
		//if the user clicked on a file when pasting, find the file's path and reassign accordingly
		if(is_file($this->sys_root . $this->get['new'])) {
			
			$this->get['new'] =  dirname($this->get['new']) . '/';
			
		}
		
		//define the source file's path
		$source = $this->sys_root . $this->get['old'];
		$dest = $this->sys_root . $this->get['new'];
		
		//set directory flag, if the source is a directory
		if(is_dir($this->sys_root . $this->get['old'])) {
			
			$isDirectory = true;
			
			//see if the new destination is an existing directory, if it is, return an error notifying the user that the directory already exists
			if(is_dir($dest . '/' . basename($this->get['old']))) {
				
				$this->error(sprintf($this->lang('FOLDER_ALREADY_EXISTS'), $this->get['new'] . basename($this->get['old'])));
				
			}
			
		} else {
			
			//see if the new destination is an existing file, if it is, return an error notifying the user that the file already exists
			if(is_file($dest . '/' . basename($this->get['old']))) {
				
				$this->error(sprintf($this->lang('FILE_ALREADY_EXISTS'), $this->get['new'] . basename($this->get['old'])));
				
			}
			
		}
		
		$source_esc = str_replace("/", "\/", $this->get['old']);
		
		if($source != $dest && preg_match("/^$source_esc/", $this->get['new']) == 0) {
			
			if(file_exists($source)) {
				
				if(rename($source, $dest . basename($this->get['old']))) {
					
					//update filemanager database link - for files use full absolute path, for folders use full path with wildcard at the end to get everything under that path
					if ($isDirectory !== true) {
						
						$result = mysql_query("SELECT wwwPath, fsPath FROM fileManager WHERE fsPath = '" . $this->sys_root . $this->get['old'] . "'");
						
					} else {
						
						$result = mysql_query("SELECT wwwPath, fsPath FROM fileManager WHERE fsPath LIKE BINARY '" . $this->sys_root . $this->get['old'] . "%'");
						
					}
					
					while ($row = mysql_fetch_object($result)) {
						
						//append the the directory structure to the new path
						if($isDirectory === true) {
							
							//strip the root path from the database entires and the selected directory, leaving just any files or directories under the selected directory
							$updatedWwwPath = str_replace($this->www_root . $this->get['old'], '', $row->wwwPath);
							$updatedFsPath = str_replace($this->sys_root . $this->get['old'], '', $row->fsPath);
							
							//reconsturct the directory structure using the new/destination path
							$updatedWwwPath = $this->www_root . $this->get['new'] . basename($this->get['old']) . '/' . $updatedWwwPath;
							$updatedFsPath = $this->sys_root . $this->get['new'] . basename($this->get['old']) . '/' . $updatedFsPath;
							
						} else {
							
							//if it's just a file
							$updatedWwwPath = $this->www_root . $this->get['new'] . basename($this->get['old']);
							$updatedFsPath = $this->sys_root . $this->get['new'] . basename($this->get['old']);
							
						}
						
						$updatedWwwPath = addslashes($updatedWwwPath);
						$updatedFsPath = addslashes($updatedFsPath);
						
						mysql_query("UPDATE fileManager SET wwwPath = '{$updatedWwwPath}', fsPath = '{$updatedFsPath}' WHERE fsPath = '{$row->fsPath}'");
						
					}
					
					//delete the thumbnail(s)
					$path_parts = pathinfo($this->get['old']);
					
					if(file_exists($this->sys_root . $path_parts['dirname'] . "/_thumbs/" . $path_parts['basename'] . ".jpg")) {
						
						unlink($this->sys_root . $path_parts['dirname'] . "/_thumbs/" . $path_parts['basename'] . ".jpg");
						
					}
					
				}
				
				$array = array(
					
					'Error'=>"",
					'Code'=>0,
					'New Path'=>$this->get['new'],
					'New Name'=>basename($this->get['old'])
					
				);
				
				return $array;
				
			} else {
				
				$this->error(sprintf($this->lang('INVALID_FOLDER_OR_FILE')));
				
			}
			
		} else {
			
			$this->error(sprintf($this->lang('MOVE_SELF_ERROR')));
			
		}
		
	}
	
	public function rename() {
		
		//do not allow deletion of repository directory
		if ($this->www_root . $this->get['old'] == "/assets/repository/") {
			
			$this->error(sprintf($this->lang('REPOSITORY_MODIFICATION_ERROR')));
			
		}
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($this->sys_root . $this->get['old']));
		if (!$userGroup->allowEditing()) {
			
			$this->error(sprintf($this->lang('GROUP_ACCESS_ERROR')));
			
		}
		
		$suffix = '';

		if(substr($this->get['old'],-1,1) == '/') {
				
			$this->get['old'] = substr($this->get['old'],0,(strlen($this->get['old'])-1));
			
			$suffix = '/';
			
		} else {
			
			$this->validateExtension($this->get['new']);
			
		}
		
		//clean and validate file extension of new name
		$this->get['new'] = $this->cleanString($this->get['new'],array('.','-'));
		
		//get the path, etc
		$path_parts = pathinfo($this->get['old']);
		$path = $path_parts['dirname'];
		$filename = $path_parts['basename'];
		
		if(file_exists($this->sys_root . $path . '/' . $this->get['new'])) {
			
			if($suffix=='/' && is_dir($this->sys_root . $path . '/' . $this->get['new'])) {
				
				$this->error(sprintf($this->lang('FOLDER_ALREADY_EXISTS'),$this->get['new']));
				
			}
			
			if($suffix=='' && is_file($this->sys_root . $path . '/' . $this->get['new'])) {
				
				$this->error(sprintf($this->lang('FILE_ALREADY_EXISTS'),$this->get['new']));
				
			}
			
		}

		if(!rename($this->sys_root . $this->get['old'],$this->sys_root . $path . '/' . $this->get['new'])) {
			
			if(is_dir($this->get['old'])) {
				
				$this->error(sprintf($this->lang('FOLDER_RENAMING_ERROR'),$filename,$this->get['new']));
				
			} else {
				
				$this->error(sprintf($this->lang('FILE_RENAMING_ERROR'),$filename,$this->get['new']));
				
			}
			
		} else {
			
			//rename the thumbnail
			if(file_exists($this->sys_root . $path . '/_thumbs/' . $filename . ".jpg")) {
				
				if(!rename($this->sys_root . $path . '/_thumbs/' . $filename . ".jpg", $this->sys_root . $path . '/_thumbs/' . $this->get['new'] . ".jpg")) {
					
					$this->error(sprintf($this->lang('FILE_RENAMING_ERROR'),$filename,$this->get['new']));
					
				}
				
			}
			
		}		 
		
		//update filemanager database link
		$oldFsPath = addslashes($this->sys_root . $path . '/' . $filename);
		
		$result = mysql_query("SELECT wwwPath, fsPath FROM fileManager WHERE fsPath LIKE BINARY '{$oldFsPath}%'");
		
		while ($row = mysql_fetch_object($result)) {
			
			$updatedWwwPath =  addslashes(str_replace($this->www_root . $path . '/' . $filename, $this->www_root . $path . '/' . $this->get['new'], $row->wwwPath));
			$updatedFsPath =  addslashes(str_replace($this->sys_root . $path . '/' . $filename, $this->sys_root . $path . '/' . $this->get['new'], $row->fsPath));
			
			mysql_query("UPDATE fileManager SET wwwPath = '{$updatedWwwPath}', fsPath = '{$updatedFsPath}' WHERE fsPath = '{$row->fsPath}'");
			
		}
		
		$array = array(
			
			'Old Path'=>$this->get['old'],
			'Old Name'=>$filename,
			'Path'=>$path . '/' . $this->get['new'].$suffix,
			'WWW Path'=>$this->www_root . $path . '/' . $this->get['new'],
			'Filename'=>$this->get['new'],
			'Error'=>"",
			'Code'=>0
			
			
		);
		
		return $array;
		
	}
	
	public function delete() {
		
		//do not allow deletion of repository directory
		if ($this->www_root . $this->get['path'] == "/assets/repository/") {
			
			$this->error(sprintf($this->lang('REPOSITORY_MODIFICATION_ERROR')));
			
		}
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($this->sys_root . $this->get['path']));
		if (!$userGroup->allowEditing()) {
			
			$this->error(sprintf($this->lang('GROUP_ACCESS_ERROR')));
			
		}
		
		if(is_dir($this->sys_root . $this->get['path'])) {
			
			if($this->unlinkRecursive($this->sys_root . $this->get['path'])) {
				
				//update filemanager database link
				$fsPath = addslashes($this->sys_root . $this->get['path']);
				mysql_query("DELETE FROM fileManager WHERE fsPath LIKE BINARY '{$fsPath}%'");
				
			}
			
			$array = array(
				
				'Error'=>"",
				'Code'=>0,
				'Path'=>$this->get['path']
				
			);
			
			return $array;
			
		} else if(file_exists($this->sys_root . $this->get['path'])) {
			
			if(unlink($this->sys_root . $this->get['path'])) {
				
				//update filemanager database link
				$fsPath = addslashes($this->sys_root . $this->get['path']);
				mysql_query("DELETE FROM fileManager WHERE fsPath = '{$fsPath}'");
				
			}
			
			$path_parts = pathinfo($this->get['path']);
			
			//delete the thumbnail
			if(file_exists($this->sys_root . $path_parts['dirname'] . "/_thumbs/" . $path_parts['basename'] . ".jpg")) {
				
				unlink($this->sys_root . $path_parts['dirname'] . "/_thumbs/" . $path_parts['basename'] . ".jpg");
				
			}
			
			$array = array(
				
				'Error'=>"",
				'Code'=>0,
				'Path'=>$this->get['path']
				
			);
			
			return $array;
			
		} else {
			
			$this->error(sprintf($this->lang('INVALID_FOLDER_OR_FILE')));
			
		}
		
	}
	
	public function setSecurity() {
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($this->sys_root . $this->get['path']));
		if (!$userGroup->allowEditing()) {
			
			$this->error(sprintf($this->lang('GROUP_ACCESS_ERROR')));
			
		}
		
		//update filemanager database link
		$fsPath = addslashes($this->sys_root . $this->get['path']);
		if (mysql_query("UPDATE fileManager SET security = '{$this->get['security']}' WHERE fsPath = '{$fsPath}'")) {
			
			$array = array(
				
				'Error'=>"",
				'Code'=>0
				
			);
			
		} else {
			
			$this->error(sprintf($this->lang('UNABLE_TO_SET_SECURITY')));
			
		}
		
		return $array;
		
	}
	
	public function add() {
		
		//check if a file was uploaded
		if(!isset($_FILES['newfile']) || !is_uploaded_file($_FILES['newfile']['tmp_name'])) {
			
			$this->error(sprintf($this->lang('INVALID_FILE_UPLOAD')));
			
		}
		
		//validate file extension
		$this->validateExtension($_FILES['newfile']['name']);
		
		//check if the file is larger than max upload size (if max upload size is enabled)
		if(($this->config['upload']['size']!=false && is_numeric($this->config['upload']['size'])) && ($_FILES['newfile']['size'] > ($this->config['upload']['size'] * 1024 * 1024))) {
			
			$this->error(sprintf($this->lang('UPLOAD_FILES_SMALLER_THAN'),$this->config['upload']['size'] . $this->lang('mb')));
			
		}
		
		//if max_disk is not set to zero, check max disk settings and remaining disk space, against the size of the upload
		if($this->config['upload']['maxdisk'] != false) {
			
			//convert max_disk to bytes
			$maxDisk = $this->config['upload']['maxdisk'] * 1024 * 1024;
			
			//get the total used and remaining disk
			$usedDisk = $this->usedDiskSpace($this->config['sys_root']);
			$remainingDisk = $maxDisk - $usedDisk;
			
			//check if the uploaded file is larger than the allotted disk space
			if($_FILES['newfile']['size'] > $maxDisk) {
				
				$this->error(sprintf($this->lang('UPLOAD_FILES_SMALLER_THAN'), $this->config['upload']['maxdisk'] . $this->lang('mb')));
				
			}
			
			//check if there is any free space in the root directory
			if($usedDisk >= $maxDisk) {
			
				$this->error(sprintf($this->lang('UPLOAD_MAX_DISK_SPACE'), $this->config['upload']['maxdisk'] . $this->lang('mb')));
			
			}
			
			//check if the uploaded file is larger than the remaining disk space
			if($_FILES['newfile']['size'] > $remainingDisk) {
				
				$this->error(sprintf($this->lang('UPLOAD_NOT_ENOUGH_DISK'), $this->formatBytes($remainingDisk)));
				
			}
			
		}
		
		//check if the upload directory exists
		if(!is_dir($this->sys_root . $this->post['currentpath'])) {
			
			$this->error(sprintf($this->lang('FOLDER_DOES_NOT_EXIST'),$this->lang('display_root_as') . $this->post['currentpath']));
			
		}
		
		$_FILES['newfile']['name'] = $this->cleanString($_FILES['newfile']['name'],array('.','-'));
		
		if(!$this->config['upload']['overwrite']) {
			
			$_FILES['newfile']['name'] = $this->checkFilename($this->sys_root . $this->post['currentpath'],$_FILES['newfile']['name']);
			
		}
		
		move_uploaded_file($_FILES['newfile']['tmp_name'], $this->sys_root . $this->post['currentpath'] . $_FILES['newfile']['name']);
		
		$this->createThumbnail($this->sys_root . $this->post['currentpath'], $_FILES['newfile']['name']);
		
		//update filemanager database link
		$wwwPath = addslashes($this->www_root . $this->post['currentpath'] . $_FILES['newfile']['name']);
		$fsPath = addslashes($this->sys_root . $this->post['currentpath'] . $_FILES['newfile']['name']);
		
		mysql_query("INSERT INTO fileManager (wwwPath, fsPath, owner, security, groupId) VALUES ('{$wwwPath}', '{$fsPath}', '{$_SESSION['username']}', '{$this->post['newfile_security']}', '')");
		
		$response = array(
			
			'Path'=>$this->post['currentpath'],
			'Name'=>$_FILES['newfile']['name'],
			'Error'=>"",
			'Code'=>0
			
		);
		
		echo '<textarea>' . json_encode($response) . '</textarea>';
		
		die();
		
	}
	
	public function addfolder() {
		
		//clean the folder name
		$newdir = $this->cleanString($this->get['name'],array('.','-'));
		
		//check if the parent directory exists
		if (!is_dir($this->sys_root . $this->get['path'])) {
			
			$this->error(sprintf($this->lang('FOLDER_DOES_NOT_EXIST'),$this->lang('display_root_as') . $this->get['path']));
			
		}
		
		//check if the folder is allowed
		if(in_array($this->get['name'], $this->config['unallowed_dirs'])) {
			
			$this->error(sprintf($this->lang('UNABLE_TO_CREATE_FOLDER'),$newdir));
			
		}
		
		//check if the folder already exists
		if(is_dir($this->sys_root . $this->get['path'] . $newdir)) {
			
			$this->error(sprintf($this->lang('FOLDER_ALREADY_EXISTS'),$newdir));
			
		}
		
		//try to create the folder
		if(!mkdir($this->sys_root . $this->get['path'] . $newdir, 0755)) {
			
			$this->error(sprintf($this->lang('UNABLE_TO_CREATE_FOLDER'),$newdir));
			
		}
		
		$array = array(
			
			'Parent'=>$this->get['path'],
			'Name'=>$newdir,
			'Error'=>"",
			'Code'=>0
			
		);
		
		return $array;
		
	}
	
	public function download() {
		
		//check group security
		$userGroup = new FileUserGroupValidator();
		$userGroup->loadFileUserGroups(sanitize_string($this->sys_root . $this->get['path']));
		if (!$userGroup->allowEditing()) {
			
			$this->error(sprintf($this->lang('GROUP_ACCESS_ERROR')));
			
		}
		
		$array = array(
			
			'Path'=>$this->www_root . $this->get['path'],
			'Error'=>"",
			'Code'=>0
			
		);
		
		return $array;
		
	}
	
	public function preview() {
	
		if(isset($this->get['path']) && file_exists($this->sys_root . $this->get['path'])) {
				
			//check if the source image exists in the source directory
			if(file_exists($this->sys_root . $this->get['path'])) {
					
				list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($this->sys_root . $this->get['path']);
			
				switch($sourceType) {
			
					case 1: $image = imagecreatefromgif($this->sys_root . $this->get['path']); break;
					case 2: $image = imagecreatefromjpeg($this->sys_root . $this->get['path']); break;
					case 3: $image = imagecreatefrompng($this->sys_root . $this->get['path']); break;
					default: return(true);
			
				}
				
				header('Content-type: image/jpeg');
				imagejpeg($image, '', 92);
				
			}
			
		} else {
			
			$this->error(sprintf($this->lang('FILE_DOES_NOT_EXIST'),$this->get['path']));
			
		}
		
	}
	
	private function unlinkRecursive($dir,$deleteRootToo=true) {
	
		if(!$dh = @opendir($dir)) {
			
			return;
			
		}
		
		while(false !== ($obj = readdir($dh))) {
			
			if($obj == '.' || $obj == '..') {
				
				continue;
				
			}
			 
			if(!@unlink($dir . '/' . $obj)) {
				
				$this->unlinkRecursive($dir.'/'.$obj, true);
				
			}
			
		}

		closedir($dh);

		if($deleteRootToo) {
			
			@rmdir($dir);
			
		}
		
		return(true);
		
	}
	
	//copy a directory and all subdirectories and files (recursive)
	function copyr($source, $dest) {
		
		// Check for symlinks
		if(is_link($source)) {
			
			return symlink(readlink($source), $dest);
			
		}
	 
		// Simple copy for a file
		if(is_file($source)) {
			
			return copy($source, $dest);
			
		}
	 
		// Make destination directory
		if(!is_dir($dest)) {
			
			mkdir($dest);
			
		}
	 
		// Loop through the folder
		$dir = dir($source);
		
		while(false !== $entry = $dir->read()) {
			
			// Skip pointers
			if($entry == '.' || $entry == '..') {
				
				continue;
				
			}
	 
			// Deep copy directories
			$this->copyr("$source/$entry", "$dest/$entry");
			
		}
	 
		// Clean up
		$dir->close();
		return true;
		
	}
	
	private function cleanString($string, $allowed = array()) {
		
		$allow = null;
		
		if(!empty($allowed)) {
		
			foreach($allowed as $value) {
			
				$allow .= "\\$value";
				
			}
			
		}
		
		$mapping = array(
		
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', ' '=>'-', '"'=>'', "'"=>'', '/'=>''
			
		);
	
		if(is_array($string)) {
			
			$cleaned = array();
			
			foreach($string as $key => $clean) {
			
				$clean = strtr($clean, $mapping);
				$clean = preg_replace("/[^{$allow}_a-zA-Z0-9]/", '', $clean);
				$cleaned[$key] = preg_replace('/[_]+/', '_', $clean); // remove double underscore
				
			}
			
		} else {
		
			$string = strtr($string, $mapping);
			$string = preg_replace("/[^{$allow}_a-zA-Z0-9]/", '', $string);
			$cleaned = preg_replace('/[_]+/', '_', $string); // remove double underscore
			
		}
		
		return $cleaned;
		
	}
	
	private function sanitize($var) {
	
		$sanitized = strip_tags($var);
		$sanitized = str_replace('http://', '', $sanitized);
		$sanitized = str_replace('https://', '', $sanitized);
		$sanitized = str_replace('../', '', $sanitized);
		
		return $sanitized;
		
	}
	
	private function checkFilename($path,$filename,$i='') {
		
		if(!file_exists($path . $filename)) {
			
			return $filename;
			
		} else {
			
			$_i = $i;
			
			$tmp = explode(/*$this->config['upload']['suffix'] . */$i . '.',$filename);
			
			if($i=='') {
				
				$i=1;
				
			} else {
				
				$i++;
				
			}
			
			$filename = str_replace($_i . '.' . $tmp[(sizeof($tmp)-1)],$i . '.' . $tmp[(sizeof($tmp)-1)],$filename);
			
			return $this->checkFilename($path,$filename,$i);
			
		}
		
	}
	
	private function loadLanguageFile() {

		// we load langCode var passed into URL if present
		// else, we use default configuration var
		if(isset($this->params['langCode'])) {
			
			$lang = $this->params['langCode'];
			
		} else {
			
			$lang = $this->config['culture'];
			
		}
		
		if(file_exists($this->root. 'scripts/languages/'.$lang.'.js')) {
			
			$stream =file_get_contents($this->root. 'scripts/languages/'.$lang.'.js');
			
			$this->language = json_decode($stream, true);
			
		} else {
			
			$this->error($this->lang('Language file not found: ' . $lang . '.js'));
			
		}
		
	}
	
	//Returns true if $file has an extension stored in the config['upload']['allowedExtensions'] array
	private function validateExtension($file) {
		
		$path_parts = pathinfo($file);
		
		if(trim($path_parts['extension']) == "") {
			
			$this->error(sprintf($this->lang('INVALID_FILENAME')));
			
		}
		
		//if wildcard is in valid extensions list, return true
		if(in_array("*", $this->config['upload']['allowedExtensions'])) {
			
			return(true);
			
		}
		
		//validate file extension
		if(!in_array($path_parts['extension'], $this->config['upload']['allowedExtensions'])) {
			
			$this->error(sprintf($this->lang('FILE_TYPE_ERROR'), $path_parts['extension']));
			
		}	
			
		return(true);
		
	}
	
	//Returns the file size for all files in a target directory (including all subdirectories within the target directory)
	private function usedDiskSpace($dir) {
		
		$return = 0;
		$dirhandle = opendir($dir);
		
		while($file = readdir($dirhandle)) {
			
			if($file !="." && $file != "..") {
				
				if(is_dir($dir . "/" . $file)) {
					
					$return = $return + $this->usedDiskSpace($dir . "/" . $file);
					
				} else {
					
					$return = $return + filesize($dir . "/" . $file);
					
				}
				
			}
			
		}
		
		closedir($dirhandle);
		
		return($return);
		
	}
	
	//create thunbnails for each file upload
	private function createThumbnail($path, $filename) {
		
		//thumbnail width and height
		$width = 170;
		$height = 170;
		
		//get image size info
		//sourceType options:
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
		
		list($sourceWidth, $sourceHeight, $sourceType) = getimagesize("$path/$filename");
		
		switch ($sourceType) {
	
			case 1: $image = imagecreatefromgif("$path/$filename"); break;
			case 2: $image = imagecreatefromjpeg("$path/$filename"); break;
			case 3: $image = imagecreatefrompng("$path/$filename"); break;
			default: return(true);
	
		}
			
		$thumbsPath = "$path/_thumbs";
		
		//check if the source image exists in the source directory
		if(file_exists("$path/$filename")) {
			
			//check if the thumbnail already exists
			if(!file_exists("$thumbsPath/$filename.jpg")) {
				
				//create the thumbnail directory if it doesn't exist
				if(!is_dir($thumbsPath)) {
					
					if(!mkdir($thumbsPath, 0755, true)) {$this->error(sprintf($this->lang('CREATE_IMAGE_THUMBNAIL_FAILED')));}
					
				}
				
				//calculate the size of the thumbnail image
				$thumbHeight = ceil($sourceHeight / $sourceWidth * $width);
				
				if((trim($width) != "" && $sourceWidth > $width) || (trim($height) != "" && $sourceHeight > $height)) {
			
					$thumbHeight = ceil($sourceHeight / $sourceWidth * $width);
					
					if(trim($height) != "" && $thumbHeight > $height) {
						
						$thumbWidth = ceil($sourceWidth / $sourceHeight * $height);
						$thumbHeight = $height;
						
					} else {
						
						$thumbWidth = $width;
						
					}
					
				} else {
					
					$thumbWidth = $sourceWidth;
					$thumbHeight = $sourceHeight;
					
				}
				
				//create the thumbnail image
				$thumbnailImage = imagecreatetruecolor($thumbWidth, $thumbHeight); 
				imagecopyresampled($thumbnailImage, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);
				
				//store the thumbnail image in the thumbnail directory
				if(!imagejpeg($thumbnailImage, "$thumbsPath/$filename.jpg", 92)) {
					
					//display an error if the thumbnail was not created
					$this->error(sprintf($this->lang('CREATE_IMAGE_THUMBNAIL_FAILED')));
					
				}
				
			}
			
		}
		
	}
	
	//converts bytes to kb, mb, or gb as needed for display.
	private function formatBytes($bytes) {
		
		$n = $bytes;
		$d = 1024;
		$c = 0;
		$u = array($this->lang('bytes'), $this->lang('kb'), $this->lang('mb'), $this->lang('gb'));
		
		while(true) {
			
			if($n < $d) {
				
				$n = round($n * 100) / 100;
				return ($n . $u[$c]);
				
			} else {
				
				$n /= $d;
				$c += 1;
				
			}
			
		}
		
	}
	
}

?>