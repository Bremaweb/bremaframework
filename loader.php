<?php
try {
    // AUTO LOADER CLASS WILL LOAD FROM BREMA FRAMEWORK AND APPLICATION
    require_once(BREMA_DIR . "classes/autoloader.class.php");
    $autoloader = new ClassAutoLoader();

    // INCLUDE BREMA FRAMEWORK FILES
    require_once(BREMA_DIR . "functions.php");
    require_once(BREMA_DIR . "html_helpers.php");

    // INCLUDE APPLICATION FILES
    if ( !defined('IS_LOCALHOST') ){
        if ( file_exists(APP_DIR . 'localhost.txt') ){
            define('IS_LOCALHOST',true);
        } else {
            define('IS_LOCALHOST',false);
        }
    }

    if ( IS_LOCALHOST && file_exists(APP_DIR . 'config/local') ){
        $confDir = APP_DIR . 'config/local/';
    } elseif ( file_exists(APP_DIR . 'config/production') ) {
        $confDir = APP_DIR . 'config/production/';
    } else {
        $confDir = APP_DIR . 'config/';
    }

    @include_once($confDir . "db_config.php");
    @include_once($confDir . "mail_config.php");
    @include_once(APP_DIR . "includes/functions.php");

    // static routes can be configured in a config/routes.php file or routes can be setup dynamically in the application
    if ( defined('ROUTE_FILE') !== false ){
        @include_once(ROUTE_FILE);
    } else {
        @include_once(APP_DIR . "/config/routes.php");
    }

    if ( !defined('NO_USER') ){
        authentication::initUser();
    }

    @include_once(APP_DIR . "includes/app_init.php");
} catch ( Exception $caughtException ){
    include(BREMA_DIR . 'error.php');
}

