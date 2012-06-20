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

function regenerateDocumentList(s, d, shortcut, status, title, dateCreated, author, orderBy, change) {
	
	//grab form fields
	var documentType = $('#documentType').val();
	var category = $('#categories').val();
	var subcategory = $('#subcategories').val();
	var subject = $('#subjects').val();
	var filterType = $('#filterType').val();
	var filterValue = $('#filterValue').val();
	
	$.ajax({
		
		url: '/ajaxSelectDocument.php',
		type: 'post',
		dataType: 'text',
		data: {documentType: documentType, category: category, subcategory: subcategory, subject: subject, filterType: filterType, filterValue: filterValue, s: s, d: d, shortcut: shortcut, status: status, title: title, dateCreated: dateCreated, author: author, orderBy: orderBy, change: change}
		
	}).done(function(data) {
		
		$('#document_list').html(data)
		
	});
	
}

function selectDocument(shortcut) {
	
	if (top.location.href != window.location.href) {
		
		openerValue = parent.window.opener;
		openerValueString = 'parent.window.opener.';
		openerValueClose = parent.window;
		
	} else {
		
		openerValue = window.opener;
		openerValueString = 'window.opener.';
		openerValueClose = parent.window;
		
	}
		
	
	if(getUrlParameters('CKEditor')) {
		
		// use CKEditor 3.0 integration method
		openerValue.CKEDITOR.tools.callFunction(getUrlParameters('CKEditorFuncNum'), shortcut);
		
	} else if (getUrlParameters('callbackFunction') !== null && getUrlParameters('elementName') !== null) {
		
		eval(openerValueString + getUrlParameters('callbackFunction') + '("' + getUrlParameters('elementName') + '", "' + shortcut + '");');
		
	}
	
	openerValueClose.close();
	
}

function getUrlParameters(parameter) {
	
	var search = window.location.search.substring(1);
	
	if(search.indexOf('&') > -1) {
		
		var parameters = search.split('&');
		
		for (var i = 0; i < parameters.length; i++) {
			
			var key_value = parameters[i].split('=');
			
			if(key_value[0] == parameter) {
				
				return key_value[1];
				
			}
			
		}
		
	} else {
		
		var parameters = search.split('=');
		
		if(parameters[0] == parameter) {
			
			return parameters[1];
			
		}
		
	}
	
	return null;
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