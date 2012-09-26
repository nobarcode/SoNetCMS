/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @rich content plugin.
 */


CKEDITOR.plugins.add('sonet_rc_component', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_rc_component';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				//use the placeholder plugin to insert
				CKEDITOR.plugins.placeholder.createPlaceholder( editor, '', '[[rc_component type="document/blog/event/announcement/conversation/member" parameter="supply" parameter="the" parameter="required" parameter="options" parameter="here"]]' );
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('RCComponent', {
			
			label : "Insert Rich Content Component Code",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_rc_component/rc_component.png"
			
		});
	
	}

});