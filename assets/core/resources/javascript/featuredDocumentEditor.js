function objectSortableOn() {
		
	$('#featured_documents_list').sortable({
		
		update: function() {
			
			$.ajax({
				
				url: '/ajaxChangeFeaturedDocumentOrder.php',
				type: 'post',
				dataType: "text",
				data: $('#featured_documents_list').sortable('serialize')
				
			});
			
		},
		handle: '.handle',
		revert: true,
		scroll: true
		
	});
	
}

function toggleActiveState(id) {
	
	$.ajax({
		
		url: '/ajaxToggleDocumentFeatureState.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		$('#active_state_' + id).html(data);
		
	});
	
}

function removeFeaturedDocument(id) {
	
	$.ajax({
		
		url: '/ajaxDeleteFeaturedDocument.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function() {
		
		regenerateList();
		
	});
	
}

function initEditFeaturedDocumentOptions(id) {
	
	//handle existing editor (if necessary)
	if ($('#edit_featured_document_options_in_place').length > 0 && editFeaturedDocumentOptionsViewLock == 1) {
		
		cancelEditFeaturedDocumentOptions();
		
		//if lastFeaturedDocumentOptions (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
		if (id == lastFeaturedDocumentOptions) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_featured_document_options_in_place').length == 0) {
		
		//keep track of last featured document being viewed
		lastFeaturedDocumentOptions = id;
		
		var showEditor = '<div id="edit_featured_document_options_in_place" style="display:none;"></div>';
		
		//create the editable fields
		$(showEditor).insertAfter('#featured_document_' + id);
		
		$.ajax({
			
			url: '/ajaxShowFeaturedDocumentOptions.php',
			type: 'post',
			dataType: 'text',
			data: {id: id}
			
		}).done(function(data) {
			
			$('#edit_featured_document_options_in_place').html(data);
			
			//set the lock
			editFeaturedDocumentOptionsViewLock = 1;
			
			//display editor
			$('#edit_featured_document_options_in_place').fadeIn(500);
			
			//watches the submit button
			$('#edit_featured_document_options_form').bind('submit', function(e) {
				
				//e.stop prevents the submit action from executing 
				e.preventDefault();
				
				$.ajax({
					
					url: this.action,
					type: 'post',
					dataType: 'script',
					data: $(this).serialize()
					
				});
				
			});
		
			$('#edit_featured_document_options_cancel').bind('click', function() {cancelEditFeaturedDocumentOptions();});
			
			//watches the start date selector
			$('#start_date_selector').bind('click', function(e) {

				showSelectorCalendar(e, '#startMonth', '#startDay', '#startYear');

			});

			//watches the expire date selector
			$('#expire_date_selector').bind('click', function(e) {

				showSelectorCalendar(e, '#expireMonth', '#expireDay', '#expireYear');

			});
			
		});
		
	}
	
}

function cancelEditFeaturedDocumentOptions() {
	
	//remove all observers for edit_user and cancel
	$('#edit_featured_document_options_form').unbind();
	$('#edit_featured_document_options_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_featured_document_options_in_place').remove();
	
	//set the lock
	editFeaturedDocumentOptionsViewLock = 0;
	
}

function regenerateList() {
	
	//grab form fields
	var documentType = $('#documentType').val();
	var category = $('#categories').val();
	var subcategory = $('#subcategories').val();
	var subject = $('#subjects').val();
	
	$.ajax({
		
		url: '/ajaxFeaturedDocumentEditor.php',
		type: 'post',
		dataType: 'text',
		data: {documentType: documentType, category: category, subcategory: subcategory, subject: subject}
		
	}).done(function(data) {
		
		$('#featured_documents_list').html(data);
		objectSortableOn();
		
	});
	
}

function updateDocumentType() {
	
	regenerateList();
	
}

function updateCategories() {
	
	if($('#categories').val() != "") {
		
		updateSubcategories();
		
	} else {
		
		$('#subcategories').html('<option value="">All</option>');
		$('#subjects').html('<option value="">All</option>');
		
		regenerateList();
		
	}
	
}

function updateSubcategories() {
	
	$.ajax({
		
		url: '/ajaxAdminSubcategorySelectList.php',
		type: 'post',
		dataType: 'text',
		data: {category: $('#categories').val()}
		
	}).done(function(data) {
		
		$('#subcategories').html(data);
		updateSubjects();
		
	});
	
}

function updateSubjects() {
	
	$.ajax({
		
		url: '/ajaxAdminSubjectSelectList.php',
		type: 'post',
		dataType: 'text',
		data: {category: $('#categories').val(), subcategory: $('#subcategories').val()}
		
	}).done(function(data) {
		
		$('#subjects').html(data);
		regenerateList();
		
	});
	
}

$(document).ready(function() {
	
	//watches the category and subcategory fields
	$('#documentType').bind('change', updateDocumentType);
	$('#categories').bind('change', updateCategories);
	$('#subcategories').bind('change', updateSubjects);
	$('#subjects').bind('change', regenerateList);
	
	regenerateList();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});