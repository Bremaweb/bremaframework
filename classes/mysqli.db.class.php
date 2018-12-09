<?php

class db {

    private $result = "";
	private $_dbconn;
	private $lastError = null;

    /**
     * db constructor.
     * @param $host
     * @param $port
     * @param $database
     * @param $user
     * @param $password
     */
    function __construct($host, $port, $database, $user, $password){	// original parameters ($dbHost,$dbPort,$dbDatabase,$dbUser,$dbPassword)
    	    $this->_dbconn = mysqli_connect($host,$user,$password,$database,$port);
			if ( !$this->_dbconn ){
				die("Unable to connect to database..." . mysqli_error($this->_dbconn));
            }
    }

    function getError(){
        return $this->lastError . " (" . get_class($this) . ")";
    }

    /**
     * @param $SQL
     * @param bool $displayErrors
     * @return bool|mysqli_result|string
     */
    function query($SQL, $displayErrors = true){
        $this->result = @mysqli_query($this->_dbconn, $SQL);

        if (!$this->result){
            $this->lastError = mysqli_error($this->_dbconn) . " - " . $SQL;
        }

        return $this->result;
    }

    /**
     * @param $SQL
     * @param bool $displayErrors
     * @return array|bool|null
     */
    function queryrow($SQL, $displayErrors = true){
        // queryrow does the query and just returns one row
        $result = $this->query($SQL, $displayErrors);
            if ($result){
                return $this->fetchrow($result);
                @mysqli_free_result($result);
            }else{
                $this->lastError = mysqli_error($this->_dbconn) . " - " . $SQL;
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
        return @mysqli_affected_rows($this->_dbconn);
    }

    function __destruct(){
        mysqli_close($this->_dbconn);
    }

    /**
     * @param $SQL
     * @param bool $optionArray
     * @return array|bool
     */
	function getRows($SQL,$optionArray=false){
		$results = $this->query($SQL);

		if ( !$results ){
            $this->lastError = mysqli_error($this->_dbconn) . " - " . $SQL;
			return false;
        }

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
    	$columns = $this->getTableCols($table,false);
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

        $results = $this->query($SQL);
        if ( $results ){
            return mysqli_insert_id();
        } else {
            $this->lastError = mysqli_error($this->_dbconn);
            return false;
        }
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

		if ( $columns == "" )
			$columns = $this->getTableCols($table);


    	$SQL = "UPDATE $table SET";
        foreach ( $values as $k => $v ){
            if ( in_array($k,$columns) && $k != $key ){
                $SQL .= " $k = '" . $this->escape($v) . "',";
            } else if ( $k == $key ){
				$keyValue = $v;
			}
        }

        $SQL = rtrim($SQL,",");

        $SQL .= " WHERE $key = '" . $this->escape($keyValue) . "'";

        $results = $this->query($SQL);
        if ( $results ){
            return $keyValue;
        } else {
            $this->lastError = mysqli_error($this->_dbconn);
            return false;
        }
    }

	function getTableCols($table,$include_ai=true){
        if ( false === $retVal = cache::get('getTableCols::' . $table) ){
            $retVal = array();
            $SQL = "SHOW FIELDS FROM $table";
            $results = $this->query($SQL);
            while ( $row = $this->fetchrow($results) ){
                if ( $include_ai == false && $row['Extra'] == "auto_increment" )
                    continue;

                $retVal[] = $row['Field'];
            }

            cache::set('getTableCols::' . $table, $retVal);
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

	public function seek($offset){
	    mysqli_data_seek($this->result, $offset);
    }

    public function escapeField($field){
	    $field = str_replace('`','',$field);
	    $parts = explode(' as ', strtolower($field));
        $eField = '`' . implode('`.`', explode('.', trim($parts[0]))) . '`';
        if ( !empty($parts[1]) ){
            $eField .= ' AS ' . $this->escapeField(trim($parts[1]));
        }
        return $eField;
    }
}

?>