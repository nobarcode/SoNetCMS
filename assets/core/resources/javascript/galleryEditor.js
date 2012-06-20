function objectSortableOn() {
		
	$('#images_list').sortable({
		
		update: function() {
			
			var values = $('#images_list').sortable('serialize');
			values += '&id=' + id + '&s=' + last_s;
			
			$.ajax({
				
				url: '/ajaxChangeImageOrder.php',
				type: 'post',
				dataType: "script",
				data: values
				
			});
			
		},
		placeholder: 'sortable_placeholder',
		handle: '.image',
		revert: true,
		scroll: true,
		tolerance: 'pointer'
		
	});
	
}

function toggleInSeries(imageId) {
	
	$.ajax({
		
		url: '/ajaxToggleInSeriesImage.php',
		type: 'post',
		dataType: 'text',
		data: {id: id, imageId: imageId}
		
	}).done(function(data) {
		
		if (data == '0') {
			
			$('#in_series_text_' + imageId).html('<span class="add_to_series"><a href="javascript:toggleInSeries(\'' + imageId + '\');">Add to Series</a></span>');
			
		} else {
			
			$('#in_series_text_' + imageId).html('<span class="in_series"><a href="javascript:toggleInSeries(\'' + imageId + '\');">In Series</a></span>');
			
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
		
		url: '/ajaxLoadImageInfo.php',
		type: 'post',
		dataType: 'script',
		data: {id: id, imageId: imageId}
		
	}).complete(function() {
		
		regenerateVersionList(imageId, '', '');
		
		if (!$('#image_editor_container').is(":visible")) {
			
			$('#image_editor_container').fadeIn(500);
			
		}
		
		$('#versioning_options').show();
		
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
	
	//hide the versions box and reset
	$('#versioning_options').hide();
	$('#selected_version').html('');
	$('#version_choices').html('');
	
}

function deleteImage(imageId) {
	
	$.ajax({
		
		url: '/ajaxDeleteImage.php',
		type: 'post',
		data: {id: id, imageId: imageId}
		
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
			$('#update_image_id').val('')();
			
			//hide the message box, then remove editing image box and cancel editing button
			$('#message_box').hide();
			$('#cancel_editing').remove();

			//clear the editor options and hide the div
			$('#editor_options').html('');
			$('#editor_options').hide();
			
			//hide the versions box and reset
			$('#versioning_options').hide();
			$('#selected_version').html('');
			$('#version_choices').html('');
			
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
		
		url: '/ajaxAddImage.php',
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
		
		url: '/ajaxGalleryEditor.php',
		type: 'post',
		dataType: 'html',
		data: {s: s, d: d, id: id}
		
	}).done(function(data){
		
		$('#images_list_container').html(data);
		objectSortableOn();
		
	});
	
}

function displayVersionOptions() {
	
	if (!$('#version_choices').is(':visible')) {
		
		$('#version_choices').fadeIn(500);
		$('#versions_navigation').html('Hide Versions');
		
		
	} else {
		
		$('#version_choices').hide();
		$('#versions_navigation').html('Show Versions');
				
	}
	
}

function regenerateVersionList(id, s, d) {
	
	$.ajax({
		
		url: '/ajaxShowVersionList.php',
		type: 'post',
		dataType: 'text',
		data: {id: id, documentType: 'documentImage', s: s, d: d}
		
	}).done(function(data){
		
		$('#version_choices').html(data);
		
	});
	
}


function changeVersion(version) {
	
	var confirmation;
	
	if (CKEDITOR.instances.documentBody.checkDirty()) {
		
		confirmation = confirm('Are you sure you want to cancel this editing session?\n\nThe changes you made will be lost if you continue.\n\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.');
		
	}
	
	if (confirmation || !CKEDITOR.instances.documentBody.checkDirty()) {
		
		$.ajax({
			
			url: '/ajaxLoadDocumentVersioning.php',
			type: 'post',
			dataType: 'script',
			data: {id: update_image_id, documentType: 'documentImage', version: version}
			
		});
		
	}
	
}

function deleteVersion(version) {
	
	$.ajax({
		
		url: '/ajaxDeleteDocumentVersion.php',
		type: 'post',
		dataType: 'script',
		data: {id: update_image_id, documentType: 'documentImage', version: version, currentVersion: currentVersion}
		
	});
	
}

function deleteVersionAll() {
	
	$.ajax({
		
		url: '/ajaxDeleteDocumentVersionAll.php',
		type: 'post',
		dataType: 'script',
		data: {id: update_image_id, documentType: 'documentImage'}
		
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

window.onbeforeunload = function() {
	
	if (id != '') {
		
		$.ajax({
			
			async: false,
			url: '/ajaxClearMyEditTracking.php',
			type: 'post',
			dataType: 'script',
			data: {documentType: 'document', id: id}
			
		});
		
	}
	
}

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});