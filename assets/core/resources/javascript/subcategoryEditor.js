function objectSortableOn() {
		
	$('#subcategories_list').sortable({
		
		update: function() {
			
			var values = $('#subcategories_list').sortable('serialize');
			values += '&category=' + category;
			
			$.ajax({
				
				url: '/ajaxChangeSubcategoryOrder.php',
				type: 'post',
				dataType: "text",
				data: values
				
			});
			
		},
		handle: '.handle',
		revert: true,
		scroll: true
		
	});
	
}

function initEditSubcategory(category, subcategory, subcategoryId) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		cancelEditSubcategory();
		
	}
	
	//hide the current static field
	$('#' + subcategoryId).hide();
	
	//create the editable text and ok & cancel buttons
	$('<span id="edit_in_place"><input style="width:450px;" type="text" id="subcategory_edit" value="' + $('#' + subcategoryId).html().replace(/"/g,'&quot;').replace(/\'/g,'&#039;') + '"> <input type="button" id="editor_ok" value="ok"> <input type="button" id="editor_cancel" value="cancel"></span>').insertAfter('#' + subcategoryId);
	
	//assign the category being edited to a global variable
	lastCategory = category;
	lastSubcategory = subcategory;
	lastSubcategoryId = subcategoryId;
	
	//create observers for ok and cancel buttons
	$('#editor_ok').bind('click', function() {editSubcategory();});
	$('#editor_cancel').bind('click', function() {cancelEditSubcategory();});
	
}

function editSubcategory() {
	
	$.ajax({
		
		url: '/ajaxUpdateSubcategory.php',
		type: 'post',
		dataType: 'script',
		data: {category: lastCategory, subcategory: lastSubcategory, value: $('#subcategory_edit').val()}
		
	});
	
}

function cancelEditSubcategory() {
	
	//remove all observers
	$('#editor_ok').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();

	//show the original static category name
	$('#' + lastSubcategoryId).show();

}

function deleteSubcategory(category, subcategory) {
	
	//remove editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		//remove all observers for edit_user and cancel
		$('#editor_ok').unbind();
		$('#editor_cancel').unbind();

		//remove the edit-in-place dom object
		$('#edit_in_place').remove();
		
		//show the original static category name
		$(lastSubcategoryId).show();
		
	}
	
	$.ajax({
		
		url: '/ajaxDeleteSubcategory.php',
		type: 'post',
		dataType: 'text',
		data: {category: category, subcategory: subcategory}
		
	}).done(function() {
		
		regenerateList();
		
	});
	
	//set the lock
	editSubcategoryOptionsViewLock = 0;
	
}

function initEditSubcategoryOptions(subcategory) {
	
	//handle existing editor (if necessary)
	if ($('#edit_subcategory_options_in_place').length > 0 && editSubcategoryOptionsViewLock == 1) {
		
		cancelEditSubcategoryOptions();
		
		//if lastCategory (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
		if (subcategory == lastSubcategoryOptions) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_subcategory_options_in_place').length == 0) {
		
		//keep track of last category being viewed
		lastSubcategoryOptions = subcategory;
		
		//create the editable fields
		$('<div id="edit_subcategory_options_in_place" style="display:none;"></div>').insertAfter('#subcategory_' + subcategory);
		
		$.ajax({
			
			url: '/ajaxShowSubcategoryOptions.php',
			type: 'post',
			dataType: 'text',
			data: {subcategory: subcategory}
			
		}).done(function(data) {
			
			$('#edit_subcategory_options_in_place').html(data);
			
			//set the lock
			editSubcategoryOptionsViewLock = 1;
			
			$('#edit_subcategory_options_in_place').fadeIn(500);
		
			//watches the submit button
			$('#edit_subcategory_options_form').bind('submit', function(e) {

				//e.stop prevents the submit action from executing
				e.preventDefault();
				
				$.ajax({
					
					url: this.action,
					type: 'post',
					dataType: 'script',
					data: $(this).serialize()
					
				});
				
			});
		
			$('#edit_subcategory_options_cancel').bind('click', function() {cancelEditSubcategoryOptions();});
			
		});
		
	}
	
}

function cancelEditSubcategoryOptions() {
	
	//remove all observers for edit_user and cancel
	$('#edit_subcategory_options_form').unbind();
	$('#edit_subcategory_options_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_subcategory_options_in_place').remove();
	
	//set the lock
	editSubcategoryOptionsViewLock = 0;
	
}

function showAddSubcategory() {
	
	if (!$('#add_subcategory_container').is(":visible")) {
		
		$('#add_subcategory_container').fadeIn(500);
		
	} else {
		
		$('#add_subcategory_container').hide();
		
	}
	
}

function addSubcategory() {
	
	//wathes the submit button
	$('#add_subcategory').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing
		e.preventDefault();
		
		$.ajax({
			
			url: this.action,
			type: 'post',
			dataType: 'script',
			data: $(this).serialize()
			
		});
		
	});
	
}

function regenerateList() {
	
	//check if anything is being edited; if there is, cancel it
	if ($('#edit_in_place').length > 0) {
		
		 cancelEditSubcategory();
		
	}
	
	if ($('#edit_subcategory_options_in_place').length > 0) {
		
		cancelEditSubcategoryOptions();
		
	}
	
	$.ajax({
		
		url: '/ajaxSubcategoryEditor.php',
		type: 'post',
		dataType: 'text',
		data: {category:category}
		
	}).done(function(data) {
		
		$('#subcategories_list').html(data);
		objectSortableOn();
		
	});
	
}

$(document).ready(function() {
	
	regenerateList();
	addSubcategory();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});