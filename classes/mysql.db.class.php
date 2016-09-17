<?php

class db {

    private $dbconn = "";
    private $result = "";

    function __construct($host, $port, $database, $user, $password){	// original parameters ($dbHost,$dbPort,$dbDatabase,$dbUser,$dbPassword)
    	    $this->dbconn=mysql_connect($host,$user,$password);
			if ( $this->dbconn )
				mysql_select_db($database);
			else
				die("Unable to connect to database..." . mysql_error());
    }

    function query($SQL, $displayErrors = true){
    	//debugLog("query($SQL)");
        $this->result = @mysql_query($SQL, $this->dbconn);

        if ($displayErrors && !$this->result){
        	//debugLog(mysql_error());
            //echo "SQL Error: " . mysql_error() . "<br><br>" . $SQL . "<br><br>";
        }
		if ( !$this->result ){
			$errFile = fopen(APP_DIR . "/logs/error_" . date("m-d-Y") . ".txt","a+");
				fwrite($errFile,"[" . date("m/d/Y h:ia") . "] SQL Error: " . mysql_error() . "\r\n" . $SQL . "\r\n\r\n");
				fwrite($errFile,debug_backtrace());
			fclose($errFile);
		}
		//debugLog($this->result);
        return $this->result;
    }

    function queryrow($SQL, $displayErrors = true){
        // queryrow does the query and just returns one row
        $result = $this->query($SQL, $displayErrors);
            if ($result){
                return $this->fetchrow($result);
                @mysql_free_result($result);
            }else{
                return false;
            }
    }

    function fetchrow($result = 0){

        if (!$result)
            $result = $this->result;

        $row = @mysql_fetch_assoc($result);
        return $row;
    }

    function lastid(){
        return mysql_insert_id();
    }

    function numrows($result=0){
        if (!$result)
            $result = $this->result;

        return @mysql_num_rows($result);
    }

	function affectedrows(){

        return @mysql_affected_rows();
    }

    function __destruct(){
        mysql_close($this->dbconn);
    }

	function getRows($SQL,$optionArray=false){
		$results = $this->query($SQL);

		if ( !$results )
			return false;

		$returnRows = array();
		while ( $row = $this->fetchrow($results) ){
			if ( $optionArray )
				$returnRows[$row[0]] = $row[1];
			else
				$returnRows[] = $row;
		}

		return $returnRows;
	}

    function insertRow($table, $values){
    	//debugLog("insertRow($table,$values);");
    	//debugLog($values);
    	$columns = $this->getTableCols($table);
    	//debugLog($values);
        $SQL = "INSERT INTO $table (";
        $SQLv = "";
        foreach ( $values as $k => $v ){
            if ( in_array($k,$columns) ){
                $SQLv .= "'" . mysql_real_escape_string($v) . "',";
                $SQL .= "$k,";
            }
        }
        $SQL = substr($SQL,0,strlen($SQL)-1);
        $SQLv = substr($SQLv,0,strlen($SQLv)-1);

        $SQL .= ") values(" . $SQLv . ")";

        $SQL = substr($SQL,0,strlen($SQL)-1);

        $SQL .= ")";

        //debugLog($SQL);

        $results = $this->query($SQL);
        if ( $results )
            return mysql_insert_id();
        else
            return false;
    }

	function getRow ($table, $columns, $key, $keyValue ){
		$SQL = "SELECT " . implode(",",$columns) . " FROM " . $table . " WHERE " . $key . " = '" . $keyValue . "'";
		debugLog($SQL);
		return $this->queryrow($SQL);
	}

	function getRowQuery ($table, $columns, $params){
		$p2 = array();
		$SQL = "SELECT " . implode(",",$columns) . " FROM " . $table;
		foreach ( $params as $k => $v )
			$p2[] = $k . " = '" . mysql_real_escape_string($v) . "' ";
		$SQL .= " WHERE " . implode("AND ", $p2);
		return $this->queryrow($SQL);
	}

    function updateRow($table,$values,$key,$keyValue="",$columns = ""){
		//debugLog("updateRow();");
    	//debugLog($table);

		if ( $columns == "" )
			$columns = $this->getTableCols($table);

    	//debugLog($values);
		//debugLog($key);
		//debugLog($keyValue);
		//debugLog($columns);
    	$SQL = "UPDATE $table SET";
        foreach ( $values as $k => $v ){
			//debugLog($k);
            if ( in_array($k,$columns) && $k != $key ){
            	//debugLog("Add to SQL");
                $SQL .= " $k = '" . mysql_real_escape_string($v) . "',";
            } else if ( $k == $key ){
				$keyValue = $v;
			}
        }
        //$SQL = substr($SQL,0,strlen($SQL)-1);
        //$SQLv = substr($SQLv,0,strlen($SQLv)-1);

        //$SQL .= ") values(" . $SQLv . ")";

        $SQL = rtrim($SQL,",");

        $SQL .= " WHERE $key = '" . mysql_real_escape_string($keyValue) . "'";

		//debugLog($SQL);
        $results = $this->query($SQL);
        if ( $results )
            return $keyValue;
        else
            return false;
    }

	function getTableCols($table){
		$retVal = array();
		$SQL = "SHOW FIELDS FROM $table";
		$results = $this->query($SQL);
		while ( $row = $this->fetchrow($results) ){
			$retVal[] = $row['Field'];
		}

		return $retVal;
	}

	function escape($str){
		return mysql_real_escape_string($str);
	}
}

?>