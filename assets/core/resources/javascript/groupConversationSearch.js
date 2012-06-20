function search() {
	
	//wathes the submit button
	$('#search').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing
		e.preventDefault();
		
		$.ajax({
			
			url: '/ajaxGroupConversationSearch.php',
			type: 'post',
			dataType: 'text',
			data: $('#search').serialize()
			
		}).done(function(data) {
			
			$('#post_list').html(data)
			
		});
		
	});
	
}

function regenerateList(s, d) {
	
	searchParameters = $('#search').serialize() + "&s=" + s + "&d=" + d;
	
	$.ajax({
		
		url: '/ajaxGroupConversationSearch.php',
		type: 'post',
		dataType: 'text',
		data: searchParameters
		
	}).done(function(data) {
		
		$('#post_list').html(data)
		
	});
	
}

$(document).ready(function() {
	
	search();
	regenerateList(0, '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});