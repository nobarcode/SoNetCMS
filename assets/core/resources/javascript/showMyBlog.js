function togglePublishState(id) {
	
	$.ajax({
		
		url: '/ajaxToggleBlogPublishState.php',
		type: 'post',
		dataType: 'text',
		data: {id: id, showImage: 'yes'}
		
	}).done(function(data) {
		
		$('#publish_state_' + id).html(data);
		
	});
	
}

function deleteBlog(id, s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteBlog.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function toggleMultipleBlogStatus(s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxToggleMultipleBlogsStatus.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleBlogsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function deleteMultipleBlogs(s, dateOrder, titleOrder, statusOrder, orderBy) {
	
	$.ajax({
		
		url: '/ajaxDeleteMultipleBlogs.php',
		type: 'post',
		dataType: 'text',
		data: $('#multipleBlogsAction').serialize()
		
	}).done(function() {
		
		regenerateList(s, '', dateOrder, titleOrder, statusOrder, orderBy, '');
		
	});
	
}

function regenerateList(s, d, dateOrder, titleOrder, statusOrder, orderBy, change) {
	
	parameters = $('#blog_list_options').serialize() + "&s=" + s + "&d=" + d + "&dateOrder=" + dateOrder + "&titleOrder=" + titleOrder + "&statusOrder=" + statusOrder + "&orderBy=" + orderBy + "&change=" + change;
	
	$.ajax({
		
		url: '/ajaxShowMyBlog.php',
		type: 'post',
		dataType: 'text',
		data: parameters
		
	}).done(function(data) {
		
		$('#blog_list').html(data);
		
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