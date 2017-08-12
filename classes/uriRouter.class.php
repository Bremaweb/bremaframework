<?php

class uriRouter {

	private static $routes = array();

	public static $params = array();

	public static function routeAdd($uri,$controller,$view = null, $requireAuthentication = false, $requirePermission = null){
		if ( empty($view) )
			$view = $controller;

		$newRoute = array();
		//$uri = str_replace("/","\/",$uri);
		$regex = "/^" . str_replace("/","\/",$uri) . "$/";
		$newRoute['view'] = $view;
		$newRoute['regex'] = $regex;
		$newRoute['controller'] = $controller;
		$newRoute['authenticate'] = !empty($requirePermission) ? true : $requireAuthentication;
		$newRoute['permission'] = $requirePermission;

		self::$routes[] = $newRoute;
	}

	private static function getRoute($requestURI){
		if ( defined('VIEW_DIR') !== false )
			$view_dir = VIEW_DIR;
		else
			$view_dir = APP_DIR . "/views";

		//$requestURI = rtrim($requestURI,'/');
		  //debugLog("Original URI: " . $requestURI);
		  $parpos = strpos($requestURI,"?");
		  if (  $parpos !== false ){
			// strip the parameters off the end of the requesturi
			//debugLog("parpos: " . $parpos);
			$requestURI = substr($requestURI,0,$parpos);
		  }

		  $requestURI = rtrim(str_replace(BASE_URI,"",$requestURI),"/");

			if ( $requestURI == "" )
				$requestURI = "/";

		  //debugLog("Mod URI: " . $requestURI);

		// match the URI
		$route = false;
		foreach ( self::$routes as $k => $v ){
			//echo "Matching: " . $v['regex'] . " -> " . $requestURI . "<br />";
			if ( preg_match($v['regex'],$requestURI) === 1 ){
				$route = $k;
				break;
			}
		}

		  if ( $route !== false ){
			$sRoute = self::$routes["{$route}"];

			if ( !empty($sRoute['authenticate']) ){
				if ( !authentication::authenticate() ){
					return false;
				}
			}

			if ( !empty($sRoute['permission']) ){
				if ( !authentication::hasPermission($sRoute['permission']) ){
					header("Status: 403 Forbidden");
					echo "403 Forbidden<br />";
					return false;
				}
			}

			if ( ( $sRoute['view'] == null || file_exists($view_dir . "/" . $sRoute['view'] . ".view.php") )
					&& ( file_exists(APP_DIR . "/controllers/" . $sRoute['controller'] . ".controller.php")
					|| file_exists(BREMA_DIR . "/controllers/" . $sRoute['controller'] . ".controller.php") )  ){

				self::$params = explode("/",$requestURI);

				@include(APP_DIR . "/includes/scripts.php");

				$r = @include(APP_DIR . "/controllers/" . $sRoute['controller'] . ".controller.php");

				if ( $r != 1 )
					@include(BREMA_DIR . "/controllers/" . $sRoute['controller'] . ".controller.php");

				if ( $sRoute['view'] != null ){
					if ( file_exists($view_dir . DIRECTORY_SEPARATOR . "header.inc.php") && defined('NO_HEADER') !== true )
						include($view_dir . DIRECTORY_SEPARATOR . "header.inc.php");

					include($view_dir . "/" . $sRoute['view'] . ".view.php");

					if ( file_exists($view_dir . DIRECTORY_SEPARATOR . "footer.inc.php") && defined('NO_FOOTER') !== true )
						include($view_dir . DIRECTORY_SEPARATOR . "footer.inc.php");
				}

			} else {
				header("Status: 404 Not Found");
				echo "404 FILE NOT FOUND - Missing View or Controller - " . $sRoute['view'] . "/" . $sRoute['controller'] . "<br />";
				return false;
			}
		  } else {
			// undefined route
				header("Status: 404 Not Found");
				echo "404 FILE NOT FOUND - Undefined Route - " . $requestURI;
				return false;
		  }
	}

	public static function getURI(){
		return ltrim(implode("/",self::$params),"/");
	}

	public static function go(){
		return self::getRoute($_SERVER['REQUEST_URI']);
	}
}

?>