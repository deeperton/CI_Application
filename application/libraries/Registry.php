<?php

/**
 * MY_CI
 *
 * LICENSE: LGPL
 * 
 * @package	   CI_application
 * @author	   Artyuh Anton 
 * @copyright  Artyuh Anton 2010
 * @link http://github.com/deeperton/CI_Application
 * @version    0.1
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
