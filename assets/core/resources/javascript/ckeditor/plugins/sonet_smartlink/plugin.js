/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @smart link plugin.
 */


CKEDITOR.plugins.add('sonet_smartlink', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_smartlink';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				//use insertHtml to insert
				CKEDITOR.instances.documentBody.insertHtml( '<p>[[smartlink activeDocument="List, Shortcuts, For, Active, Documents" cssClass="css_class_not_active" activeCssClass="css_class_active" url="/documents/open/ShortcutToDocument" linkOnActive="false"]]link text[[/smartlink]]</p><p>&nbsp;</p>' );
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('Smartlink', {
			
			label : "Insert Smartlink Code",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_smartlink/smartlink.png"
			
		});
	
	}

});