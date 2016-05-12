/**
 *	Filemanager JS core
 *
 *	filemanager.js
 *
 *	@license			MIT License
 *	@author				Jason Huck - Core Five Labs
 *	@author				Simon Georget <simon (at) linea21 (dot) com>
 *	@copyright			Authors
 *
 *	@forked & modified	Nathan Moch <no (dot) barcode (at) gmail (dot) com> 12/2010
 *
 */
 
// check if called from framset and adjust window.opener refernces

if (top.location.href != window.location.href) {
	
	openerValue = parent.window.opener;
	openerValueString = 'parent.window.opener.';
	openerValueClose = parent.window;
	
} else {
	
	openerValue = window.opener;
	openerValueString = 'window.opener.';
	openerValueClose = parent.window;
	
}
 
// function to retrieve GET params
$.urlParam = function(name){
	var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if (results)
		return results[1]; 
	else
		return 0;
}

/*---------------------------------------------------------
  Setup, Layout, and Status Functions
---------------------------------------------------------*/

// Sets paths to connectors based on language selection.
var treeConnector = 'scripts/jquery.filetree/connectors/jqueryFileTree.' + lang + location.search;
var fileConnector = 'connectors/' + lang + '/filemanager.' + lang;

//Set global screen refresh flag to false
var refreshing = false;

//Set last selected directory
var previousNode = '/';

// Set last last opened folder.
var lastOpenedFolder = '';

// Get localized messages from file 
// through culture var or from URL
if($.urlParam('langCode') != 0) culture = $.urlParam('langCode');
var lg = [];
$.ajax({
  url: 'scripts/languages/'  + culture + '.js',
  async: false,
  dataType: 'json',
  success: function (json) {
    lg = json;
  }
});


// We finalize the FileManager UI initialization 
// with localized text if necessary
$('#upload span').append(lg.upload);
$('#uploading').append(lg.upload_in_progress);
$('#newfolder span').append(lg.new_folder);
$('#grid span').attr('title', lg.grid_view);
$('#list span').attr('title', lg.list_view);
$('#fileinfo h1').append(lg.initializing_editor);
$('#itemOptions a[href$="#select"]').append(lg.select);
$('#itemOptions a[href$="#download"]').append(lg.download);
$('#itemOptions a[href$="#copy"]').append(lg.copy);
$('#itemOptions a[href$="#cut"]').append(lg.cut);
$('#itemOptions a[href$="#paste"]').append(lg.paste);
$('#itemOptions a[href$="#rename"]').append(lg.rename);
$('#itemOptions a[href$="#delete"]').append(lg.del);

$('#homeItemOptions a[href$="#paste"]').append(lg.paste);

// Forces columns to fill the layout vertically.
// Called on initial page load and on resize.
var setDimensions = function() {

	var newH = $(window).height() - $('#uploader').height() - 20;	
	$('#filetree, #fileinfo').height(newH);
	
}

var setLocationWidth = function() {
	
	var newW = $(window).width() - $('#uploaderOptions').outerWidth(true) - 40;	
	$('#location').width(newW);
	
}

// Display Min Path
var displayRoot = function(path) {
	if(path == "" || path == "/")
		return(lg.display_root_as);
	else 
		return lg.display_root_as + path;
}

// from http://phpjs.org/functions/basename:360
var basename = function(path, suffix) {
    var b = path.replace(/^.*[\/\\]/g, '');

    if (typeof(suffix) == 'string' && b.substr(b.length-suffix.length) == suffix) {
        b = b.substr(0, b.length-suffix.length);
    }
    
    return b;
}

// Sets the folder status, upload, and new folder functions 
// to the path specified. Called on initial page load and 
// whenever a new directory is selected.
var setUploader = function(path) {
	
	$('#currentpath').val(path);
	
	//removed "Current Folder" label: lg.current_folder + displayRoot(path);
	var displayCurrentPath = displayRoot(path);
	$('#location').attr('title', displayCurrentPath);
	$('#location h1').text(displayCurrentPath);
	
	if (previousNode != path) {
		
		//highlight the currently selected directory
		$('#filetree').find('a[rel="' + path + '"]').addClass('selectedDirectory');
		$('#filetree').find('a[rel="' + previousNode + '"]').removeClass('selectedDirectory');
		previousNode = path;
		
	}
	
	$('#newfolder').unbind().click(function() {
		
		var getFolderName = function(fname) {

			if(fname != ''){
				foldername = fname;

				$.getJSON(fileConnector + '?mode=addfolder&path=' + $('#currentpath').val() + '&name=' + foldername, function(result){
					if(result['Code'] == 0){
						addFolder(result['Parent'], result['Name']);
						getFolderInfo(result['Parent'], false);
					} else {
						jAlert(result['Error']);
					}				
				});
			} else {
				jAlert(lg.no_foldername);
			}
		}
		jPrompt(lg.prompt_foldername, lg.default_foldername, lg.new_folder, function(r) {if(r!==false) {getFolderName(r);}});

	});	
}

