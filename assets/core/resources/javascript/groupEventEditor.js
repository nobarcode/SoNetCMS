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
		
		url: '/ajaxAddGroupEvent.php',
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
		
		url: '/ajaxToggleGroupEventPublishState.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, id: id}
		
	}).done(function(data) {
		
		if (data == 'Published') {
			
			$('#publish_state_' + id).html('Unpublish');
			
		} else {
			
			$('#publish_state_' + id).html('Publish');
			
		}
		
	});
	
}

function displayEditor() {
	
	$.ajax({
		
		url: '/ajaxLoadGroupEventEditorContent.php',
		type: 'post',
		dataType: 'text',
		data: {id: id, groupId: groupId}
		
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
	
	//load the RTE
	displayEditor();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});