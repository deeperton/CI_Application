<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
        load_class('Twig/Autoloader', false);
        Twig_Autoloader::register();
/**
 * Description of MY_Loader
 *
 * @author toxa
 */
class MY_Loader extends CI_Loader{

    private $tw_loader;
    private $tw_twig;

    function __construct() {
        parent::CI_Loader();
        //template engine
        $this->tw_loader = new Twig_Loader_Filesystem(APPPATH.'views/'.get_instance()->config->item('theme'));
        $this->tw_twig = new Twig_Environment($this->tw_loader, array(
            'cache' => APPPATH.'views/'.get_instance()->config->item('theme_cache'),
            'debug' => get_instance()->config->item('log_threshold') >= 3,
        ));
        
    }


    public function view($view, $vars = array(), $return = FALSE) {
        $template = $this->tw_twig->loadTemplate($view . '.html');

        $res = $template->render($vars);
        if($return){
            return $res;
        }

        get_instance()->output->append_output($res);
    }

}
