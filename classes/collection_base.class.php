<?php

class collection_base {
	protected static $dbConnectionName = 'default';

	protected static $table = null;
	protected static $tableDef = null;
	protected static $db;
	protected static $wDb;

    /**
     * @return db
     */
	protected static function getDb(){
		if ( empty(static::$db) ){
			static::$db = dbConnection::getConnection(static::$dbConnectionName);
		}
		return static::$db;
	}

    /**
     * @return db
     */
	protected static function getwDb(){
        if ( empty(static::$wDb) ){
            if ( dbConnectionsDef::hasMasterConnection() ){
                static::$wDb = dbConnection::getConnection(dbConnectionsDef::getMasterConnectionId());
            } else {
                return self::getDb();
            }
        }
        return static::$wDb;
    }

    /**
     * @return tableDefinition
     */
	protected static function getTableDef(){
		self::getTable();
		if ( empty(static::$tableDef) ){
			static::$tableDef = new tableDefinition(static::$table, self::getDb());
		}
		return static::$tableDef;
	}

    /**
     * @return string
     */
	protected static function getTable(){
        if ( static::$table === null ){
            static::$table = str_replace("Collection","",get_called_class());
        }
        return static::$table;
    }


    /**
     * @param string $where
     * @param string $orderByField
     * @param string $orderBy
     * @return array
     */
	public static function getAllWhere($where, $orderByField = null, $orderBy = null){
        $db = self::getDb();
        $results = $db->query('SELECT * FROM ' . self::getTable() . ' WHERE ' . $where . ( $orderByField != null & $orderBy != null ? $orderByField . ' ' . $orderBy : '' ));
        $rows = array();
        while ( $row = $db->fetchRow($results) ){
            $rows[] = $row;
        }
        return $rows;
	}

    /**
     * @param string $where
     * @param string $orderByField
     * @param string $orderBy
     * @return array|null
     */
	public static function getOneWhere($where, $orderByField = null, $orderBy = null){
	    $db = self::getDb();
        $results = $db->query('SELECT * FROM ' . self::getTable() . ' WHERE ' . $where . ( $orderByField != null & $orderBy != null ? $orderByField . ' ' . $orderBy : '' ) . ' LIMIT 1');
        return $db->fetchrow($results);
    }

    /**
     * @param array $data
     * @return bool|int|null|string
     */
	public static function add($data){
		$fields = array();
		$values = array();
		foreach ( $data as $field => $value ){
			if ( self::getTableDef()->fieldExists($field) && !in_array($field, $fields) ){
				$fields[] = $field;
				$values[] = self::getwDb()->escape($value);
			}
		}
		$query = "INSERT INTO " . static::$table . " (" . implode(",",$fields) . ") values('" . implode("','", $values) . "');";
		if ( self::getwDb()->query($query) ){
		    return self::getwDb()->lastid();
        }
        return false;
	}

    /**
     * @param string $id
     * @param array $data
     * @param string $keyField
     * @return bool
     */
	public static function update($id, $data, $keyField = null){
        $keyField = $keyField == null ? self::getTableDef()->getPrimaryKey() : $keyField;
        $sets = array();
        foreach ( $data as $field => $value ){
            if ( self::getTableDef()->fieldExists($field) ){
                $sets[] = "`" . $field . "` = '" . self::getwDb()->escape($value) . "'";
            }
        }

        if ( !empty($sets) ){
            $query = "UPDATE " . static::$table . " SET " . implode(",",$sets) . " WHERE " . $keyField . " = '" . self::getDb()->escape($id) . "'";
            if ( self::getwDb()->query($query) ){
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $id
     * @return bool|mysqli_result|resource|string
     */
	public static function deleteById($id){
	    $query = "DELETE FROM " . static::$table . " WHERE " . self::getTableDef()->getPrimaryKey() . " = '" . self::getDb()->escape($id) . "'";
	    return self::getwDb()->query($query);
    }

    /**
     * @param $id
     * @return array|bool|null
     */
    public static function getById($id){
	    $query = "SELECT * FROM " . static::$table . " WHERE " . self::getTableDef()->getPrimaryKey() . " = '" . self::getDb()->escape($id) . "'";
	    return self::getDb()->queryrow($query);
    }

    /**
     * @param string $field
     * @param string $value
     * @param bool|int $limit
     * @return bool|mysqli_result|resource|string
     */
    public static function deleteByField($field, $value, $limit = false){
        $query = "DELETE FROM " . static::$table . " WHERE `{$field}` = '" . self::getDb()->escape($value) . "'";
        if ( $limit !== false && is_numeric($limit)){
            $query .= " LIMIT " . $limit;
        }
        return self::getwDb()->query($query);
    }

    /**
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function getByField($field, $value, $order = null){
        $query = "SELECT * FROM " . static::$table . " WHERE `" . $field . "` = '" . self::getDb()->escape($value) . "'";
        if ( !empty($order) ){
            $query .= ' ORDER BY ' . $order;
        }
        $results = self::getDb()->query($query);
        $rows = array();
        while ( $row = self::getDb()->fetchRow($results) ){
            $rows[] = $row;
        }
        return !empty($rows) ? $rows : false;
    }

    /**
     * @param null $order
     * @param null $limit
     * @return array|bool
     */
    public static function getAll($order = null, $limit = null){
       $query = "SELECT * FROM " . self::getTable();
       if ( $order !== null ){
            $query .= ' ORDER BY ' . $order;
       }

       if ( $limit !== null ){
            $query .= ' LIMIT ' . $limit;
       }

       return self::getDb()->getRows($query);
    }

    /**
     * @param db $dbConnection
     */
    public static function setDbConnection(db $dbConnection){
        static::$db = $dbConnection;
    }

    /**
     * @param db $dbConnection
     */
    public static function setwDbConnection(db $dbConnection){
        static::$wDb = $dbConnection;
    }
}
