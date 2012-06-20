function documentSearch() {
	
	//wathes the submit button
	$('#document_search').bind('change', function(e) {
		
		//e.stop prevents the submit action from executing 
		e.preventDefault();
		
		$.ajax({
			
			url: '/ajaxDocumentList.php',
			type: 'post',
			dataType: 'text',
			data: $('#document_search').serialize()
			
		}).done(function(data) {
			
			$('#documents_list').html(data);
			
		});
		
	});
	
}

function toggleSummary(elementId, id, action, limit, type) {
	
	$.ajax({
		
		url: '/ajaxToggleSummary.php',
		type: 'post',
		dataType: 'text',
		data: {elementId: elementId, id: id, action: action, limit: limit, type: type}
		
	}).done(function(data) {
		
		$('#' + elementId).html(data);
		
	});
	
}

function regenerateList(s, d) {
	
	searchParameters = $('#document_search').serialize() + "&s=" + s + "&d=" + d;
	
	$.ajax({
		
		url: '/ajaxDocumentList.php',
		type: 'post',
		dataType: 'text',
		data: searchParameters
		
	}).done(function(data) {
		
		$('#documents_list').html(data);
		
	});
	
}

$(document).ready( function() {
	
	documentSearch();
	regenerateList(0, '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});