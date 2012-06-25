function openFileManager(callbackFunction, elementName) {

	openServerBrowser(
	
		'/assets/core/resources/filemanager/index.html?callbackFunction=' + callbackFunction + '&elementName=' + elementName,
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

function initEditUser(username, s) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0 && userEditorViewLock == 1) {
		
		cancelEditUser();
		
		//if lastUsername (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
		if (username == lastUsername) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_in_place').length == 0) {
		
		//hide the editing list
		$('#editor_query_options').hide();
		$('#users_list').hide();
		$('#editor_options').hide();
		
		if ($('#add_user_container').is(":visible")) {
			
			$('#add_user_container').hide();
			
		}
		
		//hide the date selector is it's visible
		if ($('#calendar_container').is(":visible")) {
			
			$('#calendar_container').hide();
			
		}
		
		//keep track of last username being viewed
		lastUsername = username;
		
		$('<div id="edit_in_place" style="display:none;"></div>').insertAfter('#editor_query_options');
		
		$.ajax({
			
			url: '/ajaxShowUserEditingOptions.php',
			type: 'post',
			dataType: 'text',
			data: {username: username}
			
		}).done(function(data) {
			
			$('#edit_in_place').html(data);
			
			//set the lock
			userEditorViewLock = 1;
			
			//display editor
			$('#edit_in_place').fadeIn(500);
			
			//watches the date_selector element
			$('#date_selector_edit').bind('click', function(e) {
				
				showSelectorCalendar(e, '#birthMonth', '#birthDay', '#birthYear');
				
			});
			
			//wathes the submit button
			$('#edit_user').bind('submit', function(e) {

				//prevents the submit action from executing 
				e.preventDefault();
				
				$.ajax({
					
					url: this.action,
					type: 'post',
					dataType: 'script',
					data: $(this).serialize()
					
				}).complete(function() {
					
					regenerateList(s, '');
					
				});

			});
			
			$('#editor_cancel').bind('click', function() {cancelEditUser();});
			
		});
		
	}
	
}

function cancelEditUser() {
	
	//remove all observers and cancel
	$('#date_selector_edit').unbind();
	$('#edit_user').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();
	
	//hide the date selector if it's visible
	if ($('#calendar_container').is(":visible")) {
		
		$('#calendar_container').hide();
		
	}
	
	//show the editing list
	$('#editor_query_options').show();
	$('#users_list').show();
	$('#editor_options').show();
	
	//set the lock
	userEditorViewLock = 0;
	
}

function initEditUserGroups(username) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0 && userEditorViewLock == 1) {
		
		cancelEditUser();
		
		//if lastUsername (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
		if (username == lastUsername) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_in_place').length == 0) {
		
		//hide the editing list
		$('#editor_query_options').hide();
		$('#users_list').hide();
		$('#editor_options').hide();
		
		if ($('#add_user_container').is(":visible")) {
			
			$('#add_user_container').hide();
			
		}
		
		//keep track of last username being viewed
		lastUsername = username;
		
		$('<div id="edit_in_place" style="display:none;"></div>').insertAfter('#editor_query_options');
		
		$.ajax({
			
			url: '/ajaxShowUserGroupAssignments.php',
			type: 'post',
			dataType: 'text',
			data: {username: username}
			
		}).done(function(data) {
			
			$('#edit_in_place').html(data);
			
			//set the lock
			userEditorViewLock = 1;
			
			$('#edit_in_place').fadeIn(500);
			
			//wathes the submit button for the assigned section
			$('#to_available').bind('click', function() {
				
				$.ajax({
					
					url: '/ajaxUpdateAssignedUserGroups.php',
					type: 'post',
					dataType: 'script',
					data: $('#update_assigned').serialize()
					
				});
				
			});
			
			//wathes the submit button for the available section
			$('#to_assigned').bind('click', function() {
				
				$.ajax({
					
					url: '/ajaxUpdateAvailableUserGroups.php',
					type: 'post',
					dataType: 'script',
					data: $('#update_available').serialize()
					
				});
				
			});
			
			$('#editor_cancel').bind('click', function() {cancelEditUserGroups();});
			
		});
		
	}
	
}

function cancelEditUserGroups() {
	
	//remove all observers and cancel
	$('#update_assigned').unbind();
	$('#update_available').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();
	
	//show the editing list
	$('#editor_query_options').show();
	$('#users_list').show();
	$('#editor_options').show();
	
	//set the lock
	userEditorViewLock = 0;
	
}

function changeUserStatus(username) {
	
	$.ajax({
		
		url: '/ajaxToggleUserStatus.php',
		type: 'post',
		dataType: 'text',
		data: {username: username}
		
	}).done(function(data) {
		
		$('#user_status_' + username).html(data);
		
	});
	
}

function deleteUser(username, s) {
	
	$.ajax({
		
		url: '/ajaxDeleteUser.php',
		type: 'post',
		dataType: 'script',
		data: {username: username}
		
	}).done(function() {
		
		regenerateList(s, '');
		
	});
	
	//set the lock
	userEditorViewLock = 0;
	
}

function deleteMultipleUsers(s) {
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleUsers.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleUsersAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '');
		
	});
	
	//set the lock
	userEditorViewLock = 0;
	
}

function approveMultipleUsers(s) {
	
	$.ajax({
		
		url: '/ajaxApproveMultipleUsers.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleUsersAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '');
		
	});
	
	//set the lock
	userEditorViewLock = 0;
	
}

function showAddUser() {
	
	if (!$('#add_user_container').is(":visible")) {
		
		$('#add_user_container').fadeIn(500);
		
	} else {
		
		$('#add_user_container').hide();
		
		//hide the date selector is it's visible
		if ($('#calendar_container').is(":visible")) {
			
			$('#calendar_container').hide();
			
		}
				
	}
	
}

function filter() {
	
	//watches the submit button
	$('#query_filter').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing 
		e.preventDefault();
		
		regenerateList();
		
	});
	
}

function addUser() {
	
	//wathes the submit button
	$('#add_user').bind('submit', function(e) {
		
		//hide the date selector is it's visible
		if ($('#calendar_container').is(":visible")) {
			
			$('#calendar_container').hide();
			
		}
		
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

function regenerateList(s, d) {
	
	if ($('#edit_in_place') ) {
		
		cancelEditUser();
		
	}
	
	//hide the date selector
	$('#calendar_container').hide();
	
	//grab filter field values
	filterType = $('#filterType').val();
	filterValue = $('#filterValue').val();
	filterOrder = $('#filterOrder').val();
	
	$.ajax({
		
		url: '/ajaxUserEditor.php',
		type: 'post',
		dataType: 'text',
		data: {s: s, d: d, filterType: filterType, filterValue: filterValue, filterOrder: filterOrder}
		
	}).done(function(data){
		
		$('#users_list').html(data);
		
	});
	
}

$(document).ready( function() {
	
	addUser();
	regenerateList();
	filter();
	
	$('#date_selector_add').bind('click', function(e) {
		
		showSelectorCalendar(e, '#birthMonth', '#birthDay', '#birthYear');
		
	});
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});