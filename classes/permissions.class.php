<?php

class permissions {
	private static $_perms = array();
	private static $_permById = array();
	private static $_permGroups = array();
	private static $_nextNumber = 1;

	public static function getById($id){
		return self::$_permById[$id];
	}

	public static function add($key,$group = "default"){
		self::$_perms[$key] = self::$_nextNumber;
		self::$_permById[self::$_nextNumber] = $key;

		if ( empty(self::$_permGroups[$group]) ){
			self::$_permGroups[$group] = array();
		}

		self::$_permGroups[$group][] = self::$_nextNumber;

		self::$_nextNumber = self::$_nextNumber * 2;
	}


	public static function get($name){
		return self::$_perms[$name];
	}

	public static function getAll(){
		return self::$_perms;
	}
}

?>