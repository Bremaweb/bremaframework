<?php

class AuthKeys extends collection_base {
    protected static $db = null;
    protected static $dbConnectionName = 'default';
    protected static $table = 'authKeys';
    protected static $tableDef = null;
    protected static $wDb = null;


    public static function getByKey($key){
        $query = new Query(self::getDb(), self::getTable());
        $query->setParams(array(
            'auth_key' => $key,
            'expires' => array('operator' => '>', 'value' => '@NOW()')
        ));

        $data = self::getDb()->getRows($query);
        if ( !empty($data[0]) ){
            return $data[0];
        }

        return false;
    }

    public static function generateKey($userId, &$expires = null, $length = 96){
        $expires = !empty($expires) ? $expires : date("Y-m-d H:i:s", strtotime('+90 Day'));

        $crypt = new boCrypt(md5(time()));
        sleep(rand(1,3));

        $pk = md5(microtime(true));

        $search  = array("+", "/", "\\");
        $replace = array("P", "F", "B");

        $key = str_replace($search, $replace, substr('BOC64_' . $crypt->encrypt($pk, 100), 0, $length));

        self::add(array(
            'user_id' => $userId,
            'expires' => $expires,
            'auth_key' => $key
        ));

        return $key;
    }
}