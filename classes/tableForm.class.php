<?php

class tableForm {
	private $_data = array();

	function __construct($model=null,$columns=null,$query=null,$order_by = null){
		if ( $model != null )
			$this->model = $model;

		if ( is_array($columns) )
			$this->columns = $columns;

		if ( $query != null )
			$this->query = $query;

		if ( $order_by != null )
			$this->order_by = $order_by;
	}

	public function __set($key,$value){
		$this->_data["{$key}"] = $value;
	}

	public function __get($key){
		return !empty($this->_data["{$key}"]) ? $this->_data["{$key}"] : false;
	}

	public function render($blankEnd = false){
		$m = $this->model;
		$cl = new $m();

		$table = $cl->getTable();
		$key = $cl->getKey();
		$fd = $cl->getFormDetails();

		$SQL = "SELECT " . implode(",",$this->columns) . " FROM " . $table;
			if ( $this->query != "" )
				$SQL .= " WHERE " . $this->query;

			if ( $this->order_by != "" )
				$SQL .= " " . $this->order_by;

		$results = $cl->db->query($SQL);
		/*
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
		*/
		echo "<form method=\"post\">";
			echo "<table>";
				foreach ( $this->columns as $column ){
					echo "<th>" . $fd["{$column}"]['label'] . "</th>";
				}
				echo "</tr>";

				while ( $row = $cl->db->fetchrow($results) ){
					echo "<tr>";
					foreach ( $this->columns as $column ){
							if ( $column == $key ){
								echo "<td>" . $row["$column"] . "<input type=\"hidden\" name=\"" . $column . "[]\" value=\"" . $row["$column"] . "\" /></td>";
							} else {
								echo "<td>";
									echo "<input type=\"text\" name=\"" . $column . "[]\" value=\"" . $row["$column"] . "\" />";
								echo "</td>";
							}

					}
					echo "</tr>";
				}
				if ( $blankEnd == true ){
					// add a blank one on the end to add a new item
					foreach ( $this->columns as $column ){
						if ( $column == $key ){
							echo "<td>&nbsp;<input type=\"hidden\" name=\"" . $column . "[]\" /></td>";
						} else {
							echo "<td>";
								echo "<input type=\"text\" name=\"" . $column . "[]\" />";
							echo "</td>";
						}
					}
				}

			echo "</table>";
			echo "<input type=\"submit\" name=\"save_button\" value=\"Save\" />";
		echo "</form>";
	}
}

?>