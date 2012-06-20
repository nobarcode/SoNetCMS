function showMessage(id) {
	
	//handle existing editor (if necessary)
	if ($('#message_in_place').length > 0 && messageViewLock == 1) {
		
		$('#message_in_place').remove();
		
		//set the lock
		messageViewLock = 0;
		
		//if lastId (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
		if (id == lastId) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#message_in_place').length == 0) {
		
		//keep track of last message being viewed
		lastId = id;
	
		//create the body container
		$('<div id="message_in_place" style="float:left; display:none;"></div>').insertAfter('#message_' + id);
		
		$.ajax({
			
			url: '/ajaxShowMessageBody.php',
			type: 'post',
			dataType: 'text',
			data: {id: id}
			
		}).done(function(data){
			
			$('#message_in_place').html(data);
			
			//set the lock
			messageViewLock = 1;
			
			$('#message_in_place').fadeIn(500);
			
			if($('#message_' + id).hasClass('message_container_unread')) {
			
				$('#message_' + id).attr('class','message_container_read');
				$('#status_' + id).html('Read');
				
				$.ajax({
					
					url: '/ajaxShowMyMessageCount.php',
					type: 'post',
					dataType: 'text'
					
				}).done(function(data) {
					
					$('#toolbar_unread_message_count').html(data);
					
				});
				
			}
			
		});
		
	}
	
}

function deleteMessage(id, s, fromUserOrder, subjectOrder, dateSentOrder, statusOrder, orderBy) {
	
	//remove editor (if necessary)
	if ($('#message_in_place').length > 0) {
		
		//remove the edit-in-place dom object
		$('#message_in_place').remove();
		
	}
	
	$.ajax({
		
		url: '/ajaxDeleteMessage.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		regenerateList(s, '', fromUserOrder, subjectOrder, dateSentOrder, statusOrder, orderBy, '');
		
		$.ajax({
			
			url: '/ajaxShowMyMessageCount.php',
			type: 'post',
			dataType: 'text'
			
		}).done(function(data) {
			
			$('#toolbar_unread_message_count').html(data);
			
		});
		
	});
	
	//set the lock
	messageViewLock = 0;
	
}

function deleteMultipleMessages(s, fromUserOrder, subjectOrder, dateSentOrder, statusOrder, orderBy) {
	
	//remove editor (if necessary)
	if ($('#message_in_place').length > 0) {
		
		//remove the edit-in-place dom object
		$('#message_in_place').remove();
		
	}
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleMessages.php',
		type: 'post',
		dataType: 'text',
		data: $('#deleteMultipleMessages').serialize()
		
	}).done(function(data) {
		
		regenerateList(s, '', fromUserOrder, subjectOrder, dateSentOrder, statusOrder, orderBy, '');
		
		$.ajax({
			
			url: '/ajaxShowMyMessageCount.php',
			type: 'post',
			dataType: 'text'
			
		}).done(function(data) {
			
			$('#toolbar_unread_message_count').html(data);
			
		});
		
	});
	
	//set the lock
	messageViewLock = 0;
	
}

function regenerateList(s, d, fromUserOrder, subjectOrder, dateSentOrder, statusOrder, orderBy, change) {
	
	$.ajax({
		
		url: '/ajaxShowMyMessages.php',
		type: 'post',
		dataType: 'text',
		data: {s: s, d: d, fromUserOrder: fromUserOrder, subjectOrder: subjectOrder, dateSentOrder: dateSentOrder, statusOrder:statusOrder, orderBy: orderBy, change: change}
		
	}).done(function(data) {
		
		$('#message_list').html(data);
		
	});
	
}

$(document).ready(function() {
	
	regenerateList('', '', 'desc', 'desc', 'desc', 'desc', 'dateSent', '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});