<?php

class tableDefinition {
	private $table = null;
	private $tableDef = array();
	private $primaryKey = null;
	private $db = null;

	function __construct($table, $dbConnection){
		$this->table = $table;
		$this->db = $dbConnection;
		$this->getTableDefinition();
	}

	public function getTableDefinition(){
	    if ( false === $cachedDef = cache::get('tableDef:' . $this->table) ){
            $query = "DESCRIBE " . $this->table;
            $results = $this->db->query($query);
            if ( $results ){
                while ( $row = $this->db->fetchrow($results) ){
                    $this->tableDef[$row['Field']] = $row;
                    if ( $row['Key'] == 'PRI' ){
                        $this->primaryKey = $row['Field'];
                    }
                    if ( substr($row['Type'],0,4) == 'enum' ){
                        $vals = substr($row['Type'],6,strlen($row['Type'])-8);
                        $vals = explode('\',\'', $vals);
                        $vResults = array();
                        foreach ( $vals as $val ){
                            $vResults[] = array('text' => ucwords($val), 'value' => $val);
                        }
                        $this->tableDef[$row['Field']]['enum_vals'] = $vResults;
                    }
                }
                cache::set('tableDef:' . $this->table, array('fields' => $this->tableDef, 'key' => $this->primaryKey), 86400);
            } else {
                throw new Exception('Unable to load table definition. ' . $query);
            }
        } else {
            $this->tableDef = $cachedDef['fields'];
            $this->primaryKey = $cachedDef['key'];
        }
	}

	public function fieldExists($field){
		return !empty($this->tableDef[$field]);
	}

	public function getPrimaryKey(){
		return $this->primaryKey;
	}

	public function getColumns(){
		return array_keys($this->tableDef);
	}

	public function getEnumVals($field){
	    return !empty($this->tableDef[$field]['enum_vals']) ? $this->tableDef[$field]['enum_vals'] : false;
    }
}