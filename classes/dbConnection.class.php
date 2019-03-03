<?php

class dbConnection {
	private static $instance = array();
	protected $connection = null;

    /**
     * dbConnection constructor.
     * @param $connName
     */
	private function __construct($connName){
		if ( dbConnectionsDef::defExists($connName) ){
			$def = dbConnectionsDef::getConnectionDef($connName);
			require_once(BREMA_DIR . "/classes/" . $def['dbType'] . ".db.class.php");
			$this->connection = new db($def['dbHost'],$def['dbPort'],$def['dbName'],$def['dbUser'],$def['dbPassword']);
		} else {
			// else fall back to the old behavior
			require_once(BREMA_DIR . "/classes/" . DB_TYPE . ".db.class.php");
			$this->connection = new db(DB_HOST,DB_PORT,DATABASE,DB_USER,DB_PASSWORD);
		}
	}

    /**
     * @param string $connName
     * @return dbConnection
     */
	public static function getConnection($connName = 'default'){
		if ( !empty(self::$instance[$connName]) ){
			return self::$instance[$connName]->connection;
		} else {
			self::$instance[$connName] = new dbConnection($connName);
		}
		return self::$instance[$connName]->connection;
	}

    /**
     * @return dbConnection[]
     */
	public static function getAllConnections(){
	    return self::$instance;
    }
}