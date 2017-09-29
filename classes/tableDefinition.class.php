<?php

class tableDefinition {
	private $table = null;
	private $tableDef = array();
	private $primaryKey = null;
	private $db = null;

	function __construct($table, $dbConnection){
	    debugLog("tableDefinition::__construct($table)");
		$this->table = $table;
		$this->db = $dbConnection;
		$this->getTableDefinition();
	}

	public function getTableDefinition(){
	    if ( false === $cachedDef = cache::get('tableDef:' . $this->table) ){
            $query = "DESCRIBE " . $this->table;
            $results = $this->db->query($query);
            while ( $row = $this->db->fetchrow($results) ){
                $this->tableDef[$row['Field']] = $row;
                if ( $row['Key'] == 'PRI' ){
                    $this->primaryKey = $row['Field'];
                }
            }
            cache::set('tableDef:' . $this->table, array('fields' => $this->tableDef, 'key' => $this->primaryKey));
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
}