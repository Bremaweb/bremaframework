<?php

class authentication {
	protected static $user;

	public static function initUser(){
		if ( session_status() !== PHP_SESSION_ACTIVE ){
			session_set_cookie_params( (86400 * 90) );
			session_start();
        }
		static::$user = new user(!empty($_SESSION['user_id']) ? $_SESSION['user_id'] : '');
	}

	public static function getUser(){
		return static::$user;
	}

	public static function getUserData(){
	    return static::$user->getData();
    }

	public static function getCurrentUserId(){
	    return static::$user->id;
    }

	public static function isLoggedIn(){
		return static::$user->logged_in;
	}

	public static function hasPermission($permName){
        return (( authentication::getUser()->user_permissions & permissions::get($permName) ) == permissions::get($permName));
	}

	public static function authenticate(){
		debugLog("authentication::authenticate",3);
		$slic = STAY_LOGGED_IN_COOKIE;
		if ( static::$user->logged_in == false && ( !isset($_COOKIE["{$slic}"]) || $_COOKIE["{$slic}"] != static::$user->getKeyValue() ) ){
			header("location: " . LOGIN_URL . "?redirect=" . $_SERVER['REQUEST_URI']);
			return false;
		} else {
			// go ahead and load the user
			if ( !static::$user->isLoaded() ){
				static::$user->load($_SESSION['user_id']);
			}

			if ( !empty($_SESSION['expires']) ){
				if ( $_SESSION['expires'] < time() && $_COOKIE["{$slic}"] != static::$user->getKeyValue() ){
					session_destroy();
					header("location: " . LOGIN_URL . "?redirect=" . $_SERVER['REQUEST_URI']);
					return false;
				}
			}

			$_SESSION['expires'] = ( time() + 86400 );
			return true;
		}
	}

	public static function login($username,$password){
		debugLog("login($username,$password)",3);
		$SQL = "SELECT user_id FROM users WHERE `" . static::$user->getUsernameField() . "` = '" . static::$user->db->escape( $username ) . "' AND " . ( static::$user->password_field != null ? static::$user->password_field : "user_password" ) . " = '" . md5($password) . "'";
		debugLog($SQL,3);
		$r = static::$user->db->query($SQL);
		if ( static::$user->db->numrows($r) > 0 ){
			$row = static::$user->db->fetchrow($r);
			static::$user->load($row['user_id']);
			if ( in_array("user_verified",static::$user->getColumns()) ){
				if ( static::$user->user_verified != 1 ){
					debugLog('not verified',2);
					$_SESSION['error'] = "Email address not verified";
					static::$user->logged_in = false;
					return false;
				}
			}
			$_SESSION['user_id'] = $row['user_id'];
			debugLog($_SESSION);
			static::$user->logged_in = true;
			return true;
		} else {
			static::$user->logged_in = false;
			return false;
		}
	}

	public static function logout(){
		static::$user->logged_in = false;

		session_destroy();
		unset($_SESSION);
		$_SESSION = array();
	}
}