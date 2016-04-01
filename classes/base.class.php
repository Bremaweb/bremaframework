<?php

class base {
	protected $_data = array();

	function __set($key,$value){
		$this->_data["{$key}"] = $value;
	}

	function __get(){
		return $this->_data["{$key}"];
	}
}