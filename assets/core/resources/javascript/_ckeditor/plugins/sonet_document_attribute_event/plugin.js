/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @document attribute plugin.
 */


CKEDITOR.plugins.add('sonet_document_attribute_event', {
	
	init: function(editor) {
		
		var pluginName = 'sonet_document_attribute_event';
		
		editor.addCommand( pluginName, {
			
			exec : function( editor ) {
				
				//use the placeholder plugin to insert
				CKEDITOR.currentInstance.insertText( '[[attribute type="usernameCreated/usernameUpdated/documentType/category/subcategory/subject/dateCreated/datePublished/dateUpdated/startDate/expireDate/title"]]' );
				
			},
			
			canUndo : true
			
		});
	 
		editor.ui.addButton('DocumentAttributeEvent', {
			
			label : "Insert Document Attribute",
			command : pluginName,
			icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_document_attribute_event/document_attribute.png"
			
		});
	
	}

});