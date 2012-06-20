function openFileManager(callbackFunction, elementName) {

	openServerBrowser(
	
		'/assets/core/resources/filemanager/index.html?callbackFunction=' + callbackFunction + '&elementName=' + elementName,
		screen.width * 0.7,
		screen.height * 0.7
		
	);
	
}

function openServerBrowser(url, width, height) {

	var iLeft = (screen.width - width) / 2;
	var iTop = (screen.height - height) / 2;
	var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes';
	sOptions += ',width=' + width;
	sOptions += ',height=' + height;
	sOptions += ',left=' + iLeft;
	sOptions += ',top=' + iTop;
	var oWindow = window.open(url, 'BrowseWindow', sOptions);
}

function selectPath(elementName, path) {
	
	$('#' + elementName).val(path);
	
}

function ajaxSave() {
	
	//wathes the submit button
	$('#profileEditorForm').bind('submit', function(e) {
		
		//e.stop prevents the submit action from executing 
		e.preventDefault();
		
		$.ajax({
			
			url: '/ajaxUpdateProfile.php',
			type: 'post',
			dataType: 'script',
			data: $('#profileEditorForm').serialize()
			
		}).done(function() {
			
			//hide the date selector
			$('#calendar_container').hide();
			
		});
		
	});
	
	
	
}

$(document).ready(function() {
	
	ajaxSave();
	
	//watches the date_selector element
	$('#date_selector').bind('click', function(e) {
		
		showSelectorCalendar(e, '#birthMonth', '#birthDay', '#birthYear');
		
	});
	
	$('#tabs').tabs();
	
});

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});