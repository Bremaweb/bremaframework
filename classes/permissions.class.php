<?php

class permissions {
	private static $_perms = array();
	private static $_permById = array();
	private static $_permGroups = array();
	private static $_nextNumber = 1;
	private static $_permDefault = false;

    /**
     * @param int $id
     * @return mixed
     */
	public static function getById($id){
		return self::$_permById[$id];
	}

	public static function setDefault($def){
	    static::$_permDefault = !empty($def);
    }

    /**
     * @param string $key
     * @param string $group
     */
	public static function add($key,$group = "default"){
	    $key = strtolower($key);
		self::$_perms[$key] = self::$_nextNumber;
		self::$_permById[self::$_nextNumber] = $key;

		if ( empty(self::$_permGroups[$group]) ){
			self::$_permGroups[$group] = array();
		}

		self::$_permGroups[$group][] = self::$_nextNumber;

		self::$_nextNumber = self::$_nextNumber * 2;
	}

    /**
     * @param string $name
     * @return bool
     */
	public static function get($name){
	    $name = strtolower($name);
		return !empty(self::$_perms[$name]) ? self::$_perms[$name] : self::$_permDefault;
	}

    /**
     * @param null $group
     * @return array|mixed
     */
	public static function getAll($group = null){
	    if ( empty($group) ){
		    return self::$_perms;
        } else {
	        return self::$_permGroups[$group];
        }
	}

    /**
     * @return int
     */
	public static function getMax(){
	    return self::$_nextNumber;
    }

    public static function humanReadable($permName){
        return ucwords(str_replace('_',' ',$permName));
    }
}
