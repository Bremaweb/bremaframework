<?php

class authentication {
	protected static $user;

	public static function initUser(){
		if ( session_status() !== PHP_SESSION_ACTIVE ){
		    if ( !file_exists(APP_DIR . 'cache/session') ){
		        mkdir(APP_DIR . 'cache/session');
            }
            session_save_path(APP_DIR . 'cache/session');
			session_set_cookie_params( (86400 * 90) );
			session_start();
        }
        if ( empty($_SESSION['user_id']) && !empty($_COOKIE[STAY_LOGGED_IN_COOKIE]) ){
		    static::$user = new user($_COOKIE[STAY_LOGGED_IN_COOKIE], true);
        } else {
		    static::$user = new user(!empty($_SESSION['user_id']) ? $_SESSION['user_id'] : '');
        }
	}

    /**
     * @return user
     */
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
        return authentication::getUser()->hasPermission($permName);
	}

	public static function authenticate(){
		$slic = STAY_LOGGED_IN_COOKIE;
		if ( static::$user->logged_in == false && ( !isset($_COOKIE["{$slic}"]) || $_COOKIE["{$slic}"] != static::$user->guid ) ){
			header("location: " . LOGIN_URL . "?redirect=" . $_SERVER['REQUEST_URI']);
			return false;
		} else {
			// go ahead and load the user
			if ( !static::$user->isLoaded() ){
				static::$user->load($_SESSION['user_id']);
			}

			/*
			if ( !empty($_SESSION['expires']) ){
				if ( $_SESSION['expires'] < time() && $_COOKIE["{$slic}"] != static::$user->getKeyValue() ){
					session_destroy();
					header("location: " . LOGIN_URL . "?redirect=" . $_SERVER['REQUEST_URI']);
					return false;
				}
			}


			$_SESSION['expires'] = ( time() + 86400 );
			*/
			//$_SESSION['logged_in'] = true;
			return true;
		}
	}

	public static function login($username,$password){
		$SQL = "SELECT " . static::$user->getKey() . " as pkey FROM users WHERE `" . static::$user->getUsernameField() . "` = '" . static::$user->db->escape( $username ) . "' AND " . static::$user->getPasswordField() . " = '" . static::$user->hashPassword($password) . "'";
		$r = static::$user->db->query($SQL);
		if ( static::$user->db->numrows($r) > 0 ){
			$row = static::$user->db->fetchrow($r);
			static::$user->load($row['pkey']);
			if ( in_array("user_verified",static::$user->getColumns()) ){
				if ( static::$user->user_verified != 1 ){
					$_SESSION['error'] = "Email address not verified";
					static::$user->logged_in = false;
					return false;
				}
			}
			$_SESSION['user_id'] = $row['pkey'];
			//$_SESSION['logged_in'] = true;
			static::$user->logged_in = true;
			return true;
		} else {
			static::$user->logged_in = false;
			return false;
		}
	}

	public static function loginByKey($key){
        if ( false !== $keyData = AuthKeys::getByKey($key) ){
            static::$user->load($keyData['user_id']);
            if ( in_array("user_verified",static::$user->getColumns()) ){
                if ( static::$user->user_verified != 1 ){
                    $_SESSION['error'] = "Email address not verified";
                    static::$user->logged_in = false;
                    return false;
                }
            }
            $_SESSION['user_id'] = $keyData['user_id'];
            //$_SESSION['logged_in'] = true;
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
		setcookie(STAY_LOGGED_IN_COOKIE,null,0);
	}
}