// Binds specific actions to the toolbar in detail views.
// Called when detail views are loaded.
var bindToolbar = function(data) {
	
	$('#fileinfo').find('select#security').on('change', function(){
		setSecurity(data);
	});
	
	$('#fileinfo').find('button#download').click(function(){
		download(data);
	});
	
	$('#fileinfo').find('button#select').click(function(){
		selectItem(data);
	});
	
	$('#fileinfo').find('button#copy').click(function(){
		copyItem(data);
	});
	
	$('#fileinfo').find('button#cut').click(function(){
		cutItem(data);
	});
	
	if (!$.cookie("cutPath") && !$.cookie("copyPath")) {
		$('#fileinfo').find('button#paste').hide();
		$('#fileinfo').find('button#paste').click(function(){
			pasteItem(data);
		});
	} else {
		$('#fileinfo').find('button#paste').click(function(){
			pasteItem(data);
		});
		$('#fileinfo').find('button#paste').show();
	}
	
	$('#fileinfo').find('button#rename').click(function(){
		var newName = renameItem(data);
		if(newName.length) $('#fileinfo > h1').text(newName);
	});
	
	$('#fileinfo').find('button#delete').click(function(){
		if(deleteItem(data)) $('#fileinfo').html('<h1>' + lg.select_from_left + '</h1>');
	});
	
	if ($('#edit_groups').length > 0) {
		
		$('#fileinfo').find('button#edit_groups').click(function(){
			initEditUserGroups(data['Path']);
		});
		
	}
	
}

// Converts bytes to kb, mb, or gb as needed for display.
var formatBytes = function(bytes) {
	var n = parseFloat(bytes);
	var d = parseFloat(1024);
	var c = 0;
	var u = [lg.bytes,lg.kb,lg.mb,lg.gb];
	
	while(true){
		if(n < d){
			n = Math.round(n * 100) / 100;
			return n + u[c];
		} else {
			n /= d;
			c += 1;
		}
	}
}


/*---------------------------------------------------------
  Item Actions
---------------------------------------------------------*/

//download
var download = function(data) {

var connectString = fileConnector + '?mode=download&path=' + data['Path'];

	$.ajax({
		type: 'GET',
		url: connectString,
		dataType: 'json',
		async: false,
		success: function(result){
			if(result['Code'] != 0){
				
				jAlert(result['Error']);
				
			} else {
				
				window.location = '/file.php?load=' + result['Path'];
				
			}
		}
	});
	
}

//sets the security of the chosen file
var setSecurity = function(data){
	
	var connectString = fileConnector + '?mode=security&path=' + data['Path'] + '&security=' + $('select#security').val();

	$.ajax({
		type: 'GET',
		url: connectString,
		dataType: 'json',
		async: false,
		success: function(result){
			if(result['Code'] != 0){
				
				jAlert(result['Error']);
				
			}			
		}
	});
	
	return true;
}

// Calls the SetUrl function for FCKEditor compatibility,
// passes file path, dimensions, and alt text back to the
// opening window. Triggered by clicking the "Select" 
// button in detail views or choosing the "Select"
// contextual menu option in list views. 
// NOTE: closes the window when finished.
var selectItem = function(data) {
	
	if(openerValue) {
		
		if($.urlParam('CKEditor')) {
		
			// use CKEditor 3.0 integration method
			openerValue.CKEDITOR.tools.callFunction($.urlParam('CKEditorFuncNum'), '/file.php?load=' + data['WWW Path']);
			
		} else if ($.urlParam('callbackFunction') != 0 && $.urlParam('elementName') != 0) {
			
			eval(openerValueString + $.urlParam('callbackFunction') + '("' + $.urlParam('elementName') + '", "' + data['WWW Path'] + '");');
			
		} else {
		
			// use FCKEditor 2.0 integration method
			if(data['Properties']['Width'] != ''){
				var p = data['Path'];
				var w = data['Properties']['Width'];
				var h = data['Properties']['Height'];			
				openerValue.SetUrl(p,w,h);
				
			} else {
			
				openerValue.SetUrl(data['WWW Path']);
				
			}		
		}
		
	 	if(window.tinyMCEPopup) {
	 		
        	// use TinyMCE > 3.0 integration method
            var win = tinyMCEPopup.getWindowArg("window");
			win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = data['WWW Path'];
            
            if (typeof(win.ImageDialog) != "undefined") {
				// Update image dimensions
            	if (win.ImageDialog.getImageData)
                 	win.ImageDialog.getImageData();

                // Preview if necessary
                if (win.ImageDialog.showPreviewImage) {
                	
					win.ImageDialog.showPreviewImage(data['WWW Path']);
					
				}
				
			}
			
			tinyMCEPopup.close();
			return;
		}
		
		openerValueClose.close();
		
	} else {
	
		jAlert(lg.fck_select_integration);
		
	}
}

