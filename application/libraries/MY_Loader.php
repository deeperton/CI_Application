<?php
/**
 * Description of MY_Loader
 *
 * @author toxa
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
