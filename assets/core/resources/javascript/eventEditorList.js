function togglePublishState(id) {
	
	$.ajax({
		
		url: '/ajaxToggleEventPublishState.php',
		type: 'post',
		dataType: 'text',
		data: {id: id, showImage: 'yes'}
		
	}).done(function(data) {
		
		$('#publish_state_' + id).html(data);
		
	});
	
}

function deleteEvent(id, s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteEvent.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function toggleMultipleEventsStatus(s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxToggleMultipleEventsStatus.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleEventsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function deleteMultipleEvents(s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleEvents.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleEventsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function regenerateList(s, d, dateOrder, titleOrder, statusOrder, orderBy, change) {
	
	var values = $('#event_list_options').serialize();
	values += "&s=" + s + "&d=" + d + "&dateOrder=" + dateOrder + "&titleOrder=" + titleOrder + "&statusOrder=" + statusOrder + "&orderBy=" + orderBy + "&change=" + change;
	
	$.ajax({
		
		url: '/ajaxEventEditor.php',
		type: 'post',
		dataType: 'text',
		data: values
		
	}).done(function(data) {
		
		$('#event_list').html(data);
		
	});
	
}

$(document).ready(function() {
	
	regenerateList('', '', 'desc', 'desc', 'desc', 'date', '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});