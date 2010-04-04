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
class MY_Loader extends CI_Loader{
    public function view($view, $vars = array(), $return = FALSE){
        $CI =& get_instance();
        if(isset($CI->view)){
            return $CI->view->lview($view, $vars, $return);
        } else {
            return parent::view($view, $vars, $return);
        }
    }

    public function base_view($view, $vars = array(), $return = FALSE){
        return parent::view($view, $vars, $return);
    }

   
}
?>
