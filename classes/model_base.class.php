<?php

class model_base {
	public $id;

	protected $loaded = false;
	protected $changed = false;
	protected $table = "";
	protected $columns = "";
	protected $key = "";
	protected $db;

	private $data = array();

	function __construct($_key="",$isGuid = false){
		debugLog("__construct($_key,$isGuid)");
		global $db;
		$this->db = $db;

		if ( $_key != "" ){
			if ( !$this->load($_key,$isGuid) ){
				debugLog("Unable to load " . get_class($this) . " key " . $_key);
				return false;
			}
		}
		$this->afterConstruct($_key,$isGuid);
	}

	public function save(){
		//debugLog("save()");

		$this->beforeSave();

		if ( !$this->validate() )
			return false;

		if ( $this->loaded == true ){
			$retVal = $this->db->updateRow($this->table, $this->data, $this->key, $this->id, $this->columns);
		} else {
			if ( $this->db->insertRow($this->table, $this->data) !== false )
				$this->loaded = true;
			//$this->data["{$this->key}"] = $this->db->lastid();
			$retVal = $this->db->lastid();
			$this->id = $retVal;
			$this->data["{$this->key}"] = $retVal;
		}

		$this->afterSave();

		$this->changed = false;

		return $retVal;
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
		debugLog("loading from " . $this->table . " " . $this->key . " " . $keyValue);
		if ( ($this->data = $this->db->getRow($this->table, $this->columns, ($isGuid == true ? "guid" : $this->key), $keyValue)) !== false ){
			$this->loaded=true;
			$this->id = $this->data["{$this->key}"];
			$this->afterLoad();
			return true;
		} else {
			$this->loaded=false;
			return false;
		}
	}

	public function queryLoad($params){
		if ( ($this->data = $this->db->getRowQuery($this->table, $this->columns, $params) ) ){
			$this->loaded=true;
			$this->id = $this->data["{$this->key}"];
			$this->afterLoad();
			return true;
		} else {
			$this->loaded=false;
			return false;
		}
	}

	public function __get($name){
		if ( array_key_exists($name,$this->data) || in_array($name,$this->columns) )
			return $this->data["$name"];
		else
			return $_SESSION["{$this->table}"]["$name"];
	}

	public function __set($name,$val){
		//debugLog("__set($name,$val)");
		if ( array_key_exists($name,$this->data) || in_array($name,$this->columns) ){
			//debugLog("database field");
			$this->data["$name"] = $val;
			$this->changed = true;
			//$this->save();
		} else {
			//debugLog("session field");
			$_SESSION["{$this->table}"]["$name"] = $val;
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
		if ( $this->form_details["$field_name"]['name'] != "" ){
			return $this->form_details["$field_name"]['name'];
		} else {
			if ( in_array($field_name,$this->columns) )
				return $this->table . "[" . $field_name . "]";
			else
				return $field_name;
		}
	}

	private function element_attributes($field_name){
		global $user;
		$form_details = $this->form_details;
		$attr = array();
		$attr["value"] = ($this->$field_name != "" ? $this->$field_name : $form_details["$field_name"]['default']);
		if ( is_array($form_details["$field_name"]['attrib']) ){
			foreach ( $form_details["$field_name"]['attrib'] as $k => $v ){
				if ( $k == "user_field" ){
					$k = "value";
					$v = $user->$v;
				}
				$attr["{$k}"] = $v;
			}
		}

		if ( $attr["class"] == "" )
			$attr["class"] = "form-control";

		return $attr;
	}

	public function form($ajax=1,$ajaxCallback="saveFormResponse",$action="ajax/saveForm",$view="SideBySide",$view_config=array(),$guid=false){
		global $user;
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
				switch ( $attributes['type'] ){
					case "Hidden":
						$form->addElement(new $el($this->element_name($field_name),($this->$field_name != "" ? $this->$field_name : $attributes['default'])));
					break;
					case "Button":
						$hasButton = true;
						$form->addElement(new $el($attributes['label'],$attributes['button_type'],$this->element_attributes($field_name)));
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
}

?>