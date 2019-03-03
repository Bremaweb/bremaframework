<?php

class App {

    public static function setTimezone($timezone){
        if ( !empty($timezone) ){
            registry::set('_TIMEZONE', $timezone);
            date_default_timezone_set(registry::get('_TIMEZONE'));

            foreach ( dbConnection::getAllConnections() as $conn ){
                $conn->query("SET time_zone = '" . $timezone . "';");
            }
        }
    }

}