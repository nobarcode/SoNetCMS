/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @authenticated content plugin.
 */


CKEDITOR.plugins.add('sonet_auth_content', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_auth_content';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				//use insertHtml to insert
				CKEDITOR.instances.documentBody.insertHtml( '<p>[[authenticated_content]]</p><p>mixed content</p><p>[[/authenticated_content]]</p><p>&nbsp;</p>' );
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('AuthContent', {
			
			label : "Insert Authenticated Content Code",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_auth_content/auth_content.png"
			
		});
	
	}

});