// Copies the path and filename of the item to a session
// cookie.
var copyItem = function(data) {
	
	$.cookie("cutPath", null);
	$.cookie("copyPath", data['Path']);
	$('#fileinfo').find('button#paste').show();
	$('#itemOptions li.paste').show();
	$("#homeItemOptions").enableContextMenu();
	
}

var cutItem = function(data) {
	
	$.cookie("copyPath", null);
	$.cookie("cutPath", data['Path']);
	$('#fileinfo').find('button#paste').show();
	$('#itemOptions li.paste').show();
	$("#homeItemOptions").enableContextMenu();
	
}

// Pastes the file stored in cutPath or copyPath to the
// selected location.
var pasteItem = function(data) {
	
	if($.cookie("cutPath") != null) {
		
		mode = 'move';
		cookieType = 'cutPath';
		
	} else if ($.cookie("copyPath") != null) {
		
		mode = 'copy';
		cookieType = 'copyPath';
		
	}
	
	var connectString = fileConnector + '?mode=' + mode + '&old=' + $.cookie(cookieType) + '&new=' + data['Path'];

	$.ajax({
		type: 'GET',
		url: connectString,
		dataType: 'json',
		async: false,
		success: function(result) {
			if(result['Code'] == 0) {
				
				var newPath = result['New Path'];
				var newName = result['New Name'];
				
				var thisNode = $('#filetree').find('a[rel="' + newPath + '"]');
				
				//if the user was in another folder when the action was performed click 3 times
				refreshing = true;
				if(lastOpenedFolder == newPath) {
					
					thisNode.click().click();
					
				} else {
					
					thisNode.click().click().click();
					
				}
				
				refreshing = false;
				
				$('#fileinfo').find('button#paste').hide();
				$('#itemOptions li.paste').hide();
				$("#homeItemOptions").disableContextMenu();
				
				if (mode == 'move') {
					
					removeNode($.cookie(cookieType));
					
				}
				
				$.cookie(cookieType, null);
				
			} else {
				
				$('#itemOptions li.paste').hide();
				$("#homeItemOptions").disableContextMenu();
				$.cookie(cookieType, null);
				$('#fileinfo').find('button#paste').hide();
				jAlert(result['Error']);
				
			}
			
		}
		
	});
	
}

// Renames the current item and returns the new name.
// Called by clicking the "Rename" button in detail views
// or choosing the "Rename" contextual menu option in 
// list views.
var renameItem = function(data) {
	var finalName = '';

	var getNewName = function(rname) {
		
		if(rname != '') {
			
			var givenName = rname;	
			var oldPath = data['Path'];	
			var connectString = fileConnector + '?mode=rename&old=' + data['Path'] + '&new=' + givenName;
			
			$.ajax({
				type: 'GET',
				url: connectString,
				dataType: 'json',
				async: false,
				success: function(result) {
					if(result['Code'] == 0) {
						
						var newPath = result['Path'];
						var newName = result['Filename'];
	
						updateNode(oldPath, newPath, newName);
						
						//update path and bind toolbar functions
						if ($('#preview').length) {
							
							//the current path as stored onscreen in preview mode
							var currentPath = $("#preview h1").attr("title");
					
							if (currentPath == oldPath) {
								
								bindToolbar(result);
								$('#preview h1').text(newName);
								$('#preview h1').attr("title", newPath);
								
							}
							
						}
						
					} else {
					
						jAlert(result['Error']);
						
					}
					
					finalName = result['Filename'];		
					
				}
				
			});
			
		}
		
	}
	
	jPrompt(lg.new_filename, data['Filename'], lg.rename, function(r) {if(r) getNewName(r);});
	
	return finalName;
	
}

