<?php

class view_base 
	
	function save(){
		global $db;
		$db->updateRow($this->table, $this->data, $this->key);
	}
	
	function load($keyValue){
		global $db;
		$db->getRow($this->table, $this->columns, $this->key, $keyValue);
	}

}

?>