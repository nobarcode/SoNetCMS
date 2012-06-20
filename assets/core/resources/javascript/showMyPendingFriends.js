function approveFriend(owner) {
	
	$.ajax({
		
		url: '/ajaxApproveFriend.php',
		type: 'post',
		dataType: "script",
		data: {owner: owner}
		
	}).done(function() {
		
		regenerateList(last_s, '');
		
	});
	
}

function declineFriend(owner) {
	
	$.ajax({
		
		url: '/ajaxDeclinePendingFriend.php',
		type: 'post',
		dataType: "script",
		data: {owner: owner}
		
	}).done(function() {
		
		regenerateList(last_s, '');
		
	});
	
}

function regenerateList(s, d) {
	
	$.ajax({
		
		url: '/ajaxShowMyPendingFriends.php',
		type: 'post',
		dataType: "text",
		data: {s: s, d: d}
		
	}).done(function(data) {
		
		$('#friends_list_container').html(data);
		
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