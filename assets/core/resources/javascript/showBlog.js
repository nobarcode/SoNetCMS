function togglePublishState() {
	
	$.ajax({
		
		url: '/ajaxToggleBlogPublishState.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		if (data == 'Published') {
			
			$('#unpublished_message').hide();
			
		}
		
	});
	
}

function regenerateCalendar(selectedMonth, selectedYear) {
	
	$.ajax({
		
		url: '/ajaxShowBlogCalendar.php',
		type: 'post',
		dataType: 'text',
		data: {getMonth: selectedMonth, getYear: selectedYear, usernameCreated: usernameCreated}
		
	}).done(function(data) {
		
		$('#calendar_container').html(data);
		regenerateBlogList(month, day, year, '', '');
		
	});
	
}

function regenerateBlogList(selectedMonth, selectedDay, selectedYear, s, d) {
	
	$.ajax({
		
		url: '/ajaxShowBlogList.php',
		type: 'post',
		dataType: 'text',
		data: {month: selectedMonth, day: selectedDay, year: selectedYear, s: s, d: d, usernameCreated: usernameCreated}
		
	}).done(function(data) {
		
		$('#blog_list').html(data);
		
		viewportOffsetList = viewportOffset($('#list_container')).top;
		viewportOffsetLeftColumn = viewportOffset($('#left_column_container')).top;
		heightLeftColumn = $('#left_column_container').height();
		listOffset = viewportOffsetList - viewportOffsetLeftColumn;
		
		heightList = $('#list_container').height();
		
		if (heightList > 249) {
			
			if ((heightLeftColumn - listOffset) - 26 > 249) {
				
				if (heightList > (heightLeftColumn - listOffset) - 26) {
					
					heightList = (heightLeftColumn - listOffset) - 26;
						
				}
				
			} else {
				
				heightList = 249;
				
			}
			
		}
		
		$('#list').height(heightList);
		
	});
	
	month = selectedMonth;
	day = selectedDay;
	year = selectedYear;
	
}

function viewportOffset(elm) {
	
	var win = $(window);
	var offset = $(elm).offset();
	
	return {
		
		left: offset.left - win.scrollLeft(),
		top: offset.top - win.scrollTop()
		
	};
	
}

function showCommentsList() {
	
	if (!$('#comments_main_container').is(":visible")) {
		
		$('#comments_main_container').fadeIn(500);
		$('#comment_toggle_navigation').hide();
		
	} else {
		
		$('#comments_main_container').hide();
		$('#comment_toggle_navigation').show();
				
	}
	
}

function regenerateCommentsList(s, d) {
	
	$.ajax({
		
		url: '/ajaxShowComments.php',
		type: 'post',
		dataType: 'html',
		data: {id: id, type: 'blogComment', commentFilter: $('#commentFilter').val(), s: s, d: d}
		
	}).done(function(data) {
		
		$('#comments_container').html(data);
		
	});
	
}

function initEditComment(commentId) {
	
	//handle existing editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		//remove all observers
		$('#editor_ok').unbind();
		$('#editor_cancel').unbind();

		//remove the edit-in-place dom object
		$('#edit_in_place').remove();
		$('#comment_container_' + lastComment).show();
		
	}
	
	//check if row separator was used on the comment container
	if ($('#comment_container_' + commentId).hasClass('comment_row_separator')) {
		
		editorClass = ' class="comment_row_separator"';
		
	} else {
		
		editorClass = '';
		
	}
	
	//hide the current static field
	$('#comment_container_' + commentId).hide();
	
	//create the editable text and ok & cancel buttons
	$('<div id="edit_in_place"' + editorClass + '><textarea id="comment_edit" class="comment_edit" rows="10">' + $('#comment_' + commentId).html().replace(/"/g,'&quot;').replace(/\'/g,'&#039;').replace(/\n|\r/gi,'').replace(/<br>/gi,'\n') + '</textarea><br><input type="button" id="editor_ok" value="ok"> <input type="button" id="editor_cancel" value="cancel"></div>').insertBefore('#comment_container_' + commentId);
	
	//assign the category being edited to a global variable
	lastComment = commentId;
	
	//create observers for ok and cancel buttons
	$('#editor_ok').bind('click', function() {editComment(commentId);});
	$('#editor_cancel').bind('click', function() {cancelEditComment();});
	
}

function editComment(commentId) {
	
	$.ajax({
		
		url: '/ajaxUpdateComment.php',
		type: 'post',
		dataType: 'script',
		data: {id: commentId, value: $('#comment_edit').val(), type: 'blogComment'}
		
	}).done(function() {
		
		if ($('#edit_in_place').length > 0) {

			//remove all observers
			$('#editor_ok').unbind();
			$('#editor_cancel').unbind();

			//remove the edit-in-place dom object
			$('#edit_in_place').remove();
			
			//show the comment
			$('#comment_container_' + lastComment).show();

		}
		
	});
	
}

function cancelEditComment() {
	
	//remove all observers
	$('#editor_ok').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();
	
	//show the original static category name
	$('#comment_container_' + lastComment).show();
	
}

function deleteComment(id) {
	
	$.ajax({
		
		url: '/ajaxDeleteComment.php',
		type: 'post',
		dataType: 'text',
		data: {id: id, type: 'blogComment'}
		
	}).done(function() {
		
		regenerateCommentsList(last_s, '');
		regenerateCommentCount('total_comments', 'blog', id);
		
	});
	
}

function vote(elementId, type, documentId, vote) {
	
	$.ajax({
		
		url: '/ajaxDocumentVoting.php',
		type: 'post',
		dataType: 'script',
		data: {elementId: elementId, type: type, id: documentId, vote: vote}
		
	});
	
}

function regenerateCommentCount(elementId, type, id) {
	
	$.ajax({
		
		url: '/ajaxShowCommentCount.php',
		type: 'post',
		dataType: 'script',
		data: {elementId: elementId, type: type, id: id}
		
	});
	
}

function showAddComment() {
	
	if (!$('#add_comment_container').is(":visible")) {
		
		$('#add_comment_container').fadeIn(500);
		$('#add_comment_navigation').hide();
		
		
	} else {
		
		$('#add_comment_container').hide();
		$('#add_comment_navigation').show();
				
	}
	
}

function addComment() {
	
	//watches the submit button
	$('#add_comment').bind('submit', function(e) {

		//e.stop prevents the submit action from executing 
		e.preventDefault();
		
		$.ajax({
			
			url: '/ajaxAddComment.php',
			type: 'post',
			dataType: 'script',
			data: $('#add_comment').serialize()
			
		}).done(function() {
			
			regenerateCommentsList('last', '');
			regenerateCommentCount('total_comments', 'blog', id);
			$('#add_comment')[0].reset();
			
		});
		
	});
	
}

$(document).ready( function() {
	
	regenerateCommentsList(0, '');
	addComment();
	regenerateCalendar(month, year);
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});