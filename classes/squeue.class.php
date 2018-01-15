<?php
class squeue {
	private $scripts = array();
	private static $instances = array();

	private $type = "";

	private function __construct($type){
		$this->type = $type;
	}

    /**
     * @param $type
     * @return squeue
     */
	public static function getInstance($type){
	    if ( empty(static::$instances[$type]) ){
	        static::$instances[$type] = new squeue($type);
        }
        return static::$instances[$type];
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

	public static function generate($type){
	    $instance = self::getInstance($type);
		foreach ( $instance->scripts as $k => $s ){
			if ( $instance->type == "javascript" )
				echo "<script type=\"text/javascript\" src=\"" . $s . "\"></script>\r\n";

			if ( $instance->type == "css" )
				echo "<link rel=\"stylesheet\" href=\"" . $s . "\" />\r\n";
		}
	}
}