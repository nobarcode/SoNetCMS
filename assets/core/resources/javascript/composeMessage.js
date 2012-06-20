function setCursorPosition() {
	
	if($("#reply").val() != "") {
		
		document.getElementById("body").focus();
		document.getElementById("body").selectionStart=0;
		document.getElementById("body").selectionEnd=0;
		
	}
	       
}

$(document).ready(function() {
	
	setCursorPosition();
	
});