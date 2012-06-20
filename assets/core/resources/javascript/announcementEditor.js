function initEditAnnouncement(id, s, dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0 && announcementEditorViewLock == 1) {
		
		cancelEditAnnouncement();
		
		//if last viewed is being clicked again, just remove the list (above) and exit (return, below)
		if (id == lastId) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_in_place').length == 0) {
		
		//hide the editing list
		$('#announcement_list').hide();
		$('#editor_options').hide();
		
		if ($('#add_announcement_container').is(":visible")) {
			
			$('#add_announcement_container').hide();
			
		}
		
		//hide the date selector is it's visible
		if ($('#calendar_container').is(":visible")) {
			
			$('#calendar_container').hide();
			
		}
		
		//keep track of last announcement being viewed
		lastId = id;
		
		//create the editable fields
		$('<div id="edit_in_place" style="display:none;"></div>').insertAfter('#message_box');
		
		$.ajax({
			
			url: '/ajaxShowAnnouncementEditingOptions.php',
			type: 'post',
			dataType: 'text',
			data: {id: id, s: s}
			
		}).done(function(data) {
			
			$('#edit_in_place').html(data);
			
			//set the lock
			announcementEditorViewLock = 1;
			
			//display editor
			$('#edit_in_place').fadeIn(500);
			
			//watches the date_selector element
			$('#date_selector_edit').bind('click', function(e) {
				
				showSelectorCalendar(e, '#month', '#day', '#year');
				
			});
			
			//wathes the submit button
			$('#edit_announcement').bind('submit', function(e) {

				//prevents the submit action from executing 
				e.preventDefault();
				
				$.ajax({
					
					url: this.action,
					type: 'post',
					dataType: 'text',
					data: $(this).serialize()
					
				}).done(function() {
					
					regenerateList(s, '', dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy, '');
					
				});

			});
			
			//watches the cancel button
			$('#editor_cancel').bind('click', function() {cancelEditAnnouncement();});
			
		});
	
	}
	
}

function cancelEditAnnouncement() {
	
	//remove all observers and cancel
	$('#date_selector_edit').unbind();
	$('#edit_announcement').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();
	
	//hide the date selector is it's visible
	if ($('#calendar_container').is(":visible")) {
		
		$('#calendar_container').hide();
		
	}
	
	//show the editing list
	$('#announcement_list').show();
	$('#editor_options').show();
	
	//set the lock
	announcementEditorViewLock = 0;
	
}

function togglePublishState(id) {
	
	$.ajax({
		
		url: '/ajaxToggleAnnouncementPublishState.php',
		type: 'post',
		data: {id: id}
		
	}).done(function(data) {
		
		$('#publish_state_' + id).html(data);
		
	});
	
}

function deleteAnnouncement(id, s, dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy) {
	
	//remove editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		//remove the edit-in-place dom object
		$('#edit_in_place').remove();
		
	}
	
	$.ajax({
		
		url: '/ajaxDeleteAnnouncement.php',
		type: 'post',
		data: {id: id}
		
	}).done(function() {
		
		regenerateList(s, '', dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
	//set the lock
	announcementEditorViewLock = 0;
	
}

function toggleMultipleAnnouncementsStatus(s, dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxToggleMultipleAnnouncementsStatus.php',
		type: 'post',
		data: $('#multipleAnnouncementsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function deleteMultipleAnnouncements(s, dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy) {
	
	//remove editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		//remove the edit-in-place dom object
		$('#edit_in_place').remove();
		
	}
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleAnnouncements.php',
		type: 'post',
		data: $('#multipleAnnouncementsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
	//set the lock
	announcementEditorViewLock = 0;
	
}

function showAddAnnouncement() {
	
	if (!$('#add_announcement_container').is(":visible")) {
		
		$('#add_announcement_container').fadeIn(500);
		
		
	} else {
		
		$('#add_announcement_container').hide();
		
		//hide the date selector
		$('#calendar_container').hide();
				
	}
	
}

function addAnnouncement() {
	
	//wathes the submit button
	$('#add_announcement').bind('submit', function(e) {
		
		//hide the date selector
		$('#calendar_container').hide();
		
		//prevents the submit action from executing 
		e.preventDefault();
		
		$.ajax({
			
			url: this.action,
			type: 'post',
			dataType: 'script',
			data: $(this).serialize()
			
		});
				
	});
	
}

function regenerateList(s, d, dateCreatedOrder, dateExpiresOrder, titleOrder, statusOrder, orderBy, change) {
	
	//check if anything is being edited; if there is, cancel it
	if ($('#edit_in_place').length > 0) {
		
		 cancelEditAnnouncement();
		
	}
	
	//hide the date selector
	$('#calendar_container').hide();
	
	$.ajax({
		
		url: '/ajaxAnnouncementEditor.php',
		type: 'post',
		dataType: 'text',
		data: {s: s, d: d, dateCreatedOrder: dateCreatedOrder, dateExpiresOrder: dateExpiresOrder, titleOrder: titleOrder, statusOrder: statusOrder, orderBy: orderBy, change: change}
		
	}).done(function(data) {
		
		$('#announcement_list').html(data);
		
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
	
	$("#" + elementName).val(path);
	
}

$(document).ready(function() {
	
	addAnnouncement();
	regenerateList('', '', 'desc', 'desc', 'desc', 'desc', 'dateCreated', '');
	
	$('#date_selector_add').bind('click', function(e) {
		
		showSelectorCalendar(e, '#monthAdd', '#dayAdd', '#yearAdd');
		
	});
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});