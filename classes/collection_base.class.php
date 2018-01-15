<?php

class collection_base {
	protected static $dbConnectionName = 'default';

	protected static $table = null;
	protected static $tableDef = null;
	protected static $db;

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
		    debugLog($field . "=>" . $value,4);
			if ( self::getTableDef()->fieldExists($field) && !in_array($field, $fields) ){
			    debugLog("added");
				$fields[] = $field;
				$values[] = self::getDb()->escape($value);
			}
		}
		$query = "INSERT INTO " . static::$table . " (" . implode(",",$fields) . ") values('" . implode("','", $values) . "');";
		if ( self::getDb()->query($query) ){
		    return self::getDb()->lastid();
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
                $sets[] = "`" . $field . "` = '" . self::getDb()->escape($value) . "'";
            }
        }

        if ( !empty($sets) ){
            $query = "UPDATE " . static::$table . " SET " . implode(",",$sets) . " WHERE " . $keyField . " = '" . self::getDb()->escape($id) . "'";
            if ( self::getDb()->query($query) ){
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
	    return self::getDb()->query($query);
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
        return self::getDb()->query($query);
    }

    /**
     * @param string $field
     * @param string $value
     * @return array
     */
    public static function getByField($field, $value){
        $query = "SELECT * FROM " . static::$table . " WHERE `" . $field . "` = '" . self::getDb()->escape($value) . "'";
        $results = self::getDb()->query($query);
        $rows = array();
        while ( $row = self::getDb()->fetchRow($results) ){
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @param db $dbConnection
     */
    public static function setDbConnection(db $dbConnection){
        debugLog($dbConnection);
        static::$db = $dbConnection;
    }
}
