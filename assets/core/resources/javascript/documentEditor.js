function clearEditTracking() {
	
	$.post('/ajaxDeleteEditTracking.php', {documentType: 'document', id: id}, function(data) {
		
		$('#edit_tracking_container').hide();
		
	});
	
}

function updateSubcategories() {
	
	$.post('/ajaxSubcategorySelectListUpdater.php', {category: $('#categories').val()}, function(data) {
		
		$('#subcategories').html(data);
		
	}).complete(function() {
		
		updateSubjects();
		
	});

	
}

function updateSubjects() {
	
	$.post('/ajaxSubjectSelectListUpdater.php', {category: $('#categories').val(), subcategory: $('#subcategories').val()}, function(data) {
		
		$('#subjects').html(data);
		
	});
	
}

function openFileManager(callbackFunction, elementName) {

	openServerBrowser(
	
		'/assets/core/resources/filemanager/index.html?callbackFunction=' + callbackFunction + '&elementName=' + elementName,
		screen.width * 0.7,
		screen.height * 0.7
		
	);
	
}

function openDocumentManager(callbackFunction, elementName) {

	openServerBrowser(
	
		'/selectDocument.php?callbackFunction=' + callbackFunction + '&elementName=' + elementName,
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

function selectPathCss(elementName, path) {
	
	$('#' + elementName).val(path);
	
	//remove the editor and reinitialize it with the new css path
	CKEDITOR.instances.documentBody.destroy();
	initializeEditor(path);
	
}

function clearCss() {
	
	$('#cssPath').val('');
	CKEDITOR.instances.documentBody.destroy();
	initializeEditor('');
	
}

function openTemplateChooser() {
	
	var width = screen.width * 0.7;
	var height = screen.height * 0.7;
	var iLeft = (screen.width - width) / 2;
	var iTop = (screen.height - height) / 2;
	var sOptions = "toolbar=no,status=no,scrollbars=yes,resizable=yes,dependent=yes";
	sOptions += ",width=" + width;
	sOptions += ",height=" + height;
	sOptions += ",left=" + iLeft;
	sOptions += ",top=" + iTop;
	var oWindow = window.open("/documentTemplateBrowser.php", "BrowseWindow", sOptions);
	
}

function selectTemplate(cssPath, body) {
	
	//update the css path
	$('#cssPath').val(cssPath);
	
	//check if the editor is maximized; if it is, minimize it
	if (CKEDITOR.instances.documentBody.getCommand('maximize').state == CKEDITOR.TRISTATE_ON) {
		
		CKEDITOR.instances.documentBody.execCommand('maximize');
		
	}
	
	CKEDITOR.instances.documentBody.destroy(true);
	
	$('#documentBody').html(body);
	
	initializeEditor(cssPath);

}

function ajaxSave() {
	
	var values = $('#newDocumentForm').serialize();
	values += '&documentBody=' + encodeURIComponent(CKEDITOR.instances.documentBody.getData());
	
	$.ajax({
		
		url: '/ajaxAddDocument.php',
		type: 'post',
		dataType: 'script',
		data: values
		
	});
	
}

function ajaxSaveAs() {
	
	if (confirm('Are you sure you want to save this as a new document?')) {
		
		//theHash = new Hash();
		//theHash.update($('#newDocumentForm').serialize(true));
		//theHash.update({documentBody: CKEDITOR.instances.documentBody.getData()});
		
		var values = $('#newDocumentForm').serialize();
		values += '&documentBody=' + encodeURIComponent(CKEDITOR.instances.documentBody.getData());
		
		$.ajax({
			
			url: '/ajaxAddDocumentAsNew.php',
			type: 'post',
			dataType: 'script',
			data: values
			
		});
		
	}
 	
}

function togglePublishState(id) {
	
	$.ajax({
		
		url: '/ajaxTogglePublishState.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		if (data == 'Published') {
			
			$('#publish_state_' + id).html('Unpublish');
			
		} else {
			
			$('#publish_state_' + id).html('Publish');
			
		}
		
	});
	
}

function toggleFeatured(id) {
	
	$.ajax({
		
		url: '/ajaxToggleFeaturedDocument.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		$('#featured').html(data);
		
	});
	
}

function toggleFocused(id) {
	
	$.ajax({
		
		url: '/ajaxToggleDocumentFocus.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		$('#focused').html(data);
		
	});
	
}

function displayVersionOptions() {
	
	if (!$('#version_choices').is(':visible')) {
		
		$('#version_choices').fadeIn(500);
		$('#versions_navigation').html('Hide Versions');
		
		
	} else {
		
		$('#version_choices').hide();
		$('#versions_navigation').html('Show Versions');
				
	}
	
}

function regenerateVersionList(id, s, d) {
	
	$.ajax({
		
		url: '/ajaxShowVersionList.php',
		type: 'post',
		dataType: 'text',
		data: {id: id, documentType: 'document', s: s, d: d}
		
	}).done(function(data) {
		
		$('#version_choices').html(data);
		
	});
	
}

function changeVersion(version) {
	
	var confirmation;
	
	if (CKEDITOR.instances.documentBody.checkDirty()) {
		
		confirmation = confirm('Are you sure you want to cancel this editing session?\n\nThe changes you made will be lost if you continue.\n\nClick OK to discard your changes, or click Cancel to continue editing and save your changes.');
		
	}
	
	if (confirmation || !CKEDITOR.instances.documentBody.checkDirty()) {
		
		$.ajax({
			
			url: '/ajaxLoadDocumentVersioning.php',
			type: 'post',
			dataType: 'script',
			data: {id: id, documentType: 'document', version: version}
			
		});
		
	}
	
}

function deleteVersion(version) {
	
	$.ajax({
		
		url: '/ajaxDeleteDocumentVersion.php',
		type: 'post',
		dataType: 'script',
		data: {id: id, documentType: 'document', version: version, currentVersion: currentVersion}
		
	});
	
}

function deleteVersionAll() {
	
	$.ajax({
		
		url: '/ajaxDeleteDocumentVersionAll.php',
		type: 'post',
		dataType: 'script',
		data: {id: id, documentType: 'document'}
		
	});
	
}

function displayEditor() {
	
	$.ajax({
		
		url: '/ajaxLoadDocumentEditorContent.php',
		type: 'post',
		dataType: 'text',
		data: {id: id}
		
	}).done(function(data) {
		
		initializeEditor(cssFile);
		
		if (id != '') {
			
			CKEDITOR.instances.documentBody.setData(data);
			
		}
		
		setInterval(autoSave, 30000);
		
		$('#loading_editor_message').hide();
		$('#editor_container').show();
		
	});
	
}

function autoSave() {
	
	if (CKEDITOR.instances.documentBody.checkDirty()) {
		
		$.ajax({
			
			url: '/ajaxAutoSave.php',
			type: 'post',
			dataType: 'text',
			data: {content: CKEDITOR.instances.documentBody.getData()}
			
		});

	}
	
}

function autoSaveLoad() {
	
	$.ajax({
		
		url: '/ajaxAutoSaveLoad.php',
		type: 'post',
		dataType: 'script'
		
	});
	
}

$(document).ready(function() {
	
	//watches the category and subcategory fields
	$('#categories').on('change', updateSubcategories);
	$('#subcategories').on('change', updateSubjects);
	
	//watches the submit action
	$('#newDocumentForm').bind('submit', function(e) {
		
		//prevents the submit action from executing 
		e.preventDefault();
		
	});
	
	//generates version list
	regenerateVersionList(id, '', '');
	
	$('#shortcut').bind('blur', function(event) {
		
		shortcut = $('#shortcut').val();
		
		//replace all white space characters with a dash
		shortcut = shortcut.replace(/ /gi, '-');
		
		//strip all non alphanumeric except dashes
		shortcut = shortcut.replace(/[^A-Za-z0-9\-]/gi, "");
		
		$('#shortcut').val(shortcut);
		
	});
	
	//load the RTE
	displayEditor();
	
});

window.onbeforeunload = function() {
	
	if (id != '') {
		
		$.ajax({
			
			async: false,
			url: '/ajaxClearMyEditTracking.php',
			type: 'post',
			dataType: 'script',
			data: {documentType: 'document', id: id}
			
		});
		
	}
	
}

$.ajaxSetup({
	
	beforeSend: function() {$('#loading').show()},
	complete: function(){$('#loading').hide()},
	success: function() {}
	
});