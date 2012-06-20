function profileSearch() {
	
	//wathes the submit button
	$('#profile_search').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing 
		e.preventDefault();
		
		$.ajax({
			
			url: '/ajaxProfileSearch.php',
			type: 'post',
			dataType: 'text',
			data: $('#profile_search').serialize()
			
		}).done(function(data) {
			
			$('#users_list').html(data);
			
		});
		
	});
	
}

function regenerateList(s, d) {
	
	searchParameters = $('#profile_search').serialize() + "&s=" + s + "&d=" + d;
	
	$.ajax({
		
		url: '/ajaxProfileSearch.php',
		type: 'post',
		dataType: 'text',
		data: searchParameters
		
	}).done(function(data) {
		
		$('#users_list').html(data);
		
	});
	
}

$(document).ready(function() {
	
	profileSearch();
	regenerateList('', '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});