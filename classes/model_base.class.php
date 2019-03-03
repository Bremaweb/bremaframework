<?php

abstract class model_base {
	public $id;
	public $db;
	public $wDb;

	protected $loaded = false;
	protected $changed = false;
	protected $table = null;
	protected $key = null;
	protected $dbConnectionName = 'default';
	protected $data = array();

	private $tableDefinition;
	private $instanceGUID;
	private $lastError = null;

    /**
     * model_base constructor.
     * @param string $_key
     * @param bool $isGuid
     */
	public function __construct($_key="",$isGuid = false, $connectionName = null){
        $this->instanceGUID = md5($this->table . $_key);

		$this->db = dbConnection::getConnection(!empty($connectionName) ? $connectionName : $this->dbConnectionName);
        if ( dbConnectionsDef::hasMasterConnection() ){
		    $this->wDb = dbConnection::getConnection(dbConnectionDef::getMasterConnectionId());
        } else {
		    $this->wDb = $this->db;
        }

        if ( registry::get('_TIMEZONE') ){
            $this->db->query("SET time_zone = '" . registry::get('_TIMEZONE') . "'");
            $this->wDb->query("SET time_zone = '" . registry::get('_TIMEZONE') . "'");
        }

		if ( empty($this->table) ){
			$this->table = get_class($this);
		}

		$this->tableDefinition = new tableDefinition($this->table, $this->db);

		if ( empty($this->key) ){
			$this->key = $this->tableDefinition->getPrimaryKey();
		}

		if ( $_key != "" ){
			if ( !$this->load($_key,$isGuid) ){
			    throw new Exception('Unable to load new ' . get_class($this));
				return false;
			}
		}

		$this->afterConstruct($_key,$isGuid);
	}

    /**
     * @return bool
     */
	public function save(){

		$this->beforeSave();

		if ( !$this->validate() )
			return false;

		if ( $this->loaded == true ){
			$retVal = $this->wDb->updateRow($this->table, $this->data, $this->key, $this->id);
		} else {
			if ( $this->wDb->insertRow($this->table, $this->data) !== false ){
				$this->loaded = true;
            }
			//$this->data["{$this->key}"] = $this->db->lastid();
			$retVal = $this->wDb->lastid();
			$this->id = $retVal;
			$this->data["{$this->key}"] = $retVal;
		}

		$this->afterSave();

		$this->changed = false;
		return $retVal;
	}

    /**
     * @return mixed
     */
	public function delete(){
	    $query = "DELETE FROM {$this->table} WHERE {$this->key} = {$this->id}";
	    return $this->wDb->query($query);
    }

	protected function before_save(){}	// all functions with _ in their name are for backwards compatibility
	protected function beforeSave(){ $this->before_save(); }
	protected function after_save(){}
	protected function afterSave(){ $this->after_save(); }
	protected function after_load(){}
	protected function afterLoad(){ $this->after_load(); }
	protected function validate() { return true; }
	protected function afterConstruct($_key,$isGuid) { }

	public function load($keyValue,$isGuid = false){
		if ( ($this->data = $this->db->getRow($this->table, $this->getColumns(), ($isGuid == true ? "guid" : $this->key), $keyValue)) !== false ){
			$this->loaded=true;
			$this->id = $this->data[$this->key];
			$this->afterLoad();
            $this->instanceGUID = md5($this->table . $this->id);
			return true;
		} else {
			$this->loaded=false;
			return false;
		}
	}

	public function queryLoad($params){
		if ( ($this->data = $this->db->getRowQuery($this->table, $this->getColumns(), $params) ) ){
			$this->loaded=true;
			$this->id = $this->data["{$this->key}"];
			$this->afterLoad();
            $this->instanceGUID = md5($this->table . $this->id);
			return true;
		} else {
			$this->loaded=false;
			return false;
		}
	}

	public function __isset($name){
	    return isset($this->data[$name]);
    }

	public function __get($name){
		if ( isset($this->data[$name]) ){
			return $this->data[$name];
        } else {
			return isset($_SESSION[$this->instanceGUID][$name]) ? $_SESSION[$this->instanceGUID][$name] : false;
        }
	}

