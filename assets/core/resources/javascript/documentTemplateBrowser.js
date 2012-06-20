function selectTemplate(id) {
	
	$.ajax({
		
		url: '/ajaxLoadDocumentTemplate.php',
		type: 'post',
		dataType: 'script',
		data: {id: id}
		
	}).done(function(data) {
		
		self.close();
		
	});
	
}

function regenerateTemplateList() {
	
	$.ajax({
		
		url: '/ajaxShowDocumentTemplates.php',
		type: 'post',
		dataType: 'text'
		
	}).done(function(data) {
		
		$('#template_list').html(data);
		
	});
	
}

$(document).ready(function() {
	
	regenerateTemplateList();
	
});