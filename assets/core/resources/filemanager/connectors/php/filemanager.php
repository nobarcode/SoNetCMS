<?php

/**
 *	Filemanager PHP connector
 *
 *	filemanager.php
 *	use for ckeditor filemanager plug-in by Core Five - http://labs.corefive.com/Projects/FileManager/
 *
 *	@license	MIT License
 *	@author		Riaan Los <mail (at) riaanlos (dot) nl>
 *  @author		Simon Georget <simon (at) linea21 (dot) com>
 *	@copyright	Authors
 */

include("../../../../../../connectDatabase.inc");
include("../../../../../../part_session.php");
include("../../../../../../requestVariableSanitizer.inc");
include("../../../../../../class_file_user_group_validator.php");
include("inc/filemanager.inc.php");
include("filemanager.config.php");
include("filemanager.class.php");

$fm = new Filemanager($config);

if(!isset($_GET)) {
  $fm->error($fm->lang('INVALID_ACTION'));
} else {

  if(isset($_GET['mode']) && $_GET['mode']!='') {

    switch($_GET['mode']) {
      	
      default:

        $fm->error($fm->lang('MODE_ERROR'));
        break;

      case 'getinfo':

        if($fm->getvar('path')) {
          sendResponse($fm->getinfo());
        }
        break;

      case 'getfolder':
        	
        if($fm->getvar('path')) {
          sendResponse($fm->getfolder());
        }
        break;

      case 'copy':

        if($fm->getvar('old') && $fm->getvar('new')) {
          sendResponse($fm->copy());
        }
        break;

      case 'move':

        if($fm->getvar('old') && $fm->getvar('new')) {
          sendResponse($fm->move());
        }
        break;

      case 'rename':

        if($fm->getvar('old') && $fm->getvar('new')) {
          sendResponse($fm->rename());
        }
        break;

      case 'delete':

        if($fm->getvar('path')) {
          sendResponse($fm->delete());
        }
        break;

      case 'addfolder':

        if($fm->getvar('path') && $fm->getvar('name')) {
          sendResponse($fm->addfolder());
        }
        break;

      case 'download':
        if($fm->getvar('path')) {
          sendResponse($fm->download());
        }
        break;
        
      case 'preview':
        if($fm->getvar('path')) {
          $fm->preview();
        }
        break;
  		
      case 'security':
        if($fm->getvar('path') && $fm->getvar('security')) {
          sendResponse($fm->setSecurity());
        }
        break;
		
    }

  } else if(isset($_POST['mode']) && $_POST['mode']!='') {

    switch($_POST['mode']) {
      	
      default:

        $fm->error($fm->lang('MODE_ERROR'));
        break;
        	
      case 'add':

        if($fm->postvar('currentpath') && $fm->postvar('newfile_security')) {
          $fm->add();
        }
        break;

    }

  }
}

function sendResponse($response) {
	
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-type: application/json");
	echo json_encode($response);
	die();
	
}

?>