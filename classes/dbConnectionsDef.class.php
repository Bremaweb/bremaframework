<?php

class dbConnectionsDef {
	private static $definitions = array();
	private static $hasMaster = false;
	private static $masterName = null;

	public static function addConnectionDef($defName, $dbName, $dbHost, $dbPort, $dbUsername, $dbPassword, $dbType = "mysqli", $master = false){
		self::$definitions[$defName] = array(
				'dbName' => $dbName,
				'dbHost' => $dbHost,
				'dbPort' => $dbPort,
				'dbUser' => $dbUsername,
				'dbPassword' => $dbPassword,
				'dbType' => $dbType
				);

		if ( $master ){
		    static::$hasMaster = true;
		    static::$masterName = $defName;
        }
	}

	public static function getConnectionDef($defName){
		return !empty(self::$definitions[$defName]) ? self::$definitions[$defName] : false;
	}

	public static function hasConnectionDefs(){
		return !empty(self::$definitions);
	}

	public static function defExists($defName){
		return !empty(self::$definitions[$defName]);
	}

    /**
     * @return bool
     */
	public static function hasMasterConnection(){
	    return static::$hasMaster;
    }

    /**
     * @return string
     */
    public static function getMasterConnectionId(){
	    return static::$masterName;
    }
}