function deleteConversation(id, s, dateOrder, titleOrder, authorOrder, postsOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteConversation.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, authorOrder, postsOrder, orderBy, '');
		
	});
	
}

function deleteMultipleConversations(s, dateOrder, titleOrder, authorOrder, postsOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleGroupConversations.php',
		type: 'post',
		dataType: 'text',
		data: $('#deleteMultipleConversations').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, authorOrder, postsOrder, orderBy, '');
		
	});
	
}

function regenerateList(s, d, dateOrder, titleOrder, authorOrder, postsOrder, orderBy, change) {
	
	$.ajax({
		
		url: '/ajaxShowGroupConversationsList.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, s: s, d: d, dateOrder: dateOrder, titleOrder: titleOrder, authorOrder: authorOrder, postsOrder: postsOrder, orderBy: orderBy, change: change}
		
	}).done(function(data) {
		
		$('#conversations_list').html(data);
		
	});
	
}

function toggleStickyState(id, s, dateOrder, titleOrder, authorOrder, postsOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxToggleGroupConversationsStickyState.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, id: id}
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, authorOrder, postsOrder, orderBy, '');
		
	});
	
}

function showAddConversation() {
	
	if (!$('#add_conversation_container').is(":visible")) {
		
		$('#add_conversation_container').fadeIn(500);
		
	} else {
		
		$('#add_conversation_container').hide();
		
	}
	
}

function displayEditor() {
	
	if ($('#loading_editor_message')) {
		
		$('#loading_editor_message').hide();
		
	}
	
	if ($('#add_conversation_button')) {
		
		$('#add_conversation_button').show();
		
	}
	
	setInterval(autoSave, 30000);
	
}

function addConversation() {
	
	if ($('#add_conversation').length > 0) {
		
		//wathes the submit button
		$('#add_conversation').bind('submit', function(e) {

			//e.stop prevents the submit action from executing
			e.preventDefault();
			
			var values = $('#add_conversation').serialize();
			values += '&documentBody=' + encodeURIComponent(CKEDITOR.instances.documentBody.getData());
			
			$.ajax({
				
				url: '/ajaxAddGroupConversation.php',
				type: 'post',
				dataType: 'script',
				data: values
				
			});
			
		});
		
	}
	
}

function autoSave() {
	
	if (CKEDITOR.instances.documentBody.checkDirty()) {
		
		$.ajax({
			
			url: '/ajaxAutoSave.php',
			type: 'post',
			dataType: 'text',
			data: {content: CKEDITOR.instances.documentBody.getData()}
			
		});

	}
	
}

function autoSaveLoad() {
	
	$.ajax({
		
		url: '/ajaxAutoSaveLoad.php',
		type: 'post',
		dataType: 'script'
		
	});
	
}

$(document).ready(function() {
	
	regenerateList('', '', 'desc', 'desc', 'desc', 'desc', 'date', '');
	initializeEditor();
	addConversation();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});