// Updates the specified node with a new name. Called after a successful rename operation.
var updateNode = function(oldPath, newPath, newName) {

	var oldNode = $('#filetree').find('a[rel="' + oldPath + '"]');
	var parentNode = oldNode.parent().parent().prev('a');
	
	//update the old node with the new path and name
	oldNode.attr('rel', newPath).text(newName);
	
	//find the new filetree node
	var thisNode = $('#filetree').find('a[rel="' + newPath + '"]');
	
	if($('#fileinfo').data('view') == 'grid') {
	
		$('#fileinfo img[alt="' + oldPath + '"]').next('p').text(newName);
		$('#fileinfo img[alt="' + oldPath + '"]').attr('alt', newPath);
		
	} else {
		
		$('#fileinfo td[title="' + oldPath + '"]').text(newName);
		$('#fileinfo td[title="' + oldPath + '"]').attr('title', newPath);
		
	}
	
	//if we just renamed a directory click the directory to reload it
	refreshing = true;
	if (thisNode.parent().hasClass('expanded')) {
		
		if(lastOpenedFolder == oldPath) {
			
			lastOpenedFolder = newPath;
			thisNode.click().click();
			
		} else {
			
			thisNode.click().click().click();
			
		}
		
	}
	refreshing = false;
	
	jAlert(lg.successful_rename);
	
}

// Prompts for confirmation, then deletes the current item.
// Called by clicking the Delete button in detail views
// or choosing the Delete contextual menu item in list views.
var deleteItem = function(data){
	var isDeleted = false;
	
	var doDelete = function(){
	
		var connectString = fileConnector + '?mode=delete&path=' + data['Path'];
	
		$.ajax({
			type: 'GET',
			url: connectString,
			dataType: 'json',
			async: false,
			success: function(result){
				if(result['Code'] == 0){
					
					var pathParts = result['Path'].split('/');
					
					if (pathParts[pathParts.length-1] == "") {
						
						newPath = "";
						
						for (i = 0; i < pathParts.length-2; i++) {
							newPath += pathParts[i];
							newPath += "/";
						}
	
						var thisNode = $('#filetree').find('a[rel="' + newPath + '"]');
						
						refreshing = true;
						if(lastOpenedFolder == newPath) {
							
							thisNode.click().click();
							
						} else {
							
							thisNode.click().click().click();
							
						}
						refreshing = false;
						
					}
					
					//if we're in preview mode, exit the node, otherwise just remove the node
					if ($('#preview').length) {
						
						exitFileInfo(data['Path'].substr(0, data['Path'].lastIndexOf('/') + 1));
						removeNode(result['Path']);
						
					} else {
						
						removeNode(result['Path']);
						
					}
					
					isDeleted = true;
					
				} else {
				
					isDeleted = false;
					jAlert(result['Error']);
					
				}			
			}
		});
		
	}
	
	jConfirm(lg.confirmation_delete, lg.del, function(r) {if(r) doDelete();});
	
	return isDeleted;
}


/*---------------------------------------------------------
  Functions to Update the File Tree
---------------------------------------------------------*/

// Adds a new node as the first item beneath the specified
// parent node. Called after a successful file upload.
var addNode = function(path, name){
	var ext = name.substr(name.lastIndexOf('.') + 1);
	var thisNode = $('#filetree').find('a[rel="' + path + '"]');
	var parentNode = thisNode.parent();
	var newNode = '<li class="file ext_' + ext + '"><a rel="' + path + name + '/" href="#">' + name + '/</a></li>';
	
	if(!parentNode.find('ul').size()) parentNode.append('<ul></ul>');		
	parentNode.find('ul').prepend(newNode);
	
	refreshing = true;
	thisNode.click().click().click();
	refreshing = false;

	getFolderInfo(path, false);
	
	$('#uploaderOptions').show();
	$('#newfile').val('');
	$('#uploading').hide();
	jAlert(lg.successful_added_file);
	
}

// Removes the specified node. Called after a successful 
// delete operation.
var removeNode = function(path){
    $('#filetree')
        .find('a[rel="' + path + '"]')
        .parent()
        .fadeOut('slow', function(){ 
            $(this).remove();
        });
    // grid case
    if($('#fileinfo').data('view') == 'grid'){
        $('#contents img[alt="' + path + '"]').parent().parent()
            .fadeOut('slow', function(){ 
                $(this).remove();
        });
    }
    // list case
    else {
        $('table#contents')
            .find('td[title="' + path + '"]')
            .parent()
            .fadeOut('slow', function(){ 
              var pos = $('#contents').dataTable().fnGetPosition(this);
              $('#contents').dataTable().fnDeleteRow(pos);
            });
    }
    // remove fileinfo when item to remove is currently selected
    if ($('#preview').length) {
		$('#fileinfo').fadeOut('slow', function(){
			$(this).empty().show();
		});
	}
}

