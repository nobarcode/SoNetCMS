<?php

class ProcessBbcode {
	
	function convert($body) {
		
		//quote & <p> cleanup
		$body = preg_replace("/<p>\n[\t]+\[\[quote=\"(.*?)\"\]\](.+?)<\/p>/i", "[[quote=\"$1\"]]\n<p>$2</p>", $body);
		$body = preg_replace("/<p>\n[\t]+\[\[quote/i", "[[quote", $body);
		$body = preg_replace("/<p>\n[\t]+\[\[\/quote\]\]<\/p>/i", "[[/quote]]", $body);
		$body = preg_replace("/<p>\n[\t]+(.*?)\[\[\/quote\]\]<\/p>/i", "<p>$1</p>\n[[/quote]]", $body);
		$body = preg_replace("/\[\[\/quote\]\]<\/p>/i", "[[/quote]]", $body);
		
		//quote
		$body = preg_replace("/\[\[quote=\"(.*?)\"\]\](.*?)\[\[\/quote\]\]/is", "<div class=\"quote_container\"><div class=\"quote_user\">$1 wrote:</div><div class=\"quote_body\">$2</div></div>", $body);
		$body = $this->parseQuoteTagsRecursive($body);
		
		return($body);
		
	}
	
	function parseQuoteTagsRecursive($input)
	{
	
		$regex = '#\[\[quote=\"(.*?)\"\]\](.*)\[\[/quote\]\]#is';
	
		if (is_array($input)) {
			$input = "<div class=\"quote_container\"><div class=\"quote_user\">" . $input[1] . " wrote:</div><div class=\"quote_body\">" . $input[2] . "</div></div>";
		}
	
		return preg_replace_callback($regex, array(&$this, 'parseQuoteTagsRecursive'), $input);
	}
	
	function strip($body) {
		
		//quote & <p> cleanup
		$body = preg_replace("/<p>\n[\t]+\[\[quote=\"(.*?)\"\]\](.+?)<\/p>/i", "[[quote=\"$1\"]]\n<p>$2</p>", $body);
		$body = preg_replace("/<p>\n[\t]+\[\[quote/i", "[[quote", $body);
		$body = preg_replace("/<p>\n[\t]+\[\[\/quote\]\]<\/p>/i", "[[/quote]]", $body);
		$body = preg_replace("/<p>\n[\t]+(.*?)\[\/quote\]\]<\/p>/i", "<p>$1</p>\n[[/quote]]", $body);
		$body = preg_replace("/\[\[\/quote\]\]<\/p>/i", "[[/quote]]", $body);
		
		//quote
		$body = preg_replace("/\[\[quote=\"(.*?)\"\]\](.*)\[\[\/quote\]\]/is", "...", $body);
		
		//images
		$body = preg_replace("/\<img alt=\"(.*?)\"(.*?)\>/is", "$1", $body);
		
		$body = strip_tags($body, '<p>');
		
		return($body);
		
	}
	
}

?>