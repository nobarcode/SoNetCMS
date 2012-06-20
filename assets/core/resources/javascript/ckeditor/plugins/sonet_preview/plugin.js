(function() {
	
	var pluginName = 'sonet_preview';
	
	var sonetPreviewCmd = {
		
		modes : { wysiwyg: 1, source: 1 },
		canUndo : false,
		exec : function(editor) {
			
			var theForm = document.getElementById('sonetPreviewForm');
			
			//if the form hasn't be created yet, create it now
			if (!theForm) {
				
				//create the form
				theForm = document.createElement('FORM');
				theForm.method = 'POST';
				theForm.name = 'sonetPreviewForm';
				theForm.id = theForm.name;
				theForm.style.display = 'none';
				
				theForm.action = editor.config.serverPreviewURL;
				
				//create the target to the "_preview" window which will be created before the form is submitted
				theForm.target = '_preview';
				document.body.appendChild(theForm);
				
				var input0 = document.createElement('INPUT');
				input0.type = 'hidden';
				input0.name = 'author';
				theForm.appendChild(input0);
				
				var input1 = document.createElement('INPUT');
				input1.type = 'hidden';
				input1.name = 'title';
				theForm.appendChild(input1);
				
				var input2 = document.createElement('INPUT');
				input2.type = 'hidden';
				input2.name = 'htmlData';
				theForm.appendChild(input2);
				
				//create the documentType if it's available
				var input3 = document.createElement('INPUT');
				input3.type = 'hidden';
				input3.name = 'documentType';
				theForm.appendChild(input3);
				
				//create the category, subcategory, and subjects, if they're available
				var input4 = document.createElement('INPUT');
				input4.type = 'hidden';
				input4.name = 'category';
				theForm.appendChild(input4);
				
				var input5 = document.createElement('INPUT');
				input5.type = 'hidden';
				input5.name = 'subcategory';
				theForm.appendChild(input5);
				
				var input6 = document.createElement('INPUT');
				input6.type = 'hidden';
				input6.name = 'subject';
				theForm.appendChild(input6);
				
				//create the rating element
				var input7 = document.createElement('INPUT');
				input7.type = 'hidden';
				input7.name = 'rating';
				theForm.appendChild(input7);
				
				//create the cssPath element if it's being used in the document
				var input8 = document.createElement('INPUT');
				input8.type = 'hidden';
				input8.name = 'cssPath';
				theForm.appendChild(input8);
				
				//create the cssPath element if it's being used in the document
				var input9 = document.createElement('INPUT');
				input9.type = 'hidden';
				input9.name = 'customHeader';
				theForm.appendChild(input9);
				
			}
			
			//set the author if it's available
			if (document.getElementById('author') != undefined) {
				
				theForm.elements["author"].value = document.getElementById('author').value;
				
			}
			
			//set the title
			theForm.elements["title"].value = document.getElementById('title').value;
			
			//set the content
			theForm.elements["htmlData"].value = editor.getData();
			
			//set the documentType if it's available
			if (document.getElementById('documentTypes') != undefined) {
				
				theForm.elements["documentType"].value = document.getElementById('documentTypes').value;
				
			}
			
			//set the category, subcategory, and subjects, if they're available
			theForm.elements["category"].value = document.getElementById('categories').value;
			
			theForm.elements["subcategory"].value = document.getElementById('subcategories').value;
			
			theForm.elements["subject"].value = document.getElementById('subjects').value;
			
			//set the rating if it's available
			if (document.getElementById('rating') != undefined) {
				
				theForm.elements["rating"].value = document.getElementById('rating').value;
				
			}
			
			//set the cssPath if it's available
			if (document.getElementById('cssPath') != undefined) {
				
				theForm.elements["cssPath"].value = document.getElementById('cssPath').value;
				
			}
			
			//set the rating if it's available
			if (document.getElementById('customHeader') != undefined) {
				
				if (CKEDITOR.instances.customHeader) {	
					
					theForm.elements["customHeader"].value = CKEDITOR.instances.customHeader.getData();
					
				} else {
					
					theForm.elements["customHeader"].value = document.getElementById('customHeader').innerHTML;
					
				}
				
			}
			
			//create window and send the data to the server
			var previewWindow = window.open('', '_preview', 'width=1000,height=600,status=yes,resizable=yes,scrollbars=yes');
			
			theForm.submit();
			
			//bring the window to the front
			if (previewWindow) {
				
				previewWindow.focus();
				
			}
			
		}
	
	}


	CKEDITOR.plugins.add('sonet_preview', {
		
		init: function(editor) {
			
			editor.addCommand(pluginName, sonetPreviewCmd);
			
			editor.ui.addButton('SonetPreview', {
				
				label : "Preview",
				command : pluginName,
				icon : "/assets/core/resources/javascript/ckeditor/plugins/sonet_preview/preview.png"
				
			});
			
		}
		
	});
	
})();