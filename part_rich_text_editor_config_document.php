<?php

$result = mysql_query("SELECT username FROM autoSaveContent WHERE username = '{$_SESSION['username']}'");

if (mysql_num_rows($result) > 0) {

	$richTextEditorConfig .= "		\n";
	
	if (trim($id) != "") {
	
		$richTextEditorConfig .= "		customConfig : '/assets/core/resources/javascript/ckeditor/config_document_autosave.js'\n";
		
	} else {
		
		$richTextEditorConfig .= "		customConfig : '/assets/core/resources/javascript/ckeditor/config_document_autosave_not_editing.js'\n";
		
	}
	
} else {
	
	$richTextEditorConfig .= "		\n";
	
	if (trim($id) != "") {
	
		$richTextEditorConfig .= "		customConfig : '/assets/core/resources/javascript/ckeditor/config_document.js'\n";
		
	} else {
		
		$richTextEditorConfig .= "		customConfig : '/assets/core/resources/javascript/ckeditor/config_document_not_editing.js'\n";
		
	}
	
}

?>


