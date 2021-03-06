<?php

class Query {
    private $db = null;
    
    private $tables = array();
    private $fields = array();
    private $params = array();
    private $limit = '';
    private $order = '';
    
    private $_query = "";

    public function __construct($db = null, $table = null){
        if ( $db == null ){
            $this->db = dbConnection::getConnection('default');
        } else {
            $this->db = $db;
        }

        if ( !empty($table) ){
            $this->addTable($table);
        }
    }

    public function __toString(){
        return $this->GetQuery();
    }
    
    public function getQuery(){
        return $this->RenderQuery();   
    }
    
    private function renderQuery(){
        $this->_query  = $this->RenderFields();
        $this->_query .= $this->RenderTables();
        if ( !empty($this->params) ){
            $this->_query .= " WHERE ";
            $this->_query .= $this->RenderParams($this->params);
        }
        $this->_query .= $this->RenderOrder();
        $this->_query .= $this->RenderLimit();

        return $this->_query;
    }

    private function renderFields(){
        if ( !empty($this->fields) ){
            if ( is_array($this->fields) ){
                return 'SELECT ' . implode(',', array_map(function($f){ return strpos($f,'!') === 0 ? substr($f,1) : $this->db->escapeField($f); },$this->fields));
            } else {
                return 'SELECT ' . $this->db->escapeField($this->fields);
            }
        } else {
            return 'SELECT *';
        }
    }
    
    private function renderTables(){
        $tables = " FROM ";
        $i = 0;
        foreach ( $this->tables as $table ){
            if ( $i > 0 ){
                // joins
                if ( is_array($table) ){
                    if ( !empty($table['params'] && !empty($table['join']) ) ){
                        $joinType = !empty($table['join']) ? $table['join'] : 'LEFT';
                        $tables .= " {$joinType} JOIN " . $table['name'];
                        $tables .= " ON " . $this->renderParams($table['params']);
                    } else {
                        $tables .= ", " . $table['name'];
                    }
                } else {
                    $tables .= ", " . $table;
                }
            } else {
                if ( is_array($table) ){
                    $tables .= $table['name'];
                } else {
                    $tables .= $table;
                }
            }
            $i++;
        }

        return $tables;
    }
    
    private function renderParams($params){

        $paramString = '';
        $format = "%s %s %s %s";

        $i = 0;
        foreach ( $params as $field => $value ){
            if ( !empty($value['or']) ){
                $i++;
                $e = 0;
                $paramString .= " ( ";
                foreach ( $value['or'] as $orFields ){
                    foreach ( $orFields as $f => $v ){
                        list($o, $v) = $this->getOperatorAndValue($v);
                        $c = $e > 0 ? " OR " : "";
                        $eField = strpos($f, '!') === 0 ? substr($f,1) : $this->db->escapeField($f);
                        $paramString .= sprintf($format, $c, $eField, $o, $v);
                    }
                    $e++;
                }
                $paramString .= " ) ";
            } else {
                list($operator, $value) = $this->getOperatorAndValue($value);
                $a = $i > 0 ? " AND " : "";
                $eField = strpos($field, '!') === 0 ? substr($field,1) : $this->db->escapeField($field);
                $paramString .= sprintf($format, $a, $eField, $operator, $value);
                $i++;
            }
        }

        return $paramString;
    }

    private function getOperatorAndValue($v){
        $operator = '=';
        $value = $v;

        if ( is_array($v) ){
            if ( !empty($v['operator']) ){
                $operator = $v['operator'];
                $value = $v['value'];
            } else {
                $operator = $v[0];
                $value = $v[1];
            }
        }

        switch ( strtolower($operator) ){
            case "in":
            case "not in":
                if ( is_array($value) ){
                    $value = "(" . implode(",", array_map(function($x) { return "'" . $this->db->escape($x) . "'"; }, $value)) . ")";
                } else {
                    $value = "(" . $value . ")";
                }
                break;
            case "between":
            case "not between":
                    $value = implode(" AND ", array_map(function($x) { return stripos($x, '@') === 0 ? $this->db->escapeField(ltrim($x,'@')) : "'" . $this->db->escape($x) . "'"; }, $value));
                break;
            default:
                $value = stripos($value, "@") === 0 ? ltrim($value, '@') : "'" . $this->db->escape($value) . "'";
                break;
        }

        return array($operator, $value);
    }

    private function renderOrder(){
        if ( !empty($this->order) ){
            return " ORDER BY " . $this->db->escape($this->order);
        }
        return '';
    }

    private function renderLimit(){
        if ( !empty($this->limit) && is_numeric($this->limit) ){
            return " LIMIT " . $this->limit;
        }
        return '';
    }
    
    public function setFields($fields){
       $this->fields = $fields;
    }
    
    public function addTable($table){
        $this->tables[] = $table;
    }

    public function setParams($params){
        $this->params = $params;
    }

    public function setOrder($order){
        $this->order = $order;
    }
    
    public function setLimit($limit){
        $this->limit = $limit;
    }


}