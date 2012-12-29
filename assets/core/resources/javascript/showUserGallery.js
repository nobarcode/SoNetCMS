function previousThumbSet() {
	
	if (page > 0) {
		
		$("#thumbs").animate({"left": "+=180px"}, 500);
		
		page--;
		
		if (page == 0) {
			
			$('#thumbnail_arrow_left').css('visibility', 'hidden');
			
		}
		
		if (totalPages > 0 && page < totalPages) {
			
			$('#thumbnail_arrow_right').css('visibility', 'visible');
			
		}
		
	}
	
}

function nextThumbSet() {
	
	if (page < totalPages) {
		
		page++;
		
		//if this page hasn't been requested yet, use ajax to access the contents before scrolling to it
		if (highestPageRequested < page) {
			
			$('<div id="page_' + page + '" class=\"thumb_set\"></div>').insertAfter('#page_' + (page - 1));
			
			$.ajax({
				
				url: '/ajaxLoadGallerySet.php',
				type: 'post',
				dataType: 'html',
				data: {parentId: parentId, requestedPage: page, type: 'user'}
				
			}).done(function(data) {
				
				$('#page_' + page).html(data);
				
				highestPageRequested = page;
					
				$("#thumbs").animate({"left": "-=180px"}, 500);
				
			});
			
		} else {
			
			$("#thumbs").animate({"left": "-=180px"}, 500);
			
		}
		
		if (page == totalPages) {
			
			$('#thumbnail_arrow_right').css('visibility', 'hidden');
			
		}
		
		if (totalPages > 0 && page > 0) {
			
			$('#thumbnail_arrow_left').css('visibility', 'visible');
			
		}
		
	}
	
}

function showImage(imageId) {
	
	$('#image_' + lastSelection).removeClass('thumb_image_container_selected');
	
	$('#image_' + imageId).addClass('thumb_image_container_selected');
	
	lastSelection = imageId;
	
	$('#outer_content_container').height($('#main_content_container').height());
	
	$('#main_content_container').fadeTo(500, 0.0, function() {
		
		$.ajax({
			
			url: '/ajaxShowUserGalleryMainContent.php',
			type: 'post',
			dataType: 'html',
			data: {parentId: parentId, imageId: imageId}
			
		}).done(function(data) {
			
			$('#main_content_container').html(data);
			$('#main_image').load(fadeinMainImage());
			
		});
		
	});
	
}

function fadeinMainImage () {
	
	//create the height based on the combined height of all (possible) elements (plus their padding or margins) in the body of the document
	var height;
	$("<img/>") // Make in memory copy of image to avoid css issues
	.attr("src", $("#main_image").attr("src"))
	.load(function() {
		
		$('#main_content_container').fadeTo(500, 1.0);
		$('#outer_content_container').height($('#main_content_container').height());
		
	});
	
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
		
		url: '/ajaxShowCommentsImages.php',
		type: 'post',
		dataType: 'html',
		data: {parentId: parentId, id: imageId, type: 'userImageComment', commentFilter: $('#commentFilter').val(), s: s, d: d}
		
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
		data: {id: commentId, value: $('#comment_edit').val(), type: 'userImageComment'}
		
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
	$('editor_ok').stopObserving();
	$('editor_cancel').stopObserving();
	
	//remove the edit-in-place dom object
	$('edit_in_place').remove();
	
	//show the original static category name
	$('#comment_container_' + lastComment).show();
	
}

function deleteComment(commentId) {
	
	$.ajax({
		
		url: '/ajaxDeleteComment.php',
		type: 'post',
		dataType: 'text',
		data: {id: commentId, type: 'userImageComment'}
		
	}).done(function() {
		
		regenerateCommentsList(last_s, '');
		
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
			$('#add_comment')[0].reset();
			
		});
		
	});
	
}

$(document).ready(function() {
	
	showImage(imageId);
	regenerateCommentsList();
	addComment();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});