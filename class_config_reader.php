<?php

class ConfigReader {
	
	var $configFile;
	var $comment;
	var $configValues;
	
	//method to set the configuration path and file to be read by other methods
	function loadConfigFile($file) {
		
		$this->configFile = $file;
		
		if (trim($this->comment) == "") {
			
			$this->comment = "#";
			
		}
		
		if (!file_exists($this->configFile)) {die("Configuration file not found.");}
		
		$fp = fopen($this->configFile, "r");
		
		while (!feof($fp)) {
			
			$line = trim(fgets($fp));
			
			if ($line && !preg_match("/^$this->comment/", $line)) {
				
				preg_match("/^(.*?)=(.*?)$/", $line, $pieces);
				$option = trim($pieces[1]);
				$value = trim($pieces[2]);
				$this->configValues[$option] = $value;
				
			}
			
		}
		
		fclose($fp);
		
	}
	
	function readValue($parameter) {
		
		return($this->configValues["$parameter"]);
		
	}
	
}

?>