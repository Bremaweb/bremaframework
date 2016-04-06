<?php

class user_base extends model_base {
	protected $table = "users";
	protected $columns = array("user_id",
							"user_email",
							"user_password",
							"dt_created",
							"user_name");

	protected $key = "user_id";
	protected $username_field = "user_name";
	protected $password_field = null;

	public $data = array();

	function __construct($require_login=false,$user_id=""){
		global $db;
		$this->db = $db;

		if ( session_status() !== PHP_SESSION_ACTIVE ){
			session_set_cookie_params( (86400 * 90) );
			session_start();
			//unset($_SESSION['error']);
		}

		if ( $require_login === true ){
			$this->authenticate();
		}

		if ( $user_id != "" ){
			$this->load($user_id);
		} else {
			if ( $require_login == false && $_SESSION['user_id'] != "" )
				$this->load($_SESSION['user_id']);
		}
	}

	public function authenticate($required = true){
		if ( $required == true ){
			$slic = STAY_LOGGED_IN_COOKIE;
			if ( $this->logged_in == false && ( !isset($_COOKIE["{$slic}"]) || $_COOKIE["{$slic}"] != $this->user_key ) ){
				header("location: " . LOGIN_URL . "?redirect=" . $_SERVER['REQUEST_URI']);
				exit;
			} else {
				// go ahead and load the user
				if ( $user_id == "" )
					$this->load($_SESSION['user_id']);
				else
					$this->load($user_id);

				if ( isset($_SESSION['expires']) ){
					if ( $_SESSION['expires'] < time() && $_COOKIE["{$slic}"] != $this->user_key ){
						session_destroy();
						header("location: " . LOGIN_URL . "?redirect=" . $_SERVER['REQUEST_URI']);
						exit;
					}
				}

				$_SESSION['expires'] = ( time() + 3600 );

			}
		}
	}

	function login($username,$password){
		global $db;
		debugLog($password);
		$SQL = "SELECT user_id FROM users WHERE UPPER(" . $this->username_field . ") = '" . $db->escape( strtoupper($username) ) . "' AND user_password = '" . md5($password) . "'";
		$r = $db->query($SQL);
		if ( $db->numrows($r) > 0 ){
			$row = $db->fetchrow($r);
			$this->load($row['user_id']);

			$_SESSION['user_id'] = $row['user_id'];

			$this->logged_in = true;
			return true;

		} else {
			$this->logged_in = false;
			return false;
		}
	}

	function logout(){
		$this->logged_in = false;

		if ( method_exists($this,"afterLogout") )
			$this->afterLogout();

		session_destroy();
		unset($_SESSION);
		$_SESSION = array();
		$_SESSION['error'] = "You have been logged out.";
		header("location: " . SITE_URL);
		exit;
	}

	function verify($verifyCode,$email){
		global $db;
		$SQL = "SELECT user_id FROM users WHERE user_verifycode = '" . $verifyCode . "' AND user_email='" . $email . "'";
		$r = $db->query($SQL);
		if ( $db->numrows($r) > 0 ){

			$row = $db->fetchrow($r);
			$this->load($row['user_id']);
			$this->user_verifycode = '';
			$this->user_verified='1';

			return $this->save();

		} else {
			return false;
		}
	}

	function destruct(){
		unset($_SESSION['error']);
	}
	
	function getPasswordField(){
		return $this->password_field;
	}

}

?>