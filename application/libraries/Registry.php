<?php

/**
 * Description of Registry
 *
 * @author toxa
 */
class Registry {
    private static $data = array();
    
    public static function set($key, $value){
        self::$data[$key] = $value;
    }

    public static function get($key, $default = false){
        if(!isset(self::$data[$key])){
            return $default;
        }
        return self::$data[$key];
    }

}