// Adds a new folder as the first item beneath the
// specified parent node. Called after a new folder is
// successfully created.
var addFolder = function(parent, name){
	var newNode = '<li class="directory collapsed"><a rel="' + parent + name + '/" href="#">' + name + '</a><ul class="jqueryFileTree" style="display: block;"></ul></li>';
	var parentNode = $('#filetree').find('a[rel="' + parent + '"]');
	
	if(parent != ''){
		
		//if the user was in another folder when the action was performed click 3 times (technically not currently possible with folder creation as there is no right-click "New Folder" option - 12/2010)
		refreshing = true;
		if(lastOpenedFolder == parent) {
			
			//if the parent folder was collapsed when the folder was created, click once, otherwise click 2 times
			if($(parentNode).parent().hasClass('collapsed')) {
				
				parentNode.next('ul').prepend(newNode).prev('a').click();
				
			} else {
			
				parentNode.next('ul').prepend(newNode).prev('a').click().click();
				
			}
			
		} else {
		
			parentNode.next('ul').prepend(newNode).prev('a').click().click().click();
			
		}
		refreshing = false;
		
	} else {
	
		$('#filetree > ul').prepend(newNode); 
		$('#filetree').find('li a[rel="' + parent + name + '/"]').click(function(){
				getFolderInfo(parent + name + '/', false);
			}).contextMenu(
				{ menu: 'itemOptions' }, 
				function(action, el, pos){
					var path = $(el).attr('rel');
					setMenus(action, path);
				}
		);
		
	}
	
	jAlert(lg.successful_added_folder);
	
}




/*---------------------------------------------------------
  Functions to Retrieve File and Folder Details
---------------------------------------------------------*/

// Decides whether to retrieve file or folder info based on
// the path provided.
var getDetailView = function(path){
	if(path.lastIndexOf('/') == path.length - 1){
		$('#filetree').find('a[rel="' + path + '"]').click();
		getFolderInfo(path, true);
	} else {
		getFileInfo(path);
	}
}

// Binds contextual menus to items in list and grid views.
var setMenus = function(action, path){
	$.getJSON(fileConnector + '?mode=getinfo&path=' + path, function(data){
		if($('#fileinfo').data('view') == 'grid'){
			var item = $('#fileinfo').find('img[alt="' + data['Path'] + '"]').parent();
		} else {
			var item = $('#fileinfo').find('td[title="' + data['Path'] + '"]').parent();
		}
		
		if(path == '/' && action != 'paste') {return;}
		
		switch(action){
			case 'select':
				selectItem(data);
				break;
			
			case 'download':
				download(data);
				break;
				
			case 'copy':
				copyItem(data);
				break;
				
			case 'cut':
				cutItem(data);
				break;
				
			case 'paste':
				pasteItem(data);
				break;
				
			case 'rename':
				var newName = renameItem(data);
				break;
				
			case 'delete':
				// TODO: When selected, the file is deleted and the
				// file tree is updated, but the grid/list view is not.
				if(deleteItem(data)) item.fadeOut('slow', function(){ $(this).remove(); });
				break;
		}
	});
}

