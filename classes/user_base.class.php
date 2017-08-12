<?php

class user_base extends model_base {
	protected $table = "users";
	protected $columns = array("user_id",
							"user_email",
							"user_password",
							"dt_created",
							"user_name",
							"user_verified",
							"user_verifycode"
	);

	protected $key = "user_id";
	protected $username_field = "user_name";
	protected $password_field = null;

	private $tableDefinition = null;

	public $data = array();

	function __construct($require_login=false,$user_id=""){
		debugLog(get_class($this) . "->__construct($require_login,$user_id)",3);

		$this->db = dbConnection::getConnection();

		$this->tableDefinition = new tableDefinition($this->table, $this->db);

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
			if ( $require_login == false && !empty($_SESSION['user_id']) )
				$this->load($_SESSION['user_id']);
		}
	}

	public function authenticate($required = true){
		debugLog(get_class($this) . "->authenticate($required)",3);
		if ( $required == true ){
			$slic = STAY_LOGGED_IN_COOKIE;
			if ( $this->logged_in == false && ( !isset($_COOKIE["{$slic}"]) || $_COOKIE["{$slic}"] != $this->getKeyValue() ) ){
				header("location: " . LOGIN_URL . "?redirect=" . $_SERVER['REQUEST_URI']);
				return false;
			} else {
				// go ahead and load the user
				$this->load($_SESSION['user_id']);

				if ( isset($_SESSION['expires']) ){
					if ( $_SESSION['expires'] < time() && $_COOKIE["{$slic}"] != $this->getKeyValue() ){
						session_destroy();
						header("location: " . LOGIN_URL . "?redirect=" . $_SERVER['REQUEST_URI']);
						return false;
					}
				}

				$_SESSION['expires'] = ( time() + 86400 );
				return true;
			}
		}
	}

	function login($username,$password){
		debugLog(get_class($this) . "->login($username,$password)",3);
		$SQL = "SELECT user_id FROM users WHERE `" . $this->username_field . "` = '" . $this->db->escape( $username ) . "' AND " . ( $this->password_field != null ? $this->password_field : "user_password" ) . " = '" . md5($password) . "'";
		$r = $this->db->query($SQL);
		if ( $this->db->numrows($r) > 0 ){
			$row = $this->db->fetchrow($r);
			$this->load($row['user_id']);
			if ( in_array("user_verified",$this->columns) ){
				if ( $this->user_verified != 1 ){
					$_SESSION['error'] = "Email address not verified";
					$this->logged_in = false;
					return false;
				}
			}
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

	static function verify($verifyCode){
		global $db;
		$SQL = "SELECT user_id FROM users WHERE user_verifycode = '" . $db->escape($verifyCode) . "'";
		$r = $db->query($SQL);
		if ( $db->numrows($r) > 0 ){

			$row = $db->fetchrow($r);
			$u = new user($row['user_id']);
			$u->load($row['user_id']);
			$u->user_verifycode = '';
			$u->user_verified='1';

			return $u->save();

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

	function loggedIn(){
		return $this->logged_in;
	}
}

?>