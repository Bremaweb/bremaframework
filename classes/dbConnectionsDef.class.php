<?php

class dbConnectionsDef {
	private static $definitions = array();

	public static function addConnectionDef($defName, $dbName, $dbHost, $dbPort, $dbUsername, $dbPassword, $dbType = "mysqli"){
		self::$definitions[$defName] = array(
				'dbName' => $dbName,
				'dbHost' => $dbHost,
				'dbPort' => $dbPort,
				'dbUser' => $dbUsername,
				'dbPassword' => $dbPassword,
				'dbType' => $dbType
				);
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
}