/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @auto save plugin.
 */


CKEDITOR.plugins.add('sonet_autosave', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_autosave';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				if (confirm('Are you sure you want to restore the last auto-saved content to the current editor?')) {
					
					autoSaveLoad();
					
				}
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('AutoSave', {
			
			label : "Restore Auto-saved Content",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_autosave/autosave.png"
			
		});
	
	}

});