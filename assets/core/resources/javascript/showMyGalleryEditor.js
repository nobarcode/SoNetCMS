function objectSortableOn() {
				   
	$('#images_list').sortable({
		
		update: function() {
				
			var values = $('#images_list').sortable('serialize');
			values += '&s=' + last_s;
			
			$.ajax({
				url: '/ajaxChangeUserImageOrder.php',
				type: 'post',
				dataType: "script",
				data: values
			
			});
			
		},
		placeholder: 'sortable_placeholder',
		handle: '.image',
		revert: true,
		scroll: true,
		tolerance: 'pointer',
		create:function(){
			
			//set the size of the container to prevent scrollbar jumping while sorting
			$(this).height($(this).height());
			
		},
		start: function (e, ui) {
			
			if (ui.item.hasClass('column_separator')) {
									   
				ui.placeholder.addClass('column_separator');
									   
			}
			
		},
		change: function() {
			
			addThumbClasses();
			
		},
		stop: function() {
			
			addThumbClasses();
			
		}
		
	});
	
}

function addThumbClasses() {
	
	$('#images_list').children().not('.ui-sortable-helper').each(function(index, el){
		
		if ((index + 1) % 5 != 0) {
			
			$(el).addClass('column_separator');
			
		} else {
			
			$(el).removeClass('column_separator');
			
		}
		
	});
	
}

function toggleInSeries(imageId) {
	
	$.ajax({
		
		url: '/ajaxToggleInSeriesUserImage.php',
		type: 'post',
		dataType: 'text',
		data: {imageId: imageId}
		
	}).done(function(data) {
		
		if (data == '0') {
			
			$('#in_series_text_' + imageId).html('<span class="add_to_series"><a href="javascript:toggleInSeries(\'' + imageId + '\');">Add to Gallery</a></span>');
			
		} else {
			
			$('#in_series_text_' + imageId).html('<span class="in_series"><a href="javascript:toggleInSeries(\'' + imageId + '\');">In Gallery</a></span>');
			
		}
		
	});
	
}

function editImage(imageId) {
	
	if (CKEDITOR.instances.documentBody.checkDirty()) {
		
		if(!confirm("Are you sure you want to cancel this editing session?\n\nThe changes you made will be lost if you continue.\n\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.")) {
			
			return;
			
		}
		
	}
	
	if ($('#fullsize_image').length > 0) {
		
		$('#fullsize_image').remove();
		
	}
	
	//show fullsize image
	$('<div id="fullsize_image"></div>').insertBefore('#message_box');
	
	$('#message_box').hide();
	
	//looks for any divs that have a class of 'image_container_selected', if there are, remove the class and set it to the regular container
	if ($('div.image_container_editing').length > 0) {
		
		$('#image_' + lastSelection).toggleClass('image_container_editing');
		
	}
	
	$('#image_' + imageId).toggleClass('image_container_editing');
	lastSelection = imageId;
	
	$.ajax({
		
		url: '/ajaxLoadUserImageInfo.php',
		type: 'post',
		dataType: 'script',
		data: {imageId: imageId}
		
	}).complete(function() {
		
		if (!$('#image_editor_container').is(":visible")) {
			
			$('#image_editor_container').fadeIn(500);
			
		}
		
	});
	
}

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

function cancelEditing() {
	
	if (CKEDITOR.instances.documentBody.checkDirty()) {
		
		if(!confirm("Are you sure you want to cancel this editing session?\n\nThe changes you made will be lost if you continue.\n\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.")) {
			
			return;
			
		}
		
	}
	
	//clear the fullsize image
	$('#fullsize_image').remove();
	
	//reset the the form and clear update_image_id hidden field
	$('#newDocumentForm')[0].reset();
	$('#update_image_id').val('');
	
	//clear the editor window
	//clear undo history and disable buttons
	//set editor to not dirty
	CKEDITOR.instances.documentBody.setData('', function() {CKEDITOR.instances.documentBody.resetUndo(); CKEDITOR.instances.documentBody.resetDirty();});
	
	//hide the message box, then remove editing image box and cancel editing button
	$('#message_box').hide();
	$('#cancel_editing').remove();
	
	//clear the editor options and hide the div
	$('#editor_options').html('');
	$('#editor_options').hide();
	
	//remove editor class from the thumbnail
	$('#image_' + lastSelection).toggleClass('image_container_editing');
	
}

function deleteImage(imageId) {
	
	$.ajax({
		
		url: '/ajaxDeleteUserImage.php',
		type: 'post',
		data: {imageId: imageId}
		
	}).complete(function(){
		
		regenerateList(last_s, '');
			
		//if the image being deleted (imageId) is the image currently being edited (hidden field update_image_id), cancel editing
		if ($('#update_image_id').val() == imageId) {
			
			//clear the fullsize image
			$('#fullsize_image').remove();
			
			//clear the editor window
			//clear undo history and disable buttons
			//set editor to not dirty
			CKEDITOR.instances.documentBody.setData('', function() {CKEDITOR.instances.documentBody.resetUndo(); CKEDITOR.instances.documentBody.resetDirty();});
			
			//clear the update_image_id hidden field
			$('#update_image_id').val('');
			
			//hide the message box, then remove editing image box and cancel editing button
			$('#message_box').hide();
			$('#cancel_editing').remove();

			//clear the editor options and hide the div
			$('#editor_options').html('');
			$('#editor_options').hide();
			
		}
		
	});
	
}

function showImageEditor() {
	
	if (!$('#image_editor_container').is(":visible")) {
		
		$('#image_editor_container').fadeIn(500);
		
	} else {
		
		$('#image_editor_container').hide();
		
	}
	
}

function ajaxSave() {
	
	var values = $('#newDocumentForm').serialize();
	values += '&documentBody=' + encodeURIComponent(CKEDITOR.instances.documentBody.getData());
	
	$.ajax({
		
		url: '/ajaxAddUserImage.php',
		type: 'post',
		dataType: 'script',
		data: values
		
	});
 	
}

function regenerateList(s, d) {
	
	//check if anything is being edited; if there is, cancel it
	if ($('div.image_container_editing').length > 0) {
		
		cancelEditing();
		
	}
	
	$.ajax({
		
		url: '/ajaxShowMyGalleryEditor.php',
		type: 'post',
		dataType: 'html',
		data: {s: s, d: d}
		
	}).done(function(data){
		
		$('#images_list_container').html(data);
		objectSortableOn();
		
	});
	
}

function displayEditor() {
	
	$('#loading_editor_message').hide();
	$('#editor_button').show();
	setInterval(autoSave, 30000);
	
}

function autoSave() {
	
	if (CKEDITOR.instances.documentBody.checkDirty()) {
		
		content = CKEDITOR.instances.documentBody.getData();
				
		$.ajax({
			
			url: '/ajaxAutoSave.php',
			type: 'post',
			dataType: 'text',
			data: {content: content}
			
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
	
	regenerateList();
	
	//load the RTE
	initializeEditor();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});