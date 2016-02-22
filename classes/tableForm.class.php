<?php

class tableForm {
	private $_data = array();

	function __construct($model=null,$columns=null,$query=null, $order_by = null){
		if ( $model != null )
			$this->model = $model;

		if ( is_array($columns) )
			$this->columns = $columns;

		if ( $query != null )
			$this->query = $query;

		if ( $order_by != null )
			$this->order_by = $order_by;

		global $db;
		$this->db = $db;
	}

	public function __set($key,$value){
		$this->_data["{$key}"] = $value;
	}

	public function __get($key){
		return $this->_data["{$key}"];
	}

	public function generate($blankEnd = false){
		$m = $this->model;
		$cl = new $m();

		$table = $cl->getTable();
		$key = $cl->getKey();
		$fd = $cl->getFormDetails();

		$SQL = "SELECT " . $key . ", " . implode(",",$this->columns) . " FROM " . $table;
			if ( $this->query != "" )
				$SQL .= " WHERE " . $this->query;

			if ( $this->order_by != "" )
				$SQL .= " " . $this->order_by;

		$results = $this->db->query($SQL);

		$guid = GUID();
		$form = new PFBC\Form($guid);
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery"),
				"ajax" => 1,
				"ajaxCallback" => "saveFormResponse",
				"action" => SITE_URL . "ajax/saveForm",
				"model" => $this->table
		));
		$form->addElement(new PFBC\Element\Hidden("guid",$guid));

			foreach ( $this->columns as $column ){
				echo "<th>" . $fd["{$column}"]['label'] . "</th>";
			}
			echo "</tr>";

			while ( $row = $this->db->fetchrow($results) ){
				echo "<tr>";
				foreach ( $this->columns as $column ){
					echo "<td>" .
				}
				echo "</tr>";
			}

		echo "</table>";
	}
}

?>