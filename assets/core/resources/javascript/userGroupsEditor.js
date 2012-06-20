function initEditUserGroup(id, s, nameOrder, orderBy) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0 && userGroupEditorViewLock == 1) {
		
		cancelEditUserGroup();
		
		//if last viewed is being clicked again, just remove the list (above) and exit (return, below)
		if (id == lastId) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_in_place').length == 0) {
		
		//hide the editing list
		$('#user_groups_list').hide();
		$('#editor_options').hide();
		if ($('#add_user_group_container').is(":visible")) {
			
			$('#add_user_group_container').hide();
			
		}
		
		//keep track of last group being viewed
		lastId = id;
		
		//create the editable fields
		$('<div id="edit_in_place" class="edit_user_group" style="display:none;"></div>').insertAfter('#message_box');
		
		$.ajax({
			
			url: '/ajaxShowUserGroupEditingOptions.php',
			type: 'post',
			dataType: 'text',
			data: {id: id}
			
		}).done(function(data) {
			
			$('#edit_in_place').html(data);
			
			//set the lock
			userGroupEditorViewLock = 1;
			
			//display editor
			$('#edit_in_place').fadeIn(500);
			
			//wathes the submit button
			$('#edit_user_group').bind('submit', function(e) {

				//prevents the submit action from executing 
				e.preventDefault();
				
				$.ajax({
					
					url: this.action,
					type: 'post',
					dataType: 'script',
					data: $(this).serialize()
					
				}).complete(function() {
					
					regenerateList(s, '', nameOrder, orderBy);
					
				});

			});
			
			$('#editor_cancel').bind('click', function() {cancelEditUserGroup();});
			
		});
		
	}
	
}

function cancelEditUserGroup() {
	
	//remove all observers and cancel
	$('#edit_user_group').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();
	
	//show the editing list
	$('#user_groups_list').show();
	$('#editor_options').show();
	
	//set the lock
	userGroupEditorViewLock = 0;
	
}

function initEditGroupMembers(id) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0 && userGroupEditorViewLock == 1) {
		
		cancelEditUserGroup();
		
		//if last viewed is being clicked again, just remove the list (above) and exit (return, below)
		if (id == lastId) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_in_place').length == 0) {
		
		//hide the editing list
		$('#user_groups_list').hide();
		$('#editor_options').hide();
		if ($('#add_user_group_container').is(":visible")) {
			
			$('#add_user_group_container').hide();
			
		}
		
		//keep track of last group being viewed
		lastId = id;
		
		//create the editable fields
		$('<div id="edit_in_place" style="display:none;"></div>').insertAfter('#message_box');
		
		$.ajax({
			
			url: '/ajaxShowUserGroupMembers.php',
			type: 'post',
			dataType: 'text',
			data: {id: id, usernameOrder: 'asc', orderBy: 'username', change: ''}
			
		}).done(function(data) {
			
			$('#edit_in_place').html(data);
			
			//set the lock
			userGroupEditorViewLock = 1;
			
			//display editor
			$('#edit_in_place').fadeIn(500);
			
			$('#editor_cancel').bind('click', function() {cancelEditUserGroup();});
			
		});
		
	}
	
}

function cancelEditGroupMembers() {
	
	//remove all observers and cancel
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();
	
	//show the editing list
	$('#user_groups_list').show();
	$('#editor_options').show();
	
	//set the lock
	userGroupEditorViewLock = 0;
	
}

function deleteUserGroup(id, s, nameOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteUserGroup.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function() {
		
		regenerateList(s, '', nameOrder, orderBy, '');
		
	});
	
}

function deleteMultipleUserGroups(s, nameOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleUserGroups.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleUserGroupsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', nameOrder, orderBy, '');
		
	});
	
}

function showAddUserGroup() {
	
	if (!$('#add_user_group_container').is(":visible")) {
		
		$('#add_user_group_container').fadeIn(500);
		
	} else {
		
		$('#add_user_group_container').hide();
				
	}
	
}

function addUserGroup() {
	
	//wathes the submit button
	$('#add_user_group').bind('submit', function(e) {
		
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

function deleteUserGroupMember(username, id, s, usernameOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteUserGroupMember.php',
		type: 'post',
		dataType: 'text',
		data: {username: username}
		
	}).done(function() {
		
		regenerateMemberList(id, s, '', usernameOrder, orderBy, '');
		
	});
	
}

function regenerateList(s, d, nameOrder, orderBy, change) {
	
	$.ajax({
		
		url: '/ajaxUserGroupsEditor.php',
		type: 'post',
		dataType: 'text',
		data: {s: s, d: d, nameOrder: nameOrder, orderBy: orderBy, change: change}
		
	}).done(function(data) {
		
		$('#user_groups_list').html(data);
		
	});
	
}

function regenerateMemberList(id, s, d, usernameOrder, orderBy, change) {
	
	//remove all observers and cancel
	$('#editor_cancel').unbind();
	
	$.ajax({
		
		url: '/ajaxShowUserGroupMembers.php',
		type: 'post',
		dataType: 'text',
		data: {id: id, s: s, d: d, usernameOrder: usernameOrder, orderBy: orderBy, change: change}
		
	}).done(function(data) {
		
		$('#edit_in_place').html(data);
		$('#editor_cancel').bind('click', function() {cancelEditGroupMembers();});
		
	});
	
}

$(document).ready(function() {
	
	addUserGroup();
	regenerateList('', '', 'asc', 'name', '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});