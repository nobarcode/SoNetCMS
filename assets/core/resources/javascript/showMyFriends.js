function objectSortableOn() {
				   
	$('#friends_list').sortable({
		
		update: function() {
				
			var values = $('#friends_list').sortable('serialize');
			values += '&s=' + last_s;
			
			$.ajax({
				url: '/ajaxChangeFriendOrder.php',
				type: 'post',
				dataType: "script",
				data: values
			
			});
			
		},
		placeholder: 'sortable_placeholder',
		handle: '.image',
		revert: true,
		scroll: true,
		tolerance: 'pointer',
		create:function(){
			
			//set the size of the container to prevent scrollbar jumping while sorting
			$(this).height($(this).height());
			
		},
		start: function (e, ui) {
			
			if (ui.item.hasClass('column_separator')) {
									   
				ui.placeholder.addClass('column_separator');
									   
			}
			
		},
		change: function() {
			
			addThumbClasses();
			
		},
		stop: function() {
			
			addThumbClasses();
			
		}
		
	});
	
}

function addThumbClasses() {
	
	$('#friends_list').children().not('.ui-sortable-helper').each(function(index, el){
		
		if ((index + 1) % 5 != 0) {
			
			$(el).addClass('column_separator');
			
		} else {
			
			$(el).removeClass('column_separator');
			
		}
		
	});
	
}

function deleteFriend(friend) {
	
	$.ajax({
		
		url: '/ajaxDeleteFriend.php',
		type: 'post',
		dataType: "script",
		data: {friend: friend}
		
	}).done(function() {
		
		regenerateList(last_s, '');
		
	});
	
}

function regenerateList(s, d) {
	
	$.ajax({
		
		url: '/ajaxShowMyFriends.php',
		type: 'post',
		dataType: "html",
		data: {s: s, d: d}
		
	}).done(function(data) {
		
		$('#friends_list_container').html(data);
		objectSortableOn();
		
	});
	
}

$(document).ready(function() {
	
	regenerateList();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});