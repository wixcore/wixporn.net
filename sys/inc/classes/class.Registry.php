<?php 

class Registry
{
    private static $vars = array();
    
    public static function set($key, $var) {
        Registry::$vars[$key] = $var;
        return true;
    }
    
    public static function get($key) {
        if (isset(Registry::$vars[$key]) == false) {
            return null;
        }

        return Registry::$vars[$key];
    }
    
    public static function remove($var) {
        unset(Registry::$vars[$key]);
    }
    
    public static function getAll() {
        return self::$vars; 
    }
}