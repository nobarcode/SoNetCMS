/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @group content plugin.
 */


CKEDITOR.plugins.add('sonet_template_chooser', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_template_chooser';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				//open the template chooser
				openTemplateChooser();
				
			},
			
			canUndo : false
			
		});
	 
		editor.ui.addButton('SonetTemplateChooser', {
			
			label : "Templates",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_template_chooser/template_chooser.png"
			
		});
	
	}

});