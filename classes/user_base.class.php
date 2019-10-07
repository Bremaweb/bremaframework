<?php

abstract class user_base extends model_base {
	protected $username_field = "user_name";
	protected $password_field = null;

	public function __construct($_key = '', $isGuid = false){
	    parent::__construct($_key, $isGuid);
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

	public function destruct(){
		unset($_SESSION['error']);
	}

	public function getPasswordField(){
		return $this->password_field;
	}

	public function getUsernameField(){
		return $this->username_field;
	}

	public function loggedIn(){
		return $this->logged_in;
	}

	public function hashPassword($password){
	    return md5($password);
    }

    public function getPermissions(){
	    return $this->user_permissions;
    }

    public function hasPermission($permName){
        return (( $this->getPermissions() & permissions::get($permName) ) == permissions::get($permName));
    }

    public function getTimezone(){
        return !empty($this->timezone) ? $this->timezone : date_default_timezone_get();
    }
}

?>