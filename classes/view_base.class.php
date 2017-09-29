<?php

class view_base {

	public function save(){
		global $db;
		$db->updateRow($this->table, $this->data, $this->key);
	}

	public function load($keyValue){
		global $db;
		$db->getRow($this->table, $this->columns, $this->key, $keyValue);
	}

}

?>