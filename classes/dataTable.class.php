<?php

class dataTable {
	private $_query;
	private $_bootstrap;
	private $_columns = array();
	private $_actions;
	private $_groupby;
	private $_rowurl;

	function __construct($bootstrap=false){
		$this->_boostrap = $bootstrap;
	}

	public function addColumns($aryColumns){
		foreach ( $aryColumns as $column )
			$this->addColumn($column);
	}

	public function addColumn($column){
		$c = new dataTableColumn($column['field']);
		$c->link = $column['link'];
		$c->header = $column['header'];
		$c->footer = $column['footer'];
		$c->format = $column['format'];
		$this->_columns[] = $c;
	}

	public function setQuery($SQL){
		$this->_query = $SQL;
	}

	public function setActions($a){
		$this->_actions = $a;
	}

	public function setGroup($g){
		$this->_groupby = $g;
	}

	public function setRowUrl($url){
		$this->_rowurl = $url;
	}

	public function render(){
		global $db;

		echo "<table class=\"table table-striped table-hover table-bordered\">";
		echo "<tr>";
			echo "<thead>";
				foreach ( $this->_columns as $column ){
					echo "<th>" . $column->header . "</th>";
				}

				if ( $this->_actions != "" )
					echo "<th>&nbsp;</th>";
			echo "</thead>";
		echo "</tr>";
		$r = $db->query($this->_query);
		if ( $db->numrows($r) == 0 ){
			echo "<tr><td colspan=\"500\">No results!</td></tr>";
		} else {
			$last_group = null;
			while ( $row = $db->fetchrow($r) ){
				if ( $this->_groupby != "" ){
					$row_group = $row["{$this->_groupby}"];
					if ( $row_group != $last_group ){
						$clean_row_group = cleanURL($row_group);
						echo "<thead>";
						echo "<tr class=\"group-header\" data-group=\"" . $clean_row_group . "\"><td colspan=\"" . (count($this->_columns) + 1) . "\">" . $row_group . "</td></tr>";
						echo "</thead>";
					}
					$last_group = $row_group;
				}
				echo "<tr";

					if ( $this->_groupby != "" )
						echo " class=\"group-row group-" . $clean_row_group . "\"";

					if ( $this->_rowurl != "" )
						echo " data-href=\"" . vsprintf($this->_rowurl,$row) . "\"";

				echo ">";
					foreach ( $this->_columns as $c ){
						echo "<td>";
							$c->render($row);
						echo "</td>";
					}
					if ( $this->_actions != "" ){
						echo "<td>" . vsprintf($this->_actions,$row) . "</td>";
					}
				echo "</tr>";
			}
		}


		echo "</table>";
	}
}

class dataTableColumn {
	public $link = "";
	public $header = "";
	public $footer = "";
	public $field = "";
	public $format = "text";

	function __construct($field){
		$this->field = $field;
	}

	public function render($data){
		switch ( $this->format ){
			case "text":
			default:
				$contents = $data["{$this->field}"];
			break;
		}
		// build the link
		if ( $this->link != "" ){
			$contents = "<a href=\"" . vsprintf($this->link,$data) . "\">" . $contents . "</a>";
		}
		echo $contents;
	}
}