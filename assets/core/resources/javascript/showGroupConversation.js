function regenerateList(s, d) {
	
	$.ajax({
		
		url: '/ajaxShowGroupConversationPosts.php',
		type: 'post',
		dataType: 'html',
		data: {parentId: parentId, findId: findId, s: s, d: d}
		
	}).done(function(data) {
		
		$('#conversations_list').html(data);
		
		if (findId) {
			
			scrollTo($('#post_container_' + findId));
			findId = null;
			
		} else if ((s == 'first' && d == '') || d == 'n') {
			
			scrollTo($('#conversations_list .post_container').filter(':first'));
			
		} else if (s == 'last' && d == '') {
			
			scrollTo($('#conversations_list .post_container').filter(':first'));
			
		} else if ((s == 'last_post' && d == '') || d == 'b') {
			
			scrollTo($('#conversations_list .post_container').filter(':last'));
			
		}
		
	});
	
}

function scrollTo(target) {
	
	var targetOffset = target.offset().top;
	$('html,body').animate({scrollTop: targetOffset}, 1000);
	
}

function showConversationEditor() {
	
	if (!$('#conversation_editor_container').is(":visible")) {
		
		$.ajax({
			
			url: '/ajaxLoadGroupConversationSettings.php',
			type: 'post',
			dataType: 'script',
			data: {parentId: $('#parentId').val()}
			
		}).done(function() {
			
			$('#conversation_editor_container').fadeIn(500);
			
		});
		
	} else {
		
		$('#conversation_editor_container').hide();
		
	}
	
}

function editConversationSettings() {
	
	if ($('#edit_conversation_settings').length > 0) {
		
		//wathes the submit button
		$('#edit_conversation_settings').bind('submit', function(e) {

			//e.stop prevents the submit action from executing 
			e.preventDefault();
			
			$.ajax({
				
				url: '/ajaxUpdateGroupConversationSettings.php',
				type: 'post',
				dataType: 'script',
				data: $('#edit_conversation_settings').serialize()
				
			}).done(function() {
				
				showConversationEditor();
				
			});
			
		});
		
	}
	
}

function showReplyToConversation() {
	
	if (!$('#reply_container').is(":visible")) {
		
		$('#reply_container').fadeIn(500);
		
	} else {
		
		$('#reply_container').hide();
		
	}
	
}

function displayEditor() {
	
	if ($('#loading_editor_message').length > 0) {
		
		$('#loading_editor_message').hide();
		
	}
	
	if ($('#reply_button').length > 0) {
		
		$('#reply_button').show();
		
	}
	
	setInterval(autoSave, 30000);
	
}

function reply(conversationId) {
	
	$.ajax({
		
		url: '/ajaxLoadGroupConversationReply.php',
		type: 'post',
		dataType: 'script',
		data: {groupId: groupId, id: conversationId}
		
	}).done(function() {
		
		if (!$('#reply_container').is(":visible")) {
			
			$('#reply_container').show();
			
		}
		
		scrollTo($('#reply_to_conversation'));
		
	});
	
}

function addConversation() {
	
	if ($('#reply_to_conversation').length > 0) {
		
		//wathes the submit button
		$('#reply_to_conversation').bind('submit', function(e) {

			//e.stop prevents the submit action from executing
			e.preventDefault();
			
			var values = $('#reply_to_conversation').serialize();
			values += '&documentBody=' + encodeURIComponent(CKEDITOR.instances.documentBody.getData());
			
			$.ajax({
				
				url: '/ajaxAddGroupConversationPost.php',
				type: 'post',
				dataType: 'script',
				data: values
				
			});
			
		});
		
	}
	
}

function initEditConversation(conversationId) {
	
	//handle existing editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		//destroy ckeditor
		CKEDITOR.instances.post_edit.destroy();
		
		//remove all observers
		$('#editor_ok').unbind();
		$('#editor_cancel').unbind();

		//remove the edit-in-place dom object
		$('#edit_in_place').remove();
		$('#post_container_' + lastConversation).show();
		
	}
	
	//hide the current static field
	$('#post_container_' + conversationId).hide();
	
	$.ajax({
		
		url: '/ajaxLoadGroupConversationEdit.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, id: conversationId}
		
	}).done(function(data) {
		
		$('<div id="edit_in_place"><div id="post_edit"></div><div class="editor_buttons"><input type="button" id="editor_ok" value="Save"> <input type="button" id="editor_cancel" value="Cancel"></div></div>').insertBefore('#post_container_' + conversationId);
		
		//assign the category being edited to a global variable
		lastConversation = conversationId;
		
		//create observers for ok and cancel buttons
		$('#editor_ok').bind('click', function() {editConversation(conversationId);});
		$('#editor_cancel').bind('click', function() {cancelEditConversation();});
		
		initializeEditor();
		
		CKEDITOR.instances.post_edit.setData(data);
		
	});
	
}

function editConversation(conversationId) {
	
	$.ajax({
		
		url: '/ajaxUpdateGroupConversationPost.php',
		type: 'post',
		dataType: 'text',
		data: {id: conversationId, value: CKEDITOR.instances.post_edit.getData()}
		
	}).done(function() {
		
		if ($('#edit_in_place').length > 0) {
			
			$.ajax({
				
				url: '/ajaxLoadGroupConversationPost.php',
				type: 'post',
				dataType: 'text',
				data: {groupId: groupId, parentId: parentId, id: conversationId}
				
			}).done(function(data) {
				
				$('#post_container_' + conversationId).html(data);
				
				//destroy ckeditor
				CKEDITOR.instances.post_edit.destroy();
				
				//remove all observers
				$('#editor_ok').unbind();
				$('#editor_cancel').unbind();
	
				//remove the edit-in-place dom object
				$('#edit_in_place').remove();
				
				$('#post_container_' + conversationId).show();
				
			});
			
		}
		
	});
	
}

function cancelEditConversation() {
	
	//destroy ckeditor
	CKEDITOR.instances.post_edit.destroy();
	
	//remove all observers
	$('#editor_ok').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();
	
	//show the original static category name
	$('#post_container_' + lastConversation).show();
	
}

function deleteConversation(conversationId) {
	
	$.ajax({
		
		url: '/ajaxDeleteGroupConversationPost.php',
		type: 'post',
		dataType: 'script',
		data: {id: conversationId}
		
	}).done(function() {
		
		regenerateList(last_s, '');
		
	});
	
}

function autoSave() {
	
	if (CKEDITOR.currentInstance) {
		
		if (CKEDITOR.currentInstance.checkDirty()) {
			
			content = CKEDITOR.currentInstance.getData();
			
			$.ajax({
				
				url: '/ajaxAutoSave.php',
				type: 'post',
				dataType: 'text',
				data: {content: content}
				
			});
	
		}
		
	}
	
}

function autoSaveLoad() {
	
	$.ajax({
		
		url: '/ajaxAutoSaveLoad.php',
		type: 'post',
		dataType: 'script'
		
	});
	
}

$(document).ready( function() {
	
	regenerateList('', '');
	editConversationSettings();
	initializeReply();
	addConversation();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});