<?php

class uriRouter {

	var $routes = array();
	var $params = array();
	function routeAdd($uri,$controller,$view = ""){
		if ( $view == "" )
			$view = $controller;

		$newRoute = array();
		//$uri = str_replace("/","\/",$uri);
		$regex = "/^" . str_replace("/","\/",$uri) . "$/";
		$newRoute['view'] = $view;
		$newRoute['regex'] = $regex;
		$newRoute['controller'] = $controller;

		$this->routes[] = $newRoute;
	}

	function getRoute($requestURI){
		if ( defined('VIEW_DIR') !== false )
			$view_dir = VIEW_DIR;
		else
			$view_dir = APP_DIR . "/views";

		//$requestURI = rtrim($requestURI,'/');
		  debugLog("Original URI: " . $requestURI);
		  $parpos = strpos($requestURI,"?");
		  if (  $parpos !== false ){
			// strip the parameters off the end of the requesturi
			debugLog("parpos: " . $parpos);
			$requestURI = substr($requestURI,0,$parpos);
		  }

		  $requestURI = str_replace(BASE_URI,"",$requestURI);

		  debugLog("Mod URI: " . $requestURI);

		// match the URI
		$route = false;
		foreach ( $this->routes as $k => $v ){
			//echo "Matching: " . $v['regex'] . " -> " . $requestURI . "<br />";
			if ( preg_match($v['regex'],$requestURI) === 1 ){
				$route = $k;
				break;
			}
		}

		  if ( $route !== false ){
			$sRoute = $this->routes["{$route}"];
			if ( ( file_exists($view_dir . "/" . $sRoute['view'] . ".view.php") && file_exists(APP_DIR . "/controllers/" . $sRoute['controller'] . ".controller.php") )  ){

				$this->params = explode("/",$requestURI);
				if ( file_exists(APP_DIR . "/includes/scripts.php") )
					include(APP_DIR . "/includes/scripts.php");

				include(APP_DIR . "/controllers/" . $sRoute['controller'] . ".controller.php");

				if ( file_exists($view_dir . DIRECTORY_SEPARATOR . "header.inc.php") )
					include($view_dir . DIRECTORY_SEPARATOR . "header.inc.php");

				include($view_dir . "/" . $sRoute['view'] . ".view.php");

				if ( file_exists($view_dir . DIRECTORY_SEPARATOR . "footer.inc.php") )
					include($view_dir . DIRECTORY_SEPARATOR . "footer.inc.php");

			} else {
				header("Status: 404 Not Found");
				echo "404 FILE NOT FOUND - Missing View or Controller - " . $sRoute['view'] . "/" . $sRoute['controller'] . "<br />";
				exit;
			}
		  } else {
			// undefined route
				header("Status: 404 Not Found");
				echo "404 FILE NOT FOUND - Undefined Route - " . $requestURI;
				exit;
		  }
	}

	function go(){
		$route = $this->getRoute($_SERVER['REQUEST_URI']);
	}
}

?>