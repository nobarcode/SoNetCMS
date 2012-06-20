function togglePublishState(id) {
	
	$.ajax({
		
		url: '/ajaxToggleGroupEventPublishState.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, id: id, showImage: 'yes'}
		
	}).done(function(data) {
		
		$('#publish_state_' + id).html(data);
		
	});
	
}

function deleteEvent(id, s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteGroupEvent.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, id: id}
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function toggleMultipleEventsStatus(s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxToggleMultipleGroupEventsStatus.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleEventsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function deleteMultipleEvents(s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleGroupEvents.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleEventsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function regenerateList(s, d, dateOrder, titleOrder, statusOrder, orderBy, change) {
	
	var values = $('#event_list_options').serialize();
	values += "&groupId=" + groupId + "&s=" + s + "&d=" + d + "&dateOrder=" + dateOrder + "&titleOrder=" + titleOrder + "&statusOrder=" + statusOrder + "&orderBy=" + orderBy + "&change=" + change;
	
	$.ajax({
		
		url: '/ajaxGroupEventEditor.php',
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