// Retrieves information about the specified file as a JSON
// object and uses that data to populate a template for
// detail views. Binds the toolbar for that detail view to
// enable specific actions. Called whenever an item is
// clicked in the file tree or list views.
var getFileInfo = function(file){
	
	// Update location for status, upload, & new folder functions.
	var currentpath = file.substr(0, file.lastIndexOf('/') + 1);
	setUploader(currentpath);

	// Include the template.
	var template = '<div id="preview"><img /><h1></h1><table id="preview_data" cellspacing="0" cellpadding="0"></table></div>';
	template += '<form id="toolbar">';
	if(openerValue != null) template += '<button id="select" name="select" type="button" value="Select">' + lg.select + '</button>';
	template += '<button id="download" name="download" type="button" value="Download">' + lg.download + '</button>';
	template += '<button id="copy" name="copy" type="button" value="Copy">' + lg.copy + '</button>';
	template += '<button id="cut" name="cut" type="button" value="Cut">' + lg.cut + '</button>';
	template += '<button id="paste" name="paste" type="button" value="Paste">' + lg.paste + '</button>';
	template += '<button id="rename" name="rename" type="button" value="Rename">' + lg.rename + '</button>';
	template += '<button id="delete" name="delete" type="button" value="Delete">' + lg.del + '</button>';
	template += '<button id="parentfolder">' + lg.parentfolder + '</button>';
	template += '</form>';
	
	$('#fileinfo').html(template);
	$('#parentfolder').click(function() {exitFileInfo(currentpath); return false;});
	
	// Retrieve the data & populate the template.
	$.getJSON(fileConnector + '?mode=getinfo&path=' + file, function(data){
		if(data['Code'] == 0){
			$('#fileinfo').find('h1').text(data['Filename']).attr('title', file);
			$('#fileinfo').find('img').attr('src',data['Preview']);
			
			var properties = '';
			
			if(data['Properties']['Width'] && data['Properties']['Width'] != '') properties += '<tr><td>' + lg.dimensions + '</td><td class="data">' + data['Properties']['Width'] + 'x' + data['Properties']['Height'] + '</td></tr>';
			if(data['Properties']['Date Created'] && data['Properties']['Date Created'] != '') properties += '<tr><td>' + lg.created + '</td><td class="data">' + data['Properties']['Date Created'] + '</td></tr>';
			if(data['Properties']['Date Modified'] && data['Properties']['Date Modified'] != '') properties += '<tr><td>' + lg.modified + '</td><td class="data">' + data['Properties']['Date Modified'] + '</td></tr>';
			if(data['Properties']['Size'] && data['Properties']['Size'] != '') properties += '<tr><td>' + lg.size + '</td><td class="data">' + formatBytes(data['Properties']['Size']) + '</td></tr>';
			
			if(data['Security'] && data['Security'] != '') {
				
				if (data['Security'] == "private") privateChecked = ' selected'; else privateChecked = '';
				if (data['Security'] == "friends") friendsChecked = ' selected'; else friendsChecked = '';
				if (data['Security'] == "authenticated") authenticatedChecked = ' selected'; else authenticatedChecked = '';
				if (data['Security'] == "public") publicChecked = ' selected'; else publicChecked = '' ;
				
				properties += '<tr><td>' + lg.security + '</td><td class="data"><select id="security">';
				properties += '<option value="private"' + privateChecked + '>Private</option>';
				properties += '<option value="friends"' + friendsChecked + '>Friends Only</option>';
				properties += '<option value="authenticated"' + authenticatedChecked + '>Site Members</option>';
				properties += '<option value="public"' + publicChecked + '>Public</option>';
				properties += '</select></td></tr>';
				
			}
			
			if(data['Display Groups'] == 'true') {
				
				properties += '<tr><td>' + lg.groups + '</td><td class="data"><div id="file_group_assignment">' + data['Assigned Groups'] + '</div>';
				
					if (data['Edit Groups'] == 'true') {
						
						properties += '<button id="edit_groups" type="button">Edit</button></td></tr>';
						
					}
					
			}
			
			$('#preview_data').html(properties);
			
			//wraps inside of toolbar buttons with span for css styling purposes
			$('#fileinfo').find('button').wrapInner('<span></span>');
			
			// Bind toolbar functions.
			bindToolbar(data);
			
			myLayout.initContent('center');
			
		} else {
		
			jAlert(data['Error']);
			
		}
	});
	
}

var exitFileInfo = function(currentPath){
	
	var currentNode = $('#filetree').find('a[rel="' + currentPath + '"]');
	
	if (currentPath == lastOpenedFolder) {
		
		currentNode.click().click();
		
	} else {
		
		currentNode.click().click().click();
		
	}
	
	return false;
	
}

