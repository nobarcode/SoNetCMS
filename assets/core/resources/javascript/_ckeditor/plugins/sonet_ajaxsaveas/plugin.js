/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @ajaxSaveAs plugin.
 */


CKEDITOR.plugins.add('sonet_ajaxsaveas', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_ajaxsaveas';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				ajaxSaveAs();
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('AjaxSaveAs', {
			
			label : "Save As New",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_ajaxsaveas/saveas.png"
			
		});
	
	}

});