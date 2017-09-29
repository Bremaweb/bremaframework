<?php

class cache {
    // caches are kept in memory after first disk access
    private static $caches = array();


    public static function get($key){
        debugLog("cache::get($key)",4);
        $cacheFile = APP_DIR . '/cache/' . md5($key);
        if ( !empty(self::$caches[$key]) ){
            $cache = self::$caches[$key];
            if ( $cache['expires'] > time() ){
                debugLog("cache returned from memory",4);
                return $cache['value'];
            } else {
                debugLog("cache in memory expired",4);
                self::invalidateCacheEntry($key);
                return false;
            }
        }

        if ( false !== $cache = @file_get_contents($cacheFile) ){
            debugLog("cache retrieved from file",4);
            $cache = unserialize($cache);
            if ( $cache['expires'] > time() ){
                debugLog("cache returned from file",4);
                return $cache['value'];
            } else {
                debugLog("cache on file expired",4);
                self::invalidateCacheEntry($key);
            }
        }
        debugLog("no cache entry returned");
        return false;
    }

    public static function set($key, $value, $lifetime = 600){
        $cacheExpires = time() + $lifetime;
        $cache = array("expires" => $cacheExpires, "value" => $value);
        self::$caches[$key] = $cache;

        $cacheFile = APP_DIR . '/cache/' . md5($key);
        return @file_put_contents($cacheFile, serialize($cache));
    }

    public static function invalidateCacheEntry($key){
        $cacheFile = APP_DIR . '/cache/' . md5($key);
        unset(self::$caches[$key]);
        unlink($cacheFile);
    }
}