// Retrieves data for all items within the given folder and
// creates a list view. Binds contextual menu options.
var getFolderInfo = function(path, updateLastOpenedFolder){
	
	// Update location for status, upload, & new folder functions.
	setUploader(path);
	
	if(updateLastOpenedFolder) {
		
		// Keep track fo the last folder clicked for filetree operations.
		lastOpenedFolder = path;
		
	}
	
	// Display an activity indicator.
	$('#fileinfo').html('<img id="activity" src="images/wait30trans.gif" width="30" height="30" />');
	
	// Retrieve the data and generate the markup.
	$.getJSON(fileConnector + '?path=' + path + '&mode=getfolder', function(data){		
		var result = '';
		
		// Is there any error or user is unauthorized?
		if(data['Code'] == '-1') {
			
			$('#fileinfo').html('');
			jAlert(data['Error']);
			return false;
			
		};
		
		if(data){
			if($('#fileinfo').data('view') == 'grid'){
				result += '<ul id="contents" class="grid">';
				
				for(key in data){
					var props = data[key]['Properties'];
				
					var scaledWidth = 64;
					var actualWidth = props['Width'];
					if(actualWidth > 1 && actualWidth < scaledWidth) scaledWidth = actualWidth;
					
					//determine whether or not to display a thumbnail or an icon
					if (data[key]['Filename'].match(/([^\/\\]+)\.(jpg|jpeg|png|gif)$/i) != null) {
	
						result += '<li><div class="clip"><img src="' + data[key]['Thumbnail'] + '" alt="' + data[key]['Path'] + '" /><p>' + data[key]['Filename'] + '</p></div>';
						
					} else {
						
						result += '<li><div class="clip"><img src="' + data[key]['Preview'] + '" width="' + scaledWidth + '" alt="' + data[key]['Path'] + '" /><p>' + data[key]['Filename'] + '</p></div>';
						
					}
					
					if(props['Width'] && props['Width'] != '') result += '<span class="meta dimensions">' + props['Width'] + 'x' + props['Height'] + '</span>';
					if(props['Size'] && props['Size'] != '') result += '<span class="meta size">' + props['Size'] + '</span>';
					if(props['Date Created'] && props['Date Created'] != '') result += '<span class="meta created">' + props['Date Created'] + '</span>';
					if(props['Date Modified'] && props['Date Modified'] != '') result += '<span class="meta modified">' + props['Date Modified'] + '</span>';
					result += '</li>';
				}
				
				result += '</ul>';
			} else {
				result += '<table id="contents" class="list">';
				result += '<thead><tr><th><div>' + lg.name + '</div></th><th><div>' + lg.dimensions + '</div></th><th><div>' + lg.size + '</div></th><th><div>' + lg.modified + '</div></th></tr></thead>';
				result += '<tbody>';
				
				for(key in data){
					var path = data[key]['Path'];
					var props = data[key]['Properties'];
					
					//gets the file extension to use when assigning the mini icon class to the table cell
					var ext = data[key]['Filename'].substr(data[key]['Filename'].lastIndexOf('.') + 1).toLowerCase();
					
					if (data[key]['File Type'] == 'dir') {
						
						ext = "directory";
						
					} else {
						
						ext = "file ext_" + ext;
						
					}
					
					result += '<tr>';
					result += '<td class="' + ext + '" title="' + path + '">' + data[key]['Filename'] + '</td>';

					if(props['Width'] && props['Width'] != ''){
						result += ('<td>' + props['Width'] + 'x' + props['Height'] + '</td>');
					} else {
						result += '<td></td>';
					}
					
					if(props['Size'] && props['Size'] != ''){
						result += '<td>' + formatBytes(props['Size']) + '</td>';
					} else {
						result += '<td></td>';
					}
					
					if(props['Date Modified'] && props['Date Modified'] != ''){
						result += '<td>' + props['Date Modified'] + '</td>';
					} else {
						result += '<td></td>';
					}
				
					result += '</tr>';					
				}
								
				result += '</tbody>';
				result += '</table>';
			}			
		} else {
			result += '<h1>' + lg.could_not_retrieve_folder + '</h1>';
		}
		
		// Add the new markup to the DOM.
		$('#fileinfo').html(result);
		
		// Bind click events to create detail views and add
		// contextual menu options.
		if($('#fileinfo').data('view') == 'grid'){
			$('#fileinfo').find('#contents li').click(function(){
				var path = $(this).find('img').attr('alt');
				getDetailView(path);
			}).contextMenu({ menu: 'itemOptions' }, function(action, el, pos){
				var path = $(el).find('img').attr('alt');
				setMenus(action, path);
			});
		} else {
			$('#fileinfo tbody tr').click(function(){
				var path = $('td:first-child', this).attr('title');
				getDetailView(path);		
			}).contextMenu({ menu: 'itemOptions' }, function(action, el, pos){
				var path = $('td:first-child', el).attr('title');
				setMenus(action, path);
			});
			
			$('#contents').dataTable({
				
				"oLanguage": {
					"sSearch": lg.listSearch,
					"sLengthMenu": lg.listShow,
					"sInfo": lg.listPageInfo,
					"sInfoFiltered": lg.listFilteredFrom,
					"sFirst": lg.listFirstPage,
					"sLast": lg.listLastPage,
					"sNext": lg.listNextPage,
					"sPrevious": lg.listPrevPage,
					"sEmptyTable": lg.listEmptyFolder,
					"sInfoEmpty": lg.listPageInfoEmpty,
					"sZeroRecords": lg.listNoRecords
				},
				"iDisplayLength": 25,
				"aoColumns": [null,{"sType": "image_dimensions"},{"sType": "file-size"},{"sType": "us_date"}],
				"sPaginationType": "full_numbers",
				"bStateSave": true
				
			});
		}
		
	});
	
}

