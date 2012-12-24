/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @document component plugin.
 */


CKEDITOR.plugins.add('sonet_component', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_component';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				//use insertHtml to insert
				CKEDITOR.instances.documentBody.insertHtml( '<p>[[component id/file="component-shortcut or filename"]]</p><p>&nbsp;</p>' );
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('Component', {
			
			label : "Insert Component Code",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_component/component.png"
			
		});
	
	}

});