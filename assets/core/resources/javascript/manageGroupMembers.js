function toggleMemberStatus(username, s, usernameOrder, dateOrder, levelOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxToggleGroupMemberStatus.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, username: username}
		
	}).done(function(data){
		
		regenerateList(s, '', usernameOrder, dateOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function toggleMemberLevel(username, s, usernameOrder, dateOrder, levelOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxToggleGroupMemberLevel.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, username: username}
		
	}).done(function(data){
		
		regenerateList(s, '', usernameOrder, dateOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function deleteMember(username, s, usernameOrder, dateOrder, levelOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteGroupMember.php',
		type: 'post',
		dataType: 'text',
		data: {groupId: groupId, username: username}
		
	}).done(function(data){
		
		regenerateList(s, '', usernameOrder, dateOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function approveMultipleMembers(s, usernameOrder, dateOrder, levelOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxApproveMultipleGroupMembers.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleMembersAction').serialize()
		
	}).done(function(data){
		
		regenerateList(s, '', usernameOrder, dateOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function deleteMultipleMembers(s, usernameOrder, dateOrder, levelOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleGroupMembers.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleMembersAction').serialize()
		
	}).done(function(data){
		
		regenerateList(s, '', usernameOrder, dateOrder, levelOrder, statusOrder, orderBy, '');
		
	});
	
}

function regenerateList(s, d, usernameOrder, dateOrder, levelOrder, statusOrder, orderBy, change) {
	
	parameters = "groupId=" + groupId + "&s=" + s + "&d=" + d + "&usernameOrder=" + usernameOrder + "&dateOrder=" + dateOrder +"&levelOrder=" + levelOrder + "&statusOrder=" + statusOrder + "&orderBy=" + orderBy + "&change=" + change;
	
	$.ajax({
		
		url: '/ajaxManageGroupMembers.php',
		type: 'post',
		dataType: 'text',
		data: parameters
		
	}).done(function (data) {
		
		$('#member_list').html(data);
		
	});
	
}

$(document).ready(function() {
	
	regenerateList('', '', 'asc', 'asc', 'desc', 'desc', 'username', '');
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});