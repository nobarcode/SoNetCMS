/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @ajaxSave plugin.
 */


CKEDITOR.plugins.add('sonet_ajaxsave', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_ajaxsave';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				ajaxSave();
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('AjaxSave', {
			
			label : "Save",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_ajaxsave/save.png"
			
		});
	
	}

});