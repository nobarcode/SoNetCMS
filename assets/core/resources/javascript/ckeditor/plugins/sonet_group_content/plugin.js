/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @group content plugin.
 */


CKEDITOR.plugins.add('sonet_group_content', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_group_content';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				//use insertHtml to insert
				CKEDITOR.instances.documentBody.insertHtml( '<p>[[group_content groups="list, groups, that, should, have, access, to, this, content"]]</p><p>mixed content</p><p>[[/group_content]]</p><p>&nbsp;</p>' );
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('GroupContent', {
			
			label : "Insert Group Content Code",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_group_content/group_content.png"
			
		});
	
	}

});