<?php

class collection_base {
	protected $table = null;
	private $tableDef = null;

	function __construct(){
		if ( $this->table === null ){
			$this->table = str_replace("Collection","",get_class($this));
		}
		$this->tableDef = new tableDefinition($this->table);
	}

	public function getAllWhere($where){

	}

	public function add($data){
		$fields = array();
		$values = array();
		foreach ( $data as $field => $value ){
			if ( $this->tableDef->fieldExists($field) && !in_array($field, $fields) ){
				$fields[] = $field;
				$values[] = $value;
			}
		}
		$query = "INSERT INTO " . $this->table . " (" . implode(",",$fields) . ") values('" . implode("','", array_map($values, $this->db->escape)) . "');";
		return $this->db->query($query);
	}
}