//handles group assignments
var initEditUserGroups = function(path) {
	
	if ($('#edit_user_groups_in_place').length == 0) {
		
		//create the editable fields
		$('<div id="edit_user_groups_in_place" style="display:none;"></div>').insertAfter('#preview_data');
		
		$('#preview_data').hide();
		
		$.ajax({
			
			url: 'connectors/' + lang + '/ajaxShowFileUserGroupAssignments.' + lang,
			type: 'post',
			dataType: 'text',
			data: {path: path}
			
		}).done(function(data) {
			
			$('#edit_user_groups_in_place').html(data);
			
			//set the lock
			editCategoryGroupsViewLock = 1;
			
			$('#edit_user_groups_in_place').fadeIn(500);
			
			//wathes the submit button for the assigned section
			$('#to_available').bind('click', function() {
				
				$.ajax({
					
					url: 'connectors/' + lang + '/ajaxUpdateFileAssignedUserGroups.' + lang,
					type: 'post',
					dataType: 'script',
					data: $('#update_assigned').serialize()
					
				});
				
			});
			
			//wathes the submit button for the available section
			$('#to_assigned').bind('click', function() {
				
				$.ajax({
					
					url: 'connectors/' + lang + '/ajaxUpdateFileAvailableUserGroups.' + lang,
					type: 'post',
					dataType: 'script',
					data: $('#update_available').serialize()
					
				});
				
			});
			
			$('#editor_cancel').bind('click', function() {cancelEditUserGroups();});
			
		});
		
	}
	
}

var cancelEditUserGroups = function() {
	
	//remove all observers for edit_user and cancel
	$('#update_assigned').unbind();
	$('#update_available').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_user_groups_in_place').remove();
	
	$('#preview_data').show();
	
}



/*---------------------------------------------------------
  Initialization
---------------------------------------------------------*/

$(function(){
	// Set initial view state.
	$('#fileinfo').data('view', 'grid');

	// Set buttons to switch between grid and list views.
	$('#grid').click(function(){
		$(this).addClass('ON');
		$('#list').removeClass('ON');
		$('#fileinfo').data('view', 'grid');
		getFolderInfo($('#currentpath').val(), false);
	});
	
	$('#list').click(function(){
		$(this).addClass('ON');
		$('#grid').removeClass('ON');
		$('#fileinfo').data('view', 'list');
		getFolderInfo($('#currentpath').val(), false);
	});

	// Provide initial values for upload form, status, etc.
	setUploader('');

	$('#uploader').attr('action', fileConnector);

	$('#uploader').ajaxForm({
		dataType: 'json',
		beforeSubmit: function() {
			
			$('#uploaderOptions').hide();
			$('#uploading').show();
			
		},
		success: function(responseText) {
    		
    		data = responseText;
			
			if(data['Code'] == 0){
				addNode(data['Path'], data['Name']);
			} else {
				$('#uploaderOptions').show();
				$('#uploading').hide();
				jAlert(data['Error']);
			}
		}
	});

	// Creates file tree.
    $('#filetree').fileTree({
		root: '',
		script: treeConnector,
		multiFolder: true,
		expandSpeed: 100,
		collapseSpeed: 100,
		folderCallback: function(path) {
			
			getFolderInfo(path, true);
			
		},
		after: function(data) {
			// Give everything under the root menu a context menu.
			$('#filetree ul ul').find('li a').contextMenu( { menu: 'itemOptions' }, 
				function(action, el, pos){
					var path = $(el).attr('rel');
					setMenus(action, path);
				}
			);
			
			$('#filetree>ul>li>a').contextMenu(
				{ menu: 'homeItemOptions' }, 
				function(action, el, pos){
					var path = $(el).attr('rel');
					setMenus(action, path);
				}
			);
			
			// Click the first node in the filetree if the filetree has never been opened.
			if(!$('#currentpath').val()) {$('#filetree').find('li a').click();}
		}
	}, function(file){
		getFileInfo(file);
	});
	
	// Disable select function if not opened by an RTE 
	if(openerValue == null) $('#itemOptions li.select').remove();
	
	//hide move option (until a file is copied/cut)
	$('#itemOptions li.paste').hide();
	
	//hide home paste option (until a file is copied/cut)
	$("#homeItemOptions").disableContextMenu();
	
	// Adjust layout.
	setDimensions();
	$(window).resize(setDimensions);
	
	// Provides support for adjustible columns.
	myLayout = $('body').layout({ applyDefaultStyles: true });
	
	setLocationWidth();
	$(window).resize(setLocationWidth);
	
});