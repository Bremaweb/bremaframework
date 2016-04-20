<?php
class squeue {
	private $scripts = array();

	private $type = "";

	function __construct($type){
		$this->type = $type;
	}
	// pass the full url of the script
	function enqueue($script){
		// this will check to see if it has already been enqueued
		if (stripos($script,"HTTP") === false )
			$script = SITE_URL . $script;

		if ( array_search($script,$this->scripts) === false ){
			$this->scripts[] = $script;
		}
	}

	function add($script){
		$this->enqueue($script);
	}

	function generate(){
		foreach ( $this->scripts as $k => $s ){
			if ( $this->type == "javascript" )
				echo "<script type=\"text/javascript\" src=\"" . $s . "\"></script>\r\n";

			if ( $this->type == "css" )
				echo "<link rel=\"stylesheet\" href=\"" . $s . "\" />\r\n";
		}
	}
}