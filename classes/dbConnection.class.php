<?php

class dbConnection {
	private static $instance = null;
	private static $connection = null;

	private function __construct(){
		require_once(BREMA_DIR . "/classes/" . DB_TYPE . ".db.class.php");
		$this->connection = new db(DB_HOST,DB_PORT,DATABASE,DB_USER,DB_PASSWORD);
	}

	public static function getConnection(){

		if ( !empty(self::$instance) ){
			return self::$instance->connection;
		} else {
			self::$instance = new dbConnection();
		}
		debugLog(self::$instance->connection);
		return self::$instance->connection;
	}
}