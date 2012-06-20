function objectSortableOn() {
		
	$('#subjects_list').sortable({
		
		update: function() {
			
			var values = $('#subjects_list').sortable('serialize');
			values += '&category=' + category + '&subcategory=' + subcategory;
			
			$.ajax({
				
				url: '/ajaxChangeSubjectOrder.php',
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

function initEditSubject(category, subcategory, subject, subjectId) {
	
	//cancel editor (if necessary)
	if ($('#edit_in_place').length > 0) {
		
		cancelEditSubject();
		
	}
	
	//hide the current static field
	$('#' + subjectId).hide();

	//create the editable text and ok & cancel buttons
	$('<span id="edit_in_place"><input style="width:450px;" type="text" id="subject_edit" value="' + $('#' + subjectId).html().replace(/"/g,'&quot;').replace(/\'/g,'&#039;') + '"> <input type="button" id="editor_ok" value="ok"> <input type="button" id="editor_cancel" value="cancel"></span>').insertAfter('#' + subjectId);

	//assign the category being edited to a global variable
	lastCategory = category;
	lastSubcategory = subcategory;
	lastSubject = subject;
	lastSubjectId = subjectId;

	//create observers for ok and cancel buttons
	$('#editor_ok').bind('click', function() {editSubject();});
	$('#editor_cancel').bind('click', function() {cancelEditSubject();});	

}

function editSubject() {
	
	$.ajax({
		
		url: '/ajaxUpdateSubject.php',
		type: 'post',
		dataType: 'script',
		data: {category: lastCategory, subcategory: lastSubcategory, subject: lastSubject, value: $('#subject_edit').val()}
		
	});
	
}

function cancelEditSubject() {
	
	//remove all observers
	$('#editor_ok').unbind();
	$('#editor_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_in_place').remove();

	//show the original static category name
	$('#' + lastSubjectId).show();

}

function deleteSubject(category, subcategory, subject) {
	
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
		
		url: '/ajaxDeleteSubject.php',
		type: 'post',
		dataType: 'text',
		data: {category: category, subcategory: subcategory, subject: subject}
		
	}).done(function() {
		
		regenerateList();
		
	});
	
	//set the lock
	editSubjectOptionsViewLock = 0;

}

function initEditSubjectOptions(subject) {
	
	//handle existing editor (if necessary)
	if ($('#edit_subject_options_in_place').length > 0 && editSubjectOptionsViewLock == 1) {
		
		cancelEditSubjectOptions();
		
		//if lastSubjectOptions (last viewed) is being clicked again, just remove the list (above) and exit (return, below)
		if (subject == lastSubjectOptions) {
			
			return;
			
		}
		
	}
	
	//only create a new viewer if there currently isn't one open
	if ($('#edit_subject_options_in_place').length == 0) {
		
		//keep track of last subject being viewed
		lastSubjectOptions = subject;
		
		//create the editable fields
		$('<div id="edit_subject_options_in_place" style="display:none;"></div>').insertAfter('#subject_' + subject);
		
		$.ajax({
			
			url: '/ajaxShowSubjectOptions.php',
			type: 'post',
			dataType: 'text',
			data: {subject: subject}
			
		}).done(function(data) {
			
			$('#edit_subject_options_in_place').html(data);
			
			//set the lock
			editSubjectOptionsViewLock = 1;
			
			$('#edit_subject_options_in_place').fadeIn(500);
		
			//watches the submit button
			$('#edit_subject_options_form').bind('submit', function(e) {

				//e.stop prevents the submit action from executing
				e.preventDefault();
				
				$.ajax({
					
					url: this.action,
					type: 'post',
					dataType: 'script',
					data: $(this).serialize()
					
				});
				
			});
			
			$('#edit_subject_options_cancel').bind('click', function() {cancelEditSubjectOptions();});
			
		});
		
	}
	
}

function cancelEditSubjectOptions() {
	
	//remove all observers for edit_user and cancel
	$('#edit_subject_options_form').unbind();
	$('#edit_subject_options_cancel').unbind();
	
	//remove the edit-in-place dom object
	$('#edit_subject_options_in_place').remove();
	
	//set the lock
	editSubjectOptionsViewLock = 0;
	
}

function showAddSubject() {
	
	if (!$('#add_subject_container').is(":visible")) {
		
		$('#add_subject_container').fadeIn(500);
		
	} else {
		
		$('#add_subject_container').hide();
		
	}
	
}

function addSubject() {
	
	//wathes the submit button
	$('#add_subject').bind('submit', function(e) {
		
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
		
		 cancelEditSubject();
		
	}
	
	if ($('#edit_subject_options_in_place').length > 0) {
		
		cancelEditSubjectOptions();
		
	}
	
	$.ajax({
		
		url: '/ajaxSubjectEditor.php',
		type: 'post',
		dataType: 'text',
		data: {category:category, subcategory:subcategory}
		
	}).done(function(data) {
		
		$('#subjects_list').html(data);
		objectSortableOn();
		
	});
	
}

$(document).ready(function() {
	
	regenerateList();
	addSubject();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});