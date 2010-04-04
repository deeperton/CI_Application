<?php
/**
 * Description of MY_Controller
 *
 * @author toxa
 */

class MY_Controller extends Controller{

    const CACHE_LIFE_TIME = 1200;

    public $layout = 'layout';
    
    public function  __construct() {
        parent::Controller();
        load_class('Autoloader', false);
        $this->load->library('view');
        Autoloader::registerAutoloader();
        Date::init($this->config->item('time_zone'));
        $this->load->library('zendcache', null, 'cache');
    }

    final public function _remap($method){
        
        $params = $this->uri->rsegment_array();
        
        $params = array_slice($params, 2);
        
        $res = '';
        if(method_exists($this, '_reremap')){
            array_unshift($params, $method);
            ob_start();
            $res = call_user_method_array('_reremap', $this, $params);
            $res_ = ob_get_contents(); ob_end_clean();
            if($res == null){
                $res = $res_;
            }
        } elseif(method_exists($this, $method)){
            ob_start();
            $res = call_user_method_array($method, $this, $params);
            
            $res_ = ob_get_contents(); ob_end_clean();
            if($res == null){
                $res = $res_;
            }
        }
        
        if(is_array($res)){
            $this->output->set_header("HTTP/1.1 200 OK");
            $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
            $this->output->set_header("Cache-Control: post-check=0, pre-check=0");
            $this->output->set_header('Content-Type: application/json; charset=utf-8');
            $this->output->set_header("Pragma: no-cache");
            $out = json_encode($res);

            $this->output->set_output($out);
        } else {
            if(Registry::get('AJAX')){
                echo $res;
            } else {
                $res = $this->view->get_buff();
                $this->view->show($this->layout, array('content' => $res), false);
            }
        }
    }
}

