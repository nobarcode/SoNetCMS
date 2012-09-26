/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @toggler plugin.
 */


CKEDITOR.plugins.add('sonet_toggler', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_toggler';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				//use the placeholder plugin to insert
				CKEDITOR.instances.documentBody.insertHtml( '<p>[[toggler text="clickable toggle text" id="id_for_this_toggler" activeDocument="List, Shortcuts, For, Active, Documents" cssClassLink="css_applied_to_clickable_link" activeCssClassLink="css_applied_to_clickable_link_when_active" cssClassContent="css_class_applied_to_content" togglerStyle="choose_a_style"]]</p><p>mixed content</p><p>[[/toggler]]</p><p>&nbsp;</p>' );
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('Toggler', {
			
			label : "Insert Toggler Code",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_toggler/toggler.png"
			
		});
	
	}

});