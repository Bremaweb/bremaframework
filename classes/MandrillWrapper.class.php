<?php
require_once("Mandrill.php");

class MandrillWrapper {
	private $_mandrill;
	private $message = array();
	private $validFields = array("Subject"=>"subject");

	private $data = array("Host"=>"1","Username"=>"1","Password"=>"1","Port"=>"1","SMTPSecure"=>"1");

	function __construct ( $api_key ){
		$this->_mandrill = new Mandrill($api_key);
	}

	function setFrom($address, $name){
		$this->message['from_email'] = $address;
		$this->message['from_name'] = $name;
	}

	function AddReplyTo($address){
		if ( !is_array($this->message['header']) )
			$this->message['header'] = array();

		$this->message['header']['Reply-To'] = $address;
	}

	function AddCC($address){
		$this->AddAddress($address,"cc");
	}

	function AddBcc($address){
		$this->AddAddress($address,"bcc");
	}

	function AddAddress($address,$type="to"){
		if ( !is_array($this->message['to']) )
			$this->message['to'] = array();

		$this->message['to'][] = array("email"=>$address,"type"=>$type);
	}

	function MsgHTML($html){
		$this->message['html'] = $html;
	}

	function AddAttachment($file){
		$filename = basename($file);
		$type = mime_content_type($file);
		$content = base64_encode(file_get_contents($file));
		if ( !is_array($this->message['attachments']) )
			$this->message['attachments'] = array();

		$this->message['attachments'][] = array("name"=>$filename,"type"=>$type,"content"=>$content);
	}

	function Send(){
		try {
			$async = false;
			$result = $this->_mandrill->messages->send($this->message, $async);
		} catch ( Mandrill_Error $e ){
			debugPrint("Mandrill Error: " . $e->getMessage());
			throw new Exception($e->getMessage());
		}
	}

	function Host($dummy){}	// dummy function
	function Port($dummy){} // dummy function
	function ClearAllRecipients() {} // dummy function

	function __get($name){
		if ( isset($this->message["{$name}"]) )
			return $this->message["{$name}"];
		else
			return $this->data["{$name}"];
	}

	function __set($name,$value){
		if ( $this->validFields["$name"] != "" ){
			$mandrillField = $this->validFields["$name"];
			$this->message["$mandrillField"] = $value;
		} else {
			$this->data["{$name}"] = $value;
		}
	}
}