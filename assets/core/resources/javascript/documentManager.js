function updateDocumentType() {
	
	regenerateDocumentList('', '', 'desc','desc', 'desc', 'desc', 'desc', 'dateCreated', '');
	
}

function updateCategories() {
	
	if($('#categories').val() != "") {
		
		updateSubcategories();
		
	} else {
		
		$('#subcategories').html('<option value="">All</option>');
		$('#subjects').html('<option value="">All</option>');
		
		regenerateDocumentList('', '', 'desc','desc', 'desc', 'desc', 'desc', 'dateCreated', '');
		
	}
	
}

function updateSubcategories() {
	
	$.post('/ajaxAdminSubcategorySelectList.php', {category: $('#categories').val()}, function(data) {
		
		$('#subcategories').html(data);
		
	}).complete(function() {
		
		updateSubjects();
		
	});
	
}

function updateSubjects() {
	
	$.post('/ajaxAdminSubjectSelectList.php', {category: $('#categories').val(), subcategory: $('#subcategories').val()}, function(data) {
		
		$('#subjects').html(data);
		regenerateDocumentList('', '', 'desc','desc', 'desc', 'desc', 'desc', 'dateCreated', '');
		
	});
	
}

function togglePublishState(id) {
	
	$.post('/ajaxTogglePublishState.php', {id: id, showImage: 'yes'}, function(data) {
			
			$('#publish_state_' + id).html(data);
			
	});
	
}

function cloneDocument(selectedId, s, shortcut, status, title, dateCreated, author, orderBy) {
	
	$.ajax({
		
		url: '/ajaxCloneDocument.php',
		type: 'post',
		dataType: 'text',
		data: {id: selectedId}
		
	}).done(function() {
		
		regenerateDocumentList(s, '', shortcut, status, title, dateCreated, author, orderBy, '');
		
	});
	
}

function deleteDocument(selectedId, s, shortcut, status, title, dateCreated, author, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteDocument.php',
		type: 'post',
		dataType: 'text',
		data: {id: selectedId}
		
	}).done(function() {
		
		regenerateDocumentList(s, '', shortcut, status, title, dateCreated, author, orderBy, '');
		
	});
	
}

function toggleMultipleDocumentsStatus(s, shortcut, status, title, dateCreated, author, orderBy) {
	
	$.ajax({
		
		url: '/ajaxToggleMultipleDocumentsStatus.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleDocumentsAction').serialize()
		
	}).done(function() {
		
		regenerateDocumentList(s, '', shortcut, status, title, dateCreated, author, orderBy, '');
		
	});
	
}

function deleteMultipleDocuments(s, shortcut, status, title, dateCreated, author, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleDocuments.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleDocumentsAction').serialize()
		
	}).done(function() {
		
		regenerateDocumentList(s, '', shortcut, status, title, dateCreated, author, orderBy, '');
		
	});
	
}

function regenerateDocumentList(s, d, shortcut, status, title, dateCreated, author, orderBy, change) {
	
	//grab form fields
	var documentType = $('#documentType').val();
	var category = $('#categories').val();
	var subcategory = $('#subcategories').val();
	var subject = $('#subjects').val();
	var filterType = $('#filterType').val();
	var filterValue = $('#filterValue').val();
	
	$.ajax({
		
		url: '/ajaxDocumentManager.php',
		type: 'post',
		dataType: 'text',
		data: {documentType: documentType, category: category, subcategory: subcategory, subject: subject, filterType: filterType, filterValue: filterValue, s: s, d: d, shortcut: shortcut, status: status, title: title, dateCreated: dateCreated, author: author, orderBy: orderBy, change: change}
		
	}).done(function(data) {
		
		$('#document_list').html(data)
		
	});
	
}

$(document).ready(function() {
	
	//watches the category and subcategory fields
	$('#documentType').bind('change', updateDocumentType);
	$('#categories').bind('change', updateCategories);
	$('#subcategories').bind('change', updateSubjects);
	$('#subjects').bind('change', function() {
		
		regenerateDocumentList('', '', 'desc','desc', 'desc', 'desc', 'desc', 'dateCreated', '');
		
	});
	
	//watches the submit button
	$('#query_filter').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing 
		e.preventDefault();
		
		regenerateDocumentList('', '', 'desc','desc', 'desc', 'desc', 'desc', 'dateCreated', '');
		
	});
	
	regenerateDocumentList('', '', 'desc','desc', 'desc', 'desc', 'desc', 'dateCreated', '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});