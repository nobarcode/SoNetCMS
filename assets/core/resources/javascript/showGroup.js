function joinGroup() {
	
	$.ajax({
		
		url: '/ajaxJoinGroup.php',
		type: 'post',
		dataType: 'script',
		data: {groupId: groupId}
		
	});
	
}

function leaveGroup() {
	
	$.ajax({
		
		url: '/ajaxLeaveGroup.php',
		type: 'post',
		dataType: 'script',
		data: {groupId: groupId}
		
	});
	
}

function transferOwnership() {
	
	$.ajax({
		
		url: '/ajaxTransferGroupOwnership.php',
		type: 'post',
		dataType: 'script',
		data: {groupId: groupId}
		
	}).done(function() {
		
		$.ajax({
			
			url: '/ajaxShowGroupOwner.php',
			type: 'post',
			dataType: 'text',
			data: {groupId: groupId}
			
		}).done(function(data) {
			
			$('#owner_container').html(data);
			regenerateMemberList('', '');
			
		});
		
	});
	
}

function regenerateMemberList(s, d) {
	
	$.ajax({
		
		url: '/ajaxShowGroupMembers.php',
		type: 'post',
		dataType: 'html',
		data: {groupId: groupId, s: s, d: d}
		
	}).done(function(data) {
		
		$('#members_list_container').html(data);
		
	});
	
}

function regenerateMemberCount() {
	
	$.ajax({
		
		url: '/ajaxShowGroupMemberCount.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId}
		
	}).done(function(data) {
		
		$('#total_members_container').html(data);
		
	});
	
}

function regenerateEventList(s, d) {
	
	$.ajax({
		
		url: '/ajaxShowUpcomingGroupEvents.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, s: s, d: d}
		
	}).done(function(data) {
		
		$('#upcoming_events_container').html(data);
		
	});
	
}

$(document).ready(function() {
	
	regenerateMemberList('', '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});