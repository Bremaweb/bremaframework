<?php


class mCache {
    private static $_serverList = null;
    private $_mcached = null;

    public static function setServerList($servers){
        static::$_serverList = $servers;
    }

    public function __construct($persistentId = null){
        $this->_mcached = new Memcached($persistentId);
        if ( count($this->_mcached->getServerList()) == 0 ){
            if ( !empty(static::$_serverList) ){
                foreach ( static::$_serverList as $server ){
                    $this->_mcached->addServer($server['host'], $server['port']);
                }
            } else {
                $this->_mcached->addServer('localhost', '11211');
            }
        }
    }

    public function update($key, $value, $exp = null){
        if ( false === $this->_mcached->get($key) ){
            return $this->_mcached->add($key, $value, $exp);
        } else {
            return $this->_mcached->replace($key, $value, $exp);
        }
    }

    public function get($key, $cb = null){
        return $this->_mcached->get($key, $cb);
    }

    public function add($key, $value, $exp = null){
        return $this->_mcached->add($key, $value, $exp);
    }

    public function replace($key, $value, $exp = null){
        return $this->_mcached->replace($key, $value, $exp);
    }
}