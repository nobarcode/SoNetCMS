function objectSortableOn() {
		
	$('#document_types_list').sortable({
		
		update: function() {
			
			$.ajax({
				
				url: '/ajaxChangeDocumentTypeOrder.php',
				type: 'post',
				dataType: "text",
				data: $('#document_types_list').sortable('serialize')
				
			});
			
		},
		handle: '.handle',
		revert: true,
		scroll: true
		
	});
	
}

function initEditDocumentType(documentType, documentTypeId) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		cancelEditDocumentType();
		
	}
	
	if ($('#edit_in_place').length > 0) {

		cancelEditDocumentType();
		
	}
	
	//hide the current static field
	$('#' + documentTypeId).hide();
	
	//create the editable text and ok & cancel buttons
	$('<span id="edit_in_place"><input style="width:450px;" type="text" id="document_type_edit" value="' + $('#' + documentTypeId).html().replace(/"/g,'&quot;').replace(/\'/g,'&#039;') + '"> <input type="button" id="editor_ok" value="ok"> <input type="button" id="editor_cancel" value="cancel"></span>').insertAfter('#' + documentTypeId);
	
	//assign the category being edited to a global variable
	lastDocumentType = documentType;
	lastDocumentTypeId = documentTypeId;
	
	//create observers for ok and cancel buttons
	$('#editor_ok').bind('click', function() {editDocumentType();});
	$('#editor_cancel').bind('click', function() {cancelEditDocumentType();});	
	
}

function editDocumentType() {
	
	$.ajax({
		
		url: '/ajaxUpdateDocumentType.php',
		type: 'post',
		dataType: 'script',
		data: {documentType: lastDocumentType, value: $('#document_type_edit').val()}
		
	});
	
}

function cancelEditDocumentType() {
	
	//remove all observers
	$('#editor_ok').unbind();
	$('#editor_cancel').unbind();
		
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();
	
	//show the original static category name
	$('#' + lastDocumentTypeId).show();
	
}

function deleteDocumentType(documentType) {
	
	$.ajax({
		
		url: '/ajaxDeleteDocumentType.php',
		type: 'post',
		dataType: 'text',
		data: {documentType: documentType}
		
	}).done(function() {
		
		regenerateList();
		
	});
	
	editDocumentTypeOptionsViewLock = 0;
	
}

function initEditDocumentTypeOptions(documentType) {
	
	//handle existing editor (if necessary)
	if ($('#edit_document_type_options_in_place').length > 0 && editDocumentTypeOptionsViewLock == 1) {
		
		cancelEditDocumentTypeOptions();
		
		//if lastDocumentType (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
		if (documentType == lastDocumentTypeOptions) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_document_type_options_in_place').length == 0) {
		
		//keep track of last document type options being viewed
		lastDocumentTypeOptions = documentType;
		
		//create the editable fields
		$('<div id="edit_document_type_options_in_place" style="display:none;"></div>').insertAfter('#document_type_' + documentType);
		
		$.ajax({
			
			url: '/ajaxShowDocumentTypeOptions.php',
			type: 'post',
			dataType: 'text',
			data: {documentType: documentType}
			
		}).done(function(data) {
			
			$('#edit_document_type_options_in_place').html(data);
			
			//set the lock
			editDocumentTypeOptionsViewLock = 1;
			
			$('#edit_document_type_options_in_place').fadeIn(500);
		
			//watches the submit button
			$('#edit_document_type_options_form').bind('submit', function(e) {

				//e.stop prevents the submit action from executing
				e.preventDefault();
				
				$.ajax({
					
					url: this.action,
					type: 'post',
					dataType: 'script',
					data: $(this).serialize()
					
				});
				
			});
		
			$('#edit_document_type_options_cancel').bind('click', function() {cancelEditDocumentTypeOptions();});
			
		});
		
	}
	
}

function cancelEditDocumentTypeOptions() {
	
	//remove all observers for edit_user and cancel
	$('#edit_document_type_options_form').unbind();
	$('#edit_document_type_options_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_document_type_options_in_place').remove();
	
	//set the lock
	editDocumentTypeOptionsViewLock = 0;
	
}

function showAddDocumentType() {
	
	if (!$('#add_document_type_container').is(":visible")) {
		
		$('#add_document_type_container').fadeIn(500);
		
		
	} else {
		
		$('#add_document_type_container').hide();
				
	}
	
}

function addDocumentType() {
	
	//wathes the submit button
	$('#add_document_type').bind('submit', function(e) {
		
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

function regenerateList() {
	
	//check if anything is being edited; if there is, cancel it
	if ($('#edit_in_place').length > 0) {
		
		 cancelEditDocumentType();
		
	}
	
	if ($('#edit_document_type_options_in_place').length > 0) {
		
		cancelEditDocumentTypeOptions();
		
	}
	
	$.ajax({
		
		url: '/ajaxDocumentTypeEditor.php',
		type: 'post',
		dataType: 'text',
		data: $(this).serialize()
		
	}).done(function(data) {
		
		$('#document_types_list').html(data);
		objectSortableOn();
		
	});
	
}

$(document).ready(function() {
	
	regenerateList();
	addDocumentType();
	
});

//show the loading message whenever anything ajax is happening
$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});