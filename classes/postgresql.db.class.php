<?php

class db {

	private $dbconn = "";
	private $result = "";
	var $queryCount = 0;
	var $lastError = "";

	private $dbHost;
	private $dbPort;
	private $dbDatabase;

	function __construct($dbHost="",$dbPort="",$dbDatabase="",$dbUser="",$dbPassword=""){
		if ( $dbHost && $dbPort && $dbDatabase && $dbUser && $dbPassword )
			$this->open($dbHost,$dbPort,$dbDatabase,$dbUser,$dbPassword) || die( "Open failed: " . pg_last_error() );
    }

	function escape($string){
		return pg_escape_string($this->dbconn,$string);
	}

	function query($SQL, $displayErrors = true){
		$this->queryCount++;

		$this->result = @pg_query($this->dbconn,$SQL);

		if ( !$this->result ){
			if ($displayErrors && MUTE_DB_ERRORS !== true ){
				echo "<div style=\"color: #FF0000; font-weight: bold;\">Database Error: " . @pg_last_error() . "</div><br>" . $SQL . "<br />";
			}
			return false;
		}

		return $this->result;
	}

	function queryrow($SQL, $displayErrors = true, $return_array = false){
		// queryrow does the query and returns one row instead of a result set
		$result = $this->query($SQL, $displayErrors);
			if ($result){
				if ( $return_array )
					return $this->fetcharray($result);
				else
					return $this->fetchrow($result);
				pg_free_result($result);
			}else{
				return false;
			}
	}

	function fetchrow($result = 0){

		if (!$result)
			$result = $this->result;

		$row = @pg_fetch_assoc($result);
		return $row;
	}

	function fetcharray($result = 0 ){
		if ( !$result )
			$result = $this->result;

		$row = @pg_fetch_row($result);
		return $row;
	}

	function lastid($result = 0){
		if (!$result)
			$result = $this->result;

		$SQL = "SELECT lastval() as lv";
			$result = @pg_query($SQL);
			if (!$result){
				$retval = NULL;
			} else {
				$row = @pg_fetch_assoc($result);
				$retval = $row['lv'];
			}

		return $retval;
	}

	function numrows($result=0){
		if (!$result)
			$result = $this->result;

		return @pg_num_rows($result);
	}

	function rowsaffected($results=0){
		if ( !$results )
			$result = $this->result;

		return @pg_affected_rows($results);
	}

	function affectedrows($results=0){	// alias because I keep transposing this function name
		return $this->rowsaffected($results);
	}

	function close(){
		@pg_close($this->dbconn);
	}

	function open($dbHost,$dbPort,$dbDatabase,$dbUser,$dbPassword){
		$connString = "host=$dbHost port=$dbPort dbname=$dbDatabase user=$dbUser password=$dbPassword connect_timeout=15";
		$this->dbconn=pg_connect($connString);

		if ( !$this->dbconn ){
			//debugPrint("Unable to connect to $dbDatabase on $dbHost:$dbPort\r\n" . @pg_last_error());
			//echo pg_last_error();
			return false;
		}

		$this->dbHost = $dbHost;
		$this->dbPort = $dbPort;
		$this->dbDatabase = $dbDatabase;
		return true;
	}

	function isConnected(){
		return $this->dbconn;
	}

	function nextval($seq){
		$SQL = "SELECT nextval('$seq') as nv";
		$row = $this->queryrow($SQL);

		return $row['nv'];
	}

	function maxval($col,$table,$where=""){
		$SQL = "SELECT max($col) as mx FROM $table " . $where;
		$row = $this->queryrow($SQL);

		return $row['mx'];
	}

	function rowseek($result,$row){
		pg_result_seek($result,$row);
	}
	function __destructor(){
		@pg_close($this->dbconn);
	}

