function deleteGroup(groupId, s, nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxShowMyGroupsDeleteGroup.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId}
		
	}).done(function() {
		
		regenerateList(s, '', nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function deleteMultipleGroups(s, nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxShowMyGroupsDeleteMultipleGroups.php',
		type: 'post',
		dataType: 'text',
		data: $('#deleteMultipleGroups').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function leaveGroup(groupId, s, nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy) {
	
	parameters = "groupId=" + groupId + "&s=" + s + "&nameOrder=" + nameOrder + "&membersOrder=" + membersOrder + "&typeOrder=" + typeOrder + "&levelOrder=" + levelOrder + "&statusOrder=" + statusOrder + "&orderBy=" + orderBy;
	
	$.ajax({
		
		url: '/ajaxShowMyGroupsLeaveGroup.php',
		type: 'post',
		dataType: 'script',
		data: parameters
		
	}).done(function() {
		
		regenerateList(s, '', nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function transferOwnership(groupId, s, nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxTransferGroupOwnership.php',
		type: 'post',
		dataType: 'script',
		data: {groupId: groupId}
		
	}).done(function() {
		
		regenerateList(s, '', nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function regenerateList(s, d, nameOrder, membersOrder, typeOrder, levelOrder, statusOrder, orderBy, change) {
	
	parameters = "s=" + s + "&d=" + d + "&nameOrder=" + nameOrder + "&membersOrder=" + membersOrder + "&typeOrder=" + typeOrder + "&levelOrder=" + levelOrder + "&statusOrder=" + statusOrder + "&orderBy=" + orderBy + "&change=" + change;
	
	$.ajax({
		
		url: '/ajaxShowMyGroups.php',
		type: 'post',
		dataType: 'text',
		data: parameters
		
	}).done(function(data) {
		
		$('#group_list').html(data);
		
	});
	
}

$(document).ready(function() {
	
	regenerateList('', '', 'asc', 'asc', 'asc', 'asc', 'desc', 'level', '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});