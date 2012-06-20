function groupSearch() {
	
	//wathes the submit button
	$('#group_search').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing 
		e.preventDefault();
		
		$.ajax({
			
			url: '/ajaxGroupSearch.php',
			type: 'post',
			dataType: 'text',
			data: $('#group_search').serialize()
			
		}).done(function(data) {
			
			$('#group_list').html(data);
			
		});
		
	});
	
}

function regenerateList(s, d) {
	
	searchParameters = $('#group_search').serialize() + "&s=" + s + "&d=" + d;
	
	$.ajax({
		
		url: '/ajaxGroupSearch.php',
		type: 'post',
		dataType: 'text',
		data: searchParameters
		
	}).done(function(data) {
		
		$('#group_list').html(data);
		
	});
	
}

$(document).ready(function() {
	
	groupSearch();
	regenerateList('', '');
	
	//watches the min_date_est_selector element
	$('#min_date_est_selector').bind('click', function(e) {
		
		showSelectorCalendar(e, '#minDateEstMonth', '#minDateEstDay', '#minDateEstYear');
		
	});
	
	//watches the max_date_est_selector element
	$('#max_date_est_selector').bind('click', function(e) {
		
		showSelectorCalendar(e, '#maxDateEstMonth', '#maxDateEstDay', '#maxDateEstYear');
		
	});
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});