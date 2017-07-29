<?php
class settings{
	private $settingValues = array();
	private $table;
	private $key;
	private $keyvalue;

	private $db;

	function __construct ($table = "settings", $key="setting", $keyvalue=""){
		$this->db = dbConnection::getConnection();
		$SQL="SELECT * FROM $table ";

		if ( $keyvalue != "" )
			$SQL .= "WHERE " . $key . " = '" . $this->db->escape($keyvalue) . "'";

		$results = $this->db->query($SQL);
		while ( $row = $this->db->fetchrow($results) )
			$this->settingValues["{$row['setting']}"] = $row['setting_value'];
	}


	function __get ($sName){
		return $this->settingValues["$sName"];
	}

	function __set ($sName,$sVal){
		global $app;

		if ( isset($this->settingValues["$sName"]) )
			$SQL = "UPDATE settings SET setting_value = '" . addslashes($sVal) . "' WHERE setting='$sName'";
		else
			$SQL = "INSERT INTO settings ( setting, setting_value ) values('$sName','$sVal')";

		$results = $this->db->query($SQL);
		if ( $results ) {
			$this->settingValues["$sName"]=$sVal;
			return true;
		} else {
			return false;
		}
	}
}
?>
