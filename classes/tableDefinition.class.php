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
		$query = "DESCRIBE " . $this->table;
		$results = $this->db->query($query);
		while ( $row = $this->db->fetchrow($results) ){
			$this->tableDef[$row['Field']] = $row;
			if ( $row['Key'] == 'PRI' ){
				$this->primaryKey = $row['Field'];
			}
		}
	}

	public function fieldExists($field){
		return !empty($this->tableDef[$field]);
	}
}