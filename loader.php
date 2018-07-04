<?php
// AUTO LOADER CLASS WILL LOAD FROM BREMA FRAMEWORK AND APPLICATION
require_once(BREMA_DIR . "/classes/autoloader.class.php");
$autoloader = new ClassAutoLoader();

// INCLUDE APPLICATION FILES
@include_once(APP_DIR . "/config/db_config.php");
@include_once(APP_DIR . "/config/mail_config.php");
@include_once(APP_DIR . "/includes/functions.php");

// INCLUDE BREMA FRAMEWORK FILES
require_once(BREMA_DIR . "/functions.php");
require_once(BREMA_DIR . "/html_helpers.php");

// static routes can be configured in a config/routes.php file or routes can be setup dynamically in the application
if ( defined('ROUTE_FILE') !== false ){
	@include_once(ROUTE_FILE);
} else {
	@include_once(APP_DIR . "/config/routes.php");
}

if ( !defined('NO_USER') ){
    authentication::initUser();
}

@include_once(APP_DIR . "/includes/app_init.php");

