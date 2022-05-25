<?php

class cache {
    // caches are kept in memory after first disk access
    private static $caches = array();


    public static function get($key){
        $cacheFile = APP_DIR . 'cache/' . md5($key);
        if ( !empty(self::$caches[$key]) ){
            $cache = self::$caches[$key];
            if ( $cache['expires'] > time() ){
                return $cache['value'];
            } else {
                self::invalidateCacheEntry($key);
                return false;
            }
        }

        if ( false !== $cache = @file_get_contents($cacheFile) ){
            $cache = unserialize($cache);
            if ( $cache['expires'] > time() ){
                return $cache['value'];
            } else {
                self::invalidateCacheEntry($key);
            }
        }
        return false;
    }

    public static function set($key, $value, $lifetime = 600){
        $cacheExpires = time() + $lifetime;
        $cache = array("expires" => $cacheExpires, "value" => $value);
        self::$caches[$key] = $cache;

        $cacheFile = APP_DIR . 'cache/' . md5($key);
        return @file_put_contents($cacheFile, serialize($cache));
    }

    public static function invalidateCacheEntry($key){
        $cacheFile = APP_DIR . 'cache/' . md5($key);
        unset(self::$caches[$key]);
        unlink($cacheFile);
    }
}