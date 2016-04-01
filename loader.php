<?php
// AUTO LOADER CLASS WILL LOAD FROM BREMA FRAMEWORK AND APPLICATION
require_once(BREMA_DIR . "/classes/autoloader.class.php");
$autoloader = new ClassAutoLoader();

// INCLUDE APPLICATION FILES
$dr = @include_once(APP_DIR . "/config/db_config.php");
if ( $dr == 1 ){
	require_once(BREMA_DIR . "/classes/" . DB_TYPE . ".db.class.php");
	$db = new db(DB_HOST,DB_PORT,DATABASE,DB_USER,DB_PASSWORD);
}
@include_once(APP_DIR . "/config/mail_config.php");
@include_once(APP_DIR . "/includes/functions.php");

// INCLUDE BREMA FRAMEWORK FILES
require_once(BREMA_DIR . "/functions.php");
require_once(BREMA_DIR . "/html_helpers.php");

$router = new uriRouter();

// static routes can be configured in a config/routes.php file or routes can be setup dynamically in the application
if ( defined('ROUTE_FILE') !== false ){
	@include_once(ROUTE_FILE);
} else {
	@include_once(APP_DIR . "/config/routes.php");
}

@include_once(APP_DIR . "/includes/app-init.php");

?>