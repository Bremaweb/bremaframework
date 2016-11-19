<?php

class db {

    private $result = "";
	private $_dbconn;
    function __construct($host, $port, $database, $user, $password){	// original parameters ($dbHost,$dbPort,$dbDatabase,$dbUser,$dbPassword)
    	    $this->_dbconn = mysqli_connect($host,$user,$password,$database,$port);
			if ( !$this->_dbconn )
				die("Unable to connect to database..." . mysqli_error($this->_dbconn));
    }

    function query($SQL, $displayErrors = true){
    	debugLog("query($SQL)",4);
        $this->result = @mysqli_query($this->_dbconn, $SQL);

        if (!$this->result){
        	debugLog(mysqli_error($this->_dbconn));
            debugLog($SQL);
        }

        return $this->result;
    }

    function queryrow($SQL, $displayErrors = true){
        // queryrow does the query and just returns one row
        $result = $this->query($SQL, $displayErrors);
            if ($result){
                return $this->fetchrow($result);
                @mysqli_free_result($result);
            }else{
                return false;
            }
    }

    function fetchrow($result = 0){

        if (!$result)
            $result = $this->result;

        $row = @mysqli_fetch_assoc($result);
        return $row;
    }

    function fetcharray($result = 0){
    	if (!$result)
    		$result = $this->result;

    	$row = @mysqli_fetch_array($result);
    	return $row;
    }

    function lastid(){
        return mysqli_insert_id($this->_dbconn);
    }

    function numrows($result=0){
        if (!$result)
            $result = $this->result;

        return @mysqli_num_rows($result);
    }

	function affectedrows(){

        return @mysqli_affected_rows();
    }

    function __destruct(){
        mysqli_close($this->_dbconn);
    }

	function getRows($SQL,$optionArray=false){
		$results = $this->query($SQL);

		if ( !$results )
			return false;

		$returnRows = array();
		while ( $row = $this->fetcharray($results) ){
			if ( $optionArray )
				$returnRows[$row[0]] = $row[1];
			else
				$returnRows[] = $row;
		}

		return $returnRows;
	}

    function insertRow($table, $values){
    	debugLog("insertRow($table,$values);");
    	debugLog($values);
    	$columns = $this->getTableCols($table,false);
    	debugLog($columns);
        $SQL = "INSERT INTO $table (";
        $SQLv = "";
        foreach ( $values as $k => $v ){
            if ( in_array($k,$columns) ){
                $SQLv .= "'" . $this->escape($v) . "',";
                $SQL .= "$k,";
            }
        }
        $SQL = substr($SQL,0,strlen($SQL)-1);
        $SQLv = substr($SQLv,0,strlen($SQLv)-1);

        $SQL .= ") values(" . $SQLv . ")";

        $SQL = substr($SQL,0,strlen($SQL)-1);

        $SQL .= ")";

        debugLog($SQL);

        $results = $this->query($SQL);
        if ( $results )
            return mysqli_insert_id();
        else
            return false;
    }

	function getRow ($table, $columns, $key, $keyValue ){
		$SQL = "SELECT " . implode(",",$columns) . " FROM " . $table . " WHERE " . $key . " = '" . $keyValue . "'";
		return $this->queryrow($SQL);
	}

	function getRowQuery ($table, $columns, $params){
		$p2 = array();
		$SQL = "SELECT " . implode(",",$columns) . " FROM " . $table;
		foreach ( $params as $k => $v )
			$p2[] = $k . " = '" . $this->escape($v) . "' ";
		$SQL .= " WHERE " . implode("AND ", $p2);
		return $this->queryrow($SQL);
	}

    function updateRow($table,$values,$key,$keyValue="",$columns = ""){
		debugLog("updateRow();");
    	debugLog($table);

		if ( $columns == "" )
			$columns = $this->getTableCols($table);

    	debugLog($values);
		debugLog($key);
		debugLog($keyValue);
		debugLog($columns);
    	$SQL = "UPDATE $table SET";
        foreach ( $values as $k => $v ){
			debugLog($k);
            if ( in_array($k,$columns) && $k != $key ){
            	debugLog("Add to SQL");
                $SQL .= " $k = '" . $this->escape($v) . "',";
            } else if ( $k == $key ){
				$keyValue = $v;
			}
        }
        //$SQL = substr($SQL,0,strlen($SQL)-1);
        //$SQLv = substr($SQLv,0,strlen($SQLv)-1);

        //$SQL .= ") values(" . $SQLv . ")";

        $SQL = rtrim($SQL,",");

        $SQL .= " WHERE $key = '" . $this->escape($keyValue) . "'";

		debugLog($SQL);
        $results = $this->query($SQL);
        if ( $results )
            return $keyValue;
        else
            return false;
    }

	function getTableCols($table,$include_ai=true){
		$retVal = array();
		$SQL = "SHOW FIELDS FROM $table";
		$results = $this->query($SQL);
		while ( $row = $this->fetchrow($results) ){
			if ( $include_ai == false && $row['Extra'] == "auto_increment" )
				continue;

			$retVal[] = $row['Field'];
		}

		return $retVal;
	}

	function escape($str){
		return mysqli_real_escape_string($this->_dbconn,$str);
	}

	function pQuery($table,$columns,$params){
		$p2 = array();

		if ( $columns == "" )
			$columns = $this->getTableCols($table);

		$SQL = "SELECT " . implode(",",$columns) . " FROM " . $table;
		foreach ( $params as $k => $v ){
			if ( is_array($v) ){
				$p2[] = $v[0] . " " . $k . " " . $v[1] . " '" . $this->escape($v[2]) . "'";
			} else {
				$p2[] = $k . " = '" . $this->escape($v) . "' ";
			}
		}
		$SQL .= " WHERE " . implode("AND ", $p2);
		return $this->query($SQL);
	}
}

?>