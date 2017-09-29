<?php

class collection_base {
	protected static $dbConnectionName = 'default';

	protected static $table = null;
	protected static $tableDef = null;
	protected static $db;

	protected static function getDb(){
        debugLog(static::class . '::getDb',4);
		if ( empty(static::$db) ){
		    debugLog("Establishing a new connection (" . static::$dbConnectionName . ")",4);
			static::$db = dbConnection::getConnection(static::$dbConnectionName);
		}
		return static::$db;
	}

	protected static function getTableDef(){
	    debugLog(get_called_class() . '::getTableDef',4);
	    debugLog(static::$tableDef);
		self::getTable();
		if ( empty(static::$tableDef) ){
		    debugLog("not set");
			static::$tableDef = new tableDefinition(static::$table, self::getDb());
		}
		return static::$tableDef;
	}

	protected static function getTable(){
        if ( static::$table === null ){
            static::$table = str_replace("Collection","",get_called_class());
        }
        return static::$table;
    }


	public static function getAllWhere($where){

	}

	public static function add($data){
	    debugLog(get_called_class() . '::add',4);
	    debugLog($data,4);
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

	public static function update($id, $data, $keyField = null){
        debugLog(get_called_class() . '::update',4);
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

	public static function deleteById($id){
	    $query = "DELETE FROM " . static::$table . " WHERE " . self::getTableDef()->getPrimaryKey() . " = '" . self::getDb()->escape($id) . "'";
	    return self::getDb()->query($query);
    }

    public static function deleteByField($field, $value, $limit = false){
        $query = "DELETE FROM " . static::$table . " WHERE `{$field}` = '" . self::getDb()->escape($value) . "'";
        if ( $limit !== false && is_numeric($limit)){
            $query .= " LIMIT " . $limit;
        }
        return self::getDb()->query($query);
    }

    public static function getByField($field, $value){
        $query = "SELECT * FROM " . static::$table . " WHERE `" . $field . "` = '" . self::getDb()->escape($value) . "'";
        $results = self::getDb()->query($query);
        $rows = array();
        while ( $row = self::getDb()->fetchRow($results) ){
            $rows[] = $row;
        }
        return $rows;
    }
}
