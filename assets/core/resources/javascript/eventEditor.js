function clearEditTracking() {
	
	$.post('/ajaxDeleteEditTracking.php', {documentType: 'document', id: id}, function(data) {
		
		$('#edit_tracking_container').hide();
		
	});
	
}

function updateSubcategories() {
	
	$.post('/ajaxSubcategorySelectListUpdater.php', {category: $('#categories').val()}, function(data) {
		
		$('#subcategories').html(data);
		
	}).complete(function() {
		
		updateSubjects();
		
	});

	
}

function updateSubjects() {
	
	$.post('/ajaxSubjectSelectListUpdater.php', {category: $('#categories').val(), subcategory: $('#subcategories').val()}, function(data) {
		
		$('#subjects').html(data);
		
	});
	
}

function openFileManager(callbackFunction, elementName) {

	openServerBrowser(
	
		'/assets/core/resources/filemanager/index.html?callbackFunction=' + callbackFunction + '&elementName=' + elementName,
		screen.width * 0.7,
		screen.height * 0.7
		
	);
	
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

function ajaxSave() {
	
	var values = $('#newDocumentForm').serialize();
	values += '&documentBody=' + encodeURIComponent(CKEDITOR.instances.documentBody.getData());
	
	if (CKEDITOR.instances.customHeader) {	
		
		values += '&customHeader=' + encodeURIComponent(CKEDITOR.instances.customHeader.getData());
		
	} else {
		
		values += '&customHeader=' + encodeURIComponent($('#customHeader').html());
		
	}
	
	$.ajax({
		
		url: '/ajaxAddEvent.php',
		type: 'post',
		dataType: 'script',
		data: values
		
	});
	
}

function toggleCustomHeaderEditor() {
	
	if (!CKEDITOR.instances.customHeader) {
		
		cancelCustomHeader = $('#customHeader').html();
		initializeCustomHeaderEditor();
		$('#activate_custom_header_editor').hide();
		$('<div id="custom_header_editor_options"><input type="button" id="save_custom_header_editor" value="Save" onClick="toggleCustomHeaderEditor();"><input type="button" id="cancel_custom_header_editor" value="Cancel" onClick="cancelCustomHeaderEditor();"></div>').insertAfter('#activate_custom_header_editor');
		
	} else {
		
		CKEDITOR.instances.customHeader.destroy();
		$('#activate_custom_header_editor').show();
		$('#custom_header_editor_options').remove();
		
	}
	
}

function cancelCustomHeaderEditor() {
	
	if (CKEDITOR.instances.customHeader) {
		
		var confirmation;
		
		if (CKEDITOR.instances.customHeader.checkDirty()) {
			
			confirmation = confirm('Are you sure you want to cancel this editing session?\n\nThe changes you made will be lost if you continue.\n\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.');
			
		}
		
		if (confirmation || !CKEDITOR.instances.customHeader.checkDirty()) {
			
			CKEDITOR.instances.customHeader.destroy();
			$('#activate_custom_header_editor').show();
			$('#customHeader').html(cancelCustomHeader);
			$('#custom_header_editor_options').remove();
			
		}
		
	}
	
}

function togglePublishState(id) {
	
	$.ajax({
		
		url: '/ajaxToggleEventPublishState.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		if (data == 'Published') {
			
			$('#publish_state_' + id).html('Unpublish');
			
		} else {
			
			$('#publish_state_' + id).html('Publish');
			
		}
		
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
		data: {id: id, documentType: 'event', s: s, d: d}
		
	}).done(function(data) {
		
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
			data: {id: id, documentType: 'event', version: version}
			
		});
		
	}
	
}

function deleteVersion(version) {
	
	$.ajax({
		
		url: '/ajaxDeleteDocumentVersion.php',
		type: 'post',
		dataType: 'script',
		data: {id: id, documentType: 'event', version: version, currentVersion: currentVersion}
		
	});
	
}

function deleteVersionAll() {
	
	$.ajax({
		
		url: '/ajaxDeleteDocumentVersionAll.php',
		type: 'post',
		dataType: 'script',
		data: {id: id, documentType: 'event'}
		
	});
	
}

function displayEditor() {
	
	$.ajax({
		
		url: '/ajaxLoadEventEditorContent.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		initializeEditor();
		
		if (id != '') {
			
			CKEDITOR.instances.documentBody.setData(data);
			
		}
		
		setInterval(autoSave, 30000);
		
		$('#loading_editor_message').hide();
		$('#editor_container').show();
		
	});
	
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
	
	//watches the category and subcategory fields
	$('#categories').bind('change', updateSubcategories);
	$('#subcategories').bind('change', updateSubjects);
	
	//wathes the submit button
	$('#newDocumentForm').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing
		e.preventDefault();
		
	});
	
	//watches the start date selector
	$('#start_date_selector').bind('click', function(e) {
		
		showSelectorCalendar(e, '#startMonth', '#startDay', '#startYear');
		
	});
	
	//watches the expire date selector
	$('#expire_date_selector').bind('click', function(e) {
		
		showSelectorCalendar(e, '#expireMonth', '#expireDay', '#expireYear');
		
	});
	
	//generates version list
	regenerateVersionList(id, '', '');
	
	//load the RTE
	displayEditor();
	
});

window.onbeforeunload = function() {
	
	if (id != '') {
		
		$.ajax({
			
			async: false,
			url: '/ajaxClearMyEditTracking.php',
			type: 'post',
			dataType: 'script',
			data: {documentType: 'event', id: id}
			
		});
		
	}
	
}

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});