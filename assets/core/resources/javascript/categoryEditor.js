function objectSortableOn() {
		
	$('#categories_list').sortable({
		
		update: function() {
			
			$.ajax({
				
				url: '/ajaxChangeCategoryOrder.php',
				type: 'post',
				dataType: "text",
				data: $('#categories_list').sortable('serialize')
				
			});
			
		},
		handle: '.handle',
		revert: true,
		scroll: true
		
	});
	
}

function initEditCategory(category, categoryId) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		cancelEditCategory();
		
	}
	
	//hide the current static field
	$('#' + categoryId).hide();
	
	//create the editable text and ok & cancel buttons
	$('<span id="edit_in_place"><input style="width:450px;" type="text" id="category_edit" value="' + $('#' + categoryId).html().replace(/"/g,'&quot;').replace(/\'/g,'&#039;') + '"> <input type="button" id="editor_ok" value="ok"> <input type="button" id="editor_cancel" value="cancel"></span>').insertAfter('#' + categoryId);
	
	//assign the category being edited to a global variable
	lastCategory = category;
	lastCategoryId = categoryId;
	
	//create observers for ok and cancel buttons
	$('#editor_ok').bind('click', function() {editCategory();});
	$('#editor_cancel').bind('click', function() {cancelEditCategory();});	
	
}

function editCategory() {
	
	$.ajax({
		
		url: '/ajaxUpdateCategory.php',
		type: 'post',
		dataType: 'script',
		data: {category: lastCategory, value: $('#category_edit').val()}
		
	});
	
}

function cancelEditCategory() {
	
	if ($('#edit_in_place').length > 0) {
		
		//remove all observers
		$('#editor_ok').unbind();
		$('#editor_cancel').unbind();
		
		//remove the edit-in-place dom object
		$('#edit_in_place').remove();
		
		//show the original static category name
		$('#' + lastCategoryId).show();
		
	}
	
}

function deleteCategory(category) {
	
	$.ajax({
		
		url: '/ajaxDeleteCategory.php',
		type: 'post',
		dataType: 'text',
		data: {category: category}
		
	}).done(function() {
		
		regenerateList();
		
	});
	
	//set the locks
	editCategoryOptionsViewLock = 0;
	editCategoryGroupsViewLock = 0;
	
}

function initEditCategoryOptions(category) {
	
	//handle existing editor (if necessary)
	if ($('#edit_category_options_in_place').length > 0) {
		
		if (editCategoryOptionsViewLock == 1) {
			
			cancelEditCategoryOptions();
			
			//if lastCategory (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
			if (category == lastCategoryOptions) {

				return;

			}
			
		} else if (editCategoryGroupsViewLock) {
			
			cancelEditCategoryUserGroups();
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_category_options_in_place').length == 0) {
		
		//keep track of last category being viewed
		lastCategoryOptions = category;
		
		//create the editable fields
		$('<div id="edit_category_options_in_place" style="display:none;"></div>').insertAfter('#category_' + category);
		
		$.ajax({
			
			url: '/ajaxShowCategoryOptions.php',
			type: 'post',
			dataType: 'text',
			data: {category: category}
			
		}).done(function(data) {
			
			$('#edit_category_options_in_place').html(data);
			
			//set the lock
			editCategoryOptionsViewLock = 1;
			
			$('#edit_category_options_in_place').fadeIn(500);
		
			//watches the submit button
			$('#edit_category_options_form').bind('submit', function(e) {

				//e.stop prevents the submit action from executing
				e.preventDefault();
			
				ajaxSave();
				
			});
		
			$('#edit_category_options_cancel').bind('click', function() {cancelEditCategoryOptions();});
			
			$.ajax({
				
				url: '/ajaxLoadFlyoutEditorContent.php',
				type: 'post',
				dataType: 'text',
				data: {category: category}
				
			}).done(function(data) {
				
				initializeEditor();
				CKEDITOR.instances.flyoutContent.setData(data);
				
			});
			
		});
		
	}
	
}

function cancelEditCategoryOptions() {
	
	//destroy ckeditor
	CKEDITOR.instances.flyoutContent.destroy();
	
	//remove all observers for edit_user and cancel
	$('#edit_category_options_form').unbind();
	$('#edit_category_options_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_category_options_in_place').remove();
	
	//set the lock
	editCategoryOptionsViewLock = 0;
	
}

function initEditCategoryUserGroups(category) {
	
	//handle existing editor (if necessary)
	if ($('#edit_category_options_in_place').length > 0) {
		
		if (editCategoryOptionsViewLock == 1) {
			
			cancelEditCategoryOptions();
			
		} else if (editCategoryGroupsViewLock == 1) {
			
			cancelEditCategoryUserGroups();
			
			//if lastCategory (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
			if (category == lastCategoryOptions) {

				return;

			}
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_category_options_in_place').length == 0) {
		
		//keep track of last category being viewed
		lastCategoryOptions = category;
		
		//create the editable fields
		$('<div id="edit_category_options_in_place" style="display:none;"></div>').insertAfter('#category_' + category);
		
		$.ajax({
			
			url: '/ajaxShowCategoryUserGroupAssignments.php',
			type: 'post',
			dataType: 'text',
			data: {category: category}
			
		}).done(function(data) {
			
			$('#edit_category_options_in_place').html(data);
			
			//set the lock
			editCategoryGroupsViewLock = 1;
			
			$('#edit_category_options_in_place').fadeIn(500);
			
			//wathes the submit button for the assigned section
			$('#to_available').bind('click', function() {
				
				$.ajax({
					
					url: '/ajaxUpdateCategoryAssignedUserGroups.php',
					type: 'post',
					dataType: 'script',
					data: $('#update_assigned').serialize()
					
				});
				
			});
			
			//wathes the submit button for the available section
			$('#to_assigned').bind('click', function() {
				
				$.ajax({
					
					url: '/ajaxUpdateCategoryAvailableUserGroups.php',
					type: 'post',
					dataType: 'script',
					data: $('#update_available').serialize()
					
				});
				
			});
			
			$('#editor_cancel').bind('click', function() {cancelEditCategoryUserGroups();});
			
		});
		
	}
	
}

function cancelEditCategoryUserGroups() {
	
	//remove all observers for edit_user and cancel
	$('#update_assigned').unbind();
	$('#update_available').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_category_options_in_place').remove();
	
	//set the lock
	editCategoryGroupsViewLock = 0;
	
}

function ajaxSave() {
	
	var values = $('#edit_category_options_form').serialize();
	values += '&flyoutContent=' + encodeURIComponent(CKEDITOR.instances.flyoutContent.getData());
	
	$.ajax({
		
		url: '/ajaxUpdateCategoryOptions.php',
		type: 'post',
		dataType: 'script',
		data: values
		
	});
	
}

function showAddCategory() {
	
	if (!$('#add_category_container').is(":visible")) {
		
		$('#add_category_container').fadeIn(500);
		
	} else {
		
		$('#add_category_container').hide();
				
	}
	
}

function addCategory() {
	
	//wathes the submit button
	$('#add_category').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing
		e.preventDefault();
		
		$.ajax({
			
			url: this.action,
			type: 'post',
			dataType: 'script',
			data: $(this).serialize()
			
		});
		
	});
	
}

function regenerateList() {
	
	//check if anything is being edited; if there is, cancel it
	if ($('#edit_in_place').length > 0) {
		
		 cancelEditCategory();
		
	}
	
	if ($('#edit_category_options_in_place').length > 0) {
		
		cancelEditCategoryOptions();
		
	}
	
	$.ajax({
		
		url: '/ajaxCategoryEditor.php',
		type: 'post',
		dataType: 'text',
		data: $(this).serialize()
		
	}).done(function(data) {
		
		$('#categories_list').html(data);
		objectSortableOn();
		
	});
	
}

function openDocumentManager(callbackFunction, elementName) {

	openServerBrowser(
	
		'/selectDocument.php?callbackFunction=' + callbackFunction + '&elementName=' + elementName,
		screen.width * 0.7,
		screen.height * 0.7
		
	);
	
}

function openServerBrowser(url, width, height) {

	var iLeft = (screen.width - width) / 2;
	var iTop = (screen.height - height) / 2;
	var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes';
	sOptions += ',width=' + width;
	sOptions += ',height=' + height;
	sOptions += ',left=' + iLeft;
	sOptions += ',top=' + iTop;
	var oWindow = window.open(url, 'BrowseWindow', sOptions);
}

function selectPath(elementName, path) {
	
	$('#' + elementName).val(path);
	
}

$(document).ready(function() {
	
	regenerateList();
	addCategory();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});