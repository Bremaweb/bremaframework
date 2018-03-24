<?php

namespace bfMailer;

class MailerDef {
    private static $definitions = array();

    public static function addDef($defName, $mailer, $params){
        self::$definitions[$defName] = array(
            'mailer' => $mailer,
            'params' => $params
        );
    }

    public static function getDef($defName){
        return !empty(self::$definitions[$defName]) ? self::$definitions[$defName] : false;
    }

    public static function hasDefs(){
        return !empty(self::$definitions);
    }

    public static function defExists($defName){
        return !empty(self::$definitions[$defName]);
    }
}