	function insertRow($table, $values){
    	debugPrint("insertRow();");
    	debugPrint($table);
    	$columns = $this->getTableCols($table);
    	debugPrint($values);

    	// get column data types so we can properly format our SQL and ignore bad values
    	$fields = $this->getColumnTypes($table);
		//debugPrint("Retruend fields");
		debugPrint($fields);
        $SQL = "INSERT INTO $table (";
        $SQLv = "";
        foreach ( $values as $k => $v ){
            if ( in_array($k,$columns) ){
            	debugPrint($k . ": " . $fields["$k"]);
            	switch ( $fields["$k"] ){
            		case "integer":
            		case "smallint":
            		case "double precision":
            		case "real":
            		case "bigint":
            		case "numeric":
							$SQLv .= ( $v == "" ? "0" : $v ) . ",";
							$SQL .= $k . ",";
            		break;
            		case "boolean":
            			$SQLv .= ( $v ? "'t'," : "'f',");
            			$SQL .= $k . ",";
            		break;
            		case "date":
            			if ( $v != "" ){
							if ( is_numeric($v) ){
								$SQLv .= "'" . date(DATE_FORMAT,$v) . "',";
								$SQL .= $k . ",";
							} else {
								$SQLv .= "'" . $v . "',";
								$SQL .= $k . ",";
							}
            			}
            		break;

            		default:
                		$SQLv .= "'" . $this->escape($v) . "',";
                		$SQL .= "$k,";
                	break;
            	}
            }
        }
        $SQL = substr($SQL,0,strlen($SQL)-1);
        $SQLv = substr($SQLv,0,strlen($SQLv)-1);

        $SQL .= ") values(" . $SQLv . ")";

        $SQL = substr($SQL,0,strlen($SQL)-1);

        $SQL .= ")";

        //debugPrint($SQL);

        $results = $this->query($SQL,true,true,$caseSensitive);
        if ( $results )
            return $this->lastid();
        else
            return false;
    }

    function getColumnTypes($table){
    	//debugPrint("getColumnTypes($table)");
    	$SQL = "select column_name, data_type from information_schema.columns where table_name = '" . $table . "'";
    	//debugPrint($SQL);
    	$tresults = $this->query($SQL,true,true,true);
    	$tfields = array();
    	//debugPrint($tresults);
    	while ( $trow = $this->fetchrow($tresults) ){
    	//debugPrint("L: " .$trow['column_name'] . ": " . $trow['data_type']);
    		$tfields["{$trow['column_name']}"] = $trow['data_type'];
    	}
    	return $tfields;
    }

    function updateRow($table,$values,$key,$keyValue,$caseSensitive=false,$skipBlank=false){
		//debugPrint("updateRow();");
    	//debugPrint($table);
    	$columns = $this->getTableCols($table);
    	$fields = $this->getColumnTypes($table);
    	//debugPrint($values);
		//debugPrint($key);
		//debugPrint($keyValue);
    	$SQL = "UPDATE $table SET ";
        foreach ( $values as $k => $v ){

        	if ( $v == "" && $skipBlank === true )
        		continue;

            if ( in_array($k,$columns) ){
            	switch ( $fields["$k"] ){
            		case "integer":
            		case "smallint":
            		case "double precision":
            		case "real":
            		case "bigint":
            		case "numeric":
            			$SQL .= $k . "=" . ( $v == "" ? "0" : $v ) . ",";

            			break;
            		case "boolean":
            			$SQL .= $k . "=" . ( $v ? "'t'," : "'f',");
            			break;
            		case "date":
            			if ( $v != "" ){
            				if ( is_numeric($v) ){
            					$SQL .= $k . "=" . "'" . date(DATE_FORMAT,$v) . "',";
            				} else {
            					$SQL .= $k . "=" . "'" . $v . "',";
            				}
            			}
            			break;

            		default:
            			$SQL .= $k . "=" . "'" . $this->escape($v) . "',";
            			break;
            	}
            }
        }

        $SQL = rtrim($SQL,",");

        $SQL .= " WHERE $key = '" . $this->escape($keyValue) . "'";

		//debugPrint($SQL);
        $results = $this->query($SQL,true,true,$caseSensitive);
        if ( $results )
            return true;
        else
            return false;
    }

	function getTableCols($table){
		//debugPrint("getTableCols($table)");
		$retVal = array();
		$SQL = "SELECT column_name FROM information_schema.columns WHERE table_name ='" . $table . "';";
		$results = $this->query($SQL,true,true,true);
		while ( $row = $this->fetchrow($results) ){
			//debugPrint($row);
			$retVal[] = $row['column_name'];
		}
		//debugPrint($retVal);
		return $retVal;
	}


}

?>