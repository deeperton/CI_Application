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

class Autoloader {

    public static function registerAutoloader(){
        if (!function_exists('spl_autoload_register')) {
            log_message('ERROR', 'no spl_autoload_register');
            exit;
        }

        $class = 'Autoloader';
        spl_autoload_register(array($class, 'autoload'));
    }

    public static function autoload($class){
        if(strpos($class, '_')){
            $p = explode('_', $class);
            $file = array_pop($p);
            $patch = implode('/', $p) . '/';

        } else {
            $patch = '';
            $file = $class;
        }
        
        if(is_file(APPPATH.'libraries/'.$patch.$file.EXT)){
            include(APPPATH.'libraries/'.$patch.$file.EXT);
            return;
        }

        if(is_file(APPPATH.'models/'.$patch.$file.EXT)){
            include(APPPATH.'models/'.$patch.$file.EXT);
            return;
        }

        //throw new Exception('libraries/'.$patch.$file.EXT);
    }

}
?>
