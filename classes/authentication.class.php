<?php

class authentication {
	private static $user;

	public static function initUser(){
		self::$user = new user();
	}

	public static function getUser(){
		return self::$user;
	}

	public static function isLoggedIn(){
		return self::$user->loggedIn();
	}

	public static function hasPermission($permName){
		return self::$user->hasPermission(permissions::get($permName));
	}

	public static function authenticate(){
		return self::$user->authenticate();
	}
}