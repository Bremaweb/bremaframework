<?php

class registry {
	private static $_data = array();

	public static function set($index, $value){
		self::$_data[$index] = $value;
	}

	public static function get($index){
		return !empty(self::$_data[$index]) ? self::$_data[$index] : null;
	}
}