	public function __set($name,$val){
		if ( array_key_exists($name,$this->data) || in_array($name,$this->getColumns()) ){
			//debugLog("database field");
			$this->data[$name] = $val;
			$this->changed = true;
			//$this->save();
		} else {
			//debugLog("session field");
			$_SESSION[$this->instanceGUID][$name] = $val;
		}

		//debugLog($this->data);
	}

	public function getKey(){
		return $this->key;
	}

	public function getKeyValue(){
		return $this->data[$this->key];
	}

	public function getTable(){
		return $this->table;
	}

	public function getFormDetails(){
		return $this->form_details;
	}

	public function isLoaded(){
		return $this->loaded;
	}

	public function arraySet($ary){
		foreach ( $ary as $key => $val ){
			$this->$key = $val;
		}
	}

	function __destruct(){
		/*//debugLog("Destruct model_base");
		if ( $this->changed == true )
			$this->save();*/
	}

	private function element_name($field_name){
		if ( !empty($this->form_details["$field_name"]['name']) ){
			return $this->form_details["$field_name"]['name'];
		} else {
			if ( in_array($field_name,$this->getColumns()) )
				return $this->table . "[" . $field_name . "]";
			else
				return $field_name;
		}
	}

	private function element_attributes($field_name){
		$user = authentication::getUser();

		$form_details = $this->form_details;
		$attr = array();
		$default = !empty($form_details["$field_name"]['default']) ? $form_details["$field_name"]['default'] : '';
		$attr["value"] = ($this->$field_name != "" ? $this->$field_name : $default);
		if ( !empty($form_details["$field_name"]['attrib']) && is_array($form_details["$field_name"]['attrib']) ){
			foreach ( $form_details["$field_name"]['attrib'] as $k => $v ){
				if ( $k == "user_field" ){
					$k = "value";
					$v = $user->$v;
				}
				$attr["{$k}"] = $v;
			}
		}

		if ( empty($attr["class"]) )
			$attr["class"] = "form-control";

		return $attr;
	}

	public function form($ajax=1,$ajaxCallback="saveFormResponse",$action="ajax/saveForm",$view="SideBySide",$view_config=array(),$guid=false){
		$user = authentication::getUser();

		$hasButton = false;
		if ( $guid == false )
			$guid = GUID();

		$form = new PFBC\Form($guid);
		$view = "PFBC\\View\\" . $view;
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery"),
				"ajax" => $ajax,
				"ajaxCallback" => $ajaxCallback,
				"action" => SITE_URL . $action,
				"model" => get_class($this),
				"view" => new $view($view_config)
		));
		$form->addElement(new PFBC\Element\Hidden("guid",$guid));
		$form->addElement(new PFBC\Element\Hidden("id",$this->id,array("id"=>"id")));
		if ( is_array($this->form_details) ){
			foreach ( $this->form_details as $field_name => $attributes ){
				$el = "PFBC\\Element\\" . $attributes['type'];
				$default = !empty($attributes['default']) ? $attributes['default'] : '';
				switch ( $attributes['type'] ){
					case "Hidden":
						$form->addElement(new $el($this->element_name($field_name),($this->$field_name != "" ? $this->$field_name : $default)));
					break;
					case "Button":
						$hasButton = true;
						$form->addElement(new $el($attributes['label'],$attributes['button_type'],$this->element_attributes($field_name)));
					break;
					case "Select":
						$form->addElement(new $el($attributes['label'],$this->element_name($field_name),$attributes['options'],$this->element_attributes($field_name)));
					break;
					default:
						$form->addElement(new $el($attributes['label'],$this->element_name($field_name),$this->element_attributes($field_name)));
					break;
				}
			}
		}

		if ( $hasButton == false )
			$form->addElement(new PFBC\Element\Button);

		$form->render();
	}

	public function getColumns(){
		return $this->tableDefinition->getColumns();
	}

	public function getData(){
	    return $this->data;
    }

    public function getEnumValues($field){
	    return $this->tableDefinition->getEnumVals($field);
    }

    public function setError($error){
        $this->lastError = $error;
    }

    public function getError(){
        if ( !empty($this->lastError) ){
            return $this->lastError . " (" . get_class($this) . ")";
        } else {
            return $this->db->getError();
        }
    }

    public function NewQuery(){
        $q = new Query();
        $q->addTable($this->getTable());
        return $q;
    }
}

?>