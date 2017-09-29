<?php

abstract class user_base extends model_base {
	protected $username_field = "user_name";
	protected $password_field = null;

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

	function getUsernameField(){
		return $this->username_field;
	}

	function loggedIn(){
		return $this->logged_in;
	}

}

?>