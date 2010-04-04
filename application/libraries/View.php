<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Simple render class
 * It's a part of TEngineEx
 * 
 * @author Artyuh Anton <deeperton@gmail.com>
 */
class View {
    const TITLE = 'title';
    const KEYWORDS = 'keywords';
    const DESCRIPTION = 'description';

    //add methods
    const APPEND = 'append';
    const PREPEND = 'prepend';
    const REPLACE = 'replace';

    const PRE_SCRIPTS = 'pre_scripts';
    const AFTER_SCRIPTS = 'after_scripts';

    const PRE_STYLES = 'pre_styles';
    const AFTER_STYLES = 'after_styles';

    const THAT_PLACE_SCRIPT = 'that_place_script';
    const THAT_PLACE_STYLE = 'that_place_style';

    const WIDGET_START = '$$';
    const WIDGET_END = '/$$';
    
    const RENDER_VIEW = 'view';
    const RENDER_PARSER = 'parser';

    private $render_engine = self::RENDER_VIEW;
    private $render_loaded = '';

    //private $web_patch_to_theme = '';
    private $theme_patch = '';
    private $theme = '';
    private $doctype = 'xhtml11';
    private $encode = 'utf-8';
    private $keywords = '';
    private $description = '';
    private $meta = array('keywords' => '', 'description' => '', 'author' => 'Xitex Software. developer: Artyuh Anton');
    private $title = '';
    private $robots = 'all';
    private $links = null;

    private $compile_styles = false;
    private $style_no_compile_for_mask = '';
    private $style_sum_dir = 'css';

    private $rc_folder = 'rc';

    private $title_delimiter = ' :: ';
    private $keywords_delimiter = ',';
    private $description_delimiter = ' ';

    private $styles = array();
    private $scripts = array();
    private $raw_headers = array();
    private $script_jQuery_main = '';
    private $raw_script = array();
    private $jQ_onReady = array();
    private $jQ_onWindowsLoad = array();

    private $__body_header = '';

    private $scripts_libs = array();

    private $begin_html = array();
    private $end_html = array();

    private $allow_overwrite_keywords = true;
    private $allow_overwrite_description = true;
    private $allow_overwrite_title = true;
    private $title_cant_add = false;
    private $keywords_cant_add = false;
    private $description_cant_add = false;
    private $banchmark = false;

    private $view_system_init = false;
    private $CI;
    private $no_final_output = false;

    private $__out_buff = '';

    function  __construct($params = array()) {
        if(count($params) > 0) {
            $this->initialize($params);
        }
        $this->CI = & get_instance();
        $this->CI->load->helper('html');
        $this->CI->load->helper('string');
        log_message('debug', "View Class Initialized");
    }

    /**
     * Initialize option and object
     *
     * @param array $params settings
     */
    public function initialize($params = array()) {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
               if (isset($this->$key)) {
                  $this->$key = $val;
               }
           }
        }
    }

    public function getRcFolder(){
        return $this->rc_folder;
    }

    /**
     * Set new value for param with check allow_overwrite_$name
     *
     * @param string name of param
     * @param mixed new value of param
     * @return boolean
     */
    public function setParam($name, $value, $stop = false){
        if(isset($this->$name)){
            $flag = 'allow_overwrite_'.$name;
            if(isset($this->$flag)){
                $this->$name = ($this->$flag)?($value):($this->$name);
            } else {
                $p = $name.'_cant_add';
                $this->$p = $stop;
                $this->$name = $value;
            }
            return true;
        } else {
            $this->addToParam($name, $value);
        }
        return false;
    }

    /**
     * Add to param. with delimiter and method APPEND or PREPPEND
     *
     * @param string name
     * @param string value
     * @param const method, default = APPEND
     * @return boolean
     */
    public function addToParam($name, $value, $method = self::APPEND){
        if(isset($this->$name)){
            $p = $name . '_cant_add';
            if(isset($this->$p) && $this->$p){
                return false;
            }
            $flag = $name.'_delimiter';
            if(isset($this->$flag)){
                
                switch($method){
                    case  self::APPEND:{
                            $this->$name = ($this->$name =='') ? ($value) : ($this->$name . $this->$flag . $value);
                            break;
                    }
                    case self::PREPEND:{
                            $this->$name = ($this->$name =='') ? ($value) : ($value . $this->$flag . $this->$name);
                            break;
                    }
                    default:{
                            return $this->setParam($name, $value);
                    }
                }
                        
                return true;
            }
        } else {
			if(isset($this->meta[$name])){
				$flag = $name.'_delimiter';
				if(isset($this->$flag)){
					$this->meta[$name] = ($method == self::APPEND) ?
							($this->meta[$name] . $this->$flag . $value) :
							($value . $this->$flag . $this->meta[$name]);
					return true;
				}
			}
		}
        return false;
    }

    /**
     * Добавление мета информации на страницу
     *
     * @param array ассоциативный массив где ключ из набора title, description, keywords, robots
     * @param string Если не пустое, то значимые поля дописываються к уже
     *               существующим через разделитель указанный в этом параметре
     * @param bool если TRUE (по умолчанию) то в случае дозаписи, дозапись производиться в конец
     */
    public function setMetaData($params, $sep = '', $end = true){
        if(is_array($params)){
            foreach($params as $k=>$v){
                if($k == 'title'){
                    ($sep == '') ? ($this->title = $v) : (
                       ($end) ? ($this->title .= $sep.$v) : (
                             $this->title = $v.$sep.$this->title
                       )
                    );
				}
			}
		}
    }

    public function addStyle($path, $media = '', $_raw = false){
        if($_raw){
            $this->styles[]['raw'] = $path;
        } else {
            if(strpos($path, '/') != 0){
                $path = '/' .$path;
            }
            if($media != ''){
                $this->styles[] = array('var' => reduce_double_slashes('/' . $this->rc_folder . $path), 'media' => $media);
            } else {
                $this->styles[] = reduce_double_slashes('/' . $this->rc_folder . $path);
            }
        }
    }

    public function addHtml($str, $begin = true){
        if($begin){
            $this->begin_html[] = $str;
        } else {
            $this->end_html[] = $str;
        }
    }

    public function addScript($path, $pr = 5, $language = 'javascript', $type ='text/javascript', $_raw = false){
        static $last_patch = '';
        if($_raw){
            if(($n = search($this->scripts, 'src', $last_path)) === false){
                $this->scripts[] = array('raw' => array($path));
            } else {
                $this->scripts[$n]['raw'][] = $patch;
            }
        } else {
            if(preg_match("#^http://#", $path) == false){
                $path = reduce_double_slashes('/' . $this->rc_folder.'/'.$path);
            }
            $last_patch = $path;
            if(($n = search($this->scripts, 'src', $path)) === false){
                array_unshift($this->scripts, array('src' => $path, 'language' => $language, 'type' => $type, 'pr' => $pr));
            } else {
                $this->scripts[$n]['pr'] = $pr;
            }
        }
        //$this->scripts[] = ;
    }

    public function addRawScript($code, $language = 'javascript', $type = 'text/javascript'){
        $this->raw_script[] = array('code' => $code, 'language' => $language, 'type' => $type);
    }

    public function addRawHeader($s, $place = self::THAT_PLACE_SCRIPT){
        switch($place){
            case self::AFTER_SCRIPTS:
            case self::PRE_SCRIPTS:
            case self::PRE_STYLES:
            case self::AFTER_STYLES:{
                $this->raw_headers[$place][] = $s;
                break;
            }
            case self::THAT_PLACE_SCRIPT:{
                $this->addScript($s, 0, 0, 0, TRUE);
                break;
            }
            case self::THAT_PLACE_STYLE:{
                $this->addStyle($s, '', TRUE);
                break;
            }

        }
    }

    public function addjQ_OnReady($code){
        $this->jQ_onReady[] = $code;
    }

    public function addjQ_WinLoad($code){
        $this->jQ_onWindowsLoad[] = $code;
    }

    public function set_jQ_main($path, $owerwrite = false){
        if(!empty($this->script_jQuery_main) && !$owerwrite){
            return false;
        }
        if(strpos($path, '/') != 0 && preg_match("#^http://#", $path) == false){
            $path = '/' .$path;
        }
        $this->script_jQuery_main = '/'.$this->rc_folder.$path;
    }

    public function addLinks($array){
        $this->links[] = $array;
    }

    public function CompileStyles(){
        $l = count($this->styles);
        if($l == 0){
            return false;
        }
        $path = reduce_double_slashes($_SERVER['DOCUMENT_ROOT'] . '/' . $this->style_sum_dir);
        
        $file_name = md5(implode('_', $this->styles));
        
        $file = reduce_double_slashes($path .'/' . $file_name) . '.css';
        
        $web_file = reduce_double_slashes('/' . $this->style_sum_dir . '/' . $file_name) . '.css';
        $other = array();
        if(file_exists($file)){
            $ret = "\t\t".'<link rel="stylesheet" type="text/css" href="'.$web_file.'"/>'."\n";
            for($i = 0; $i < $l; $i++){
                $file_name = reduce_double_slashes($_SERVER['DOCUMENT_ROOT'] . '/' . $this->styles[$i]);
                if($this->style_no_compile_for_mask != ''){
                    if(preg_match($this->style_no_compile_for_mask, substr($file_name, strrpos($file_name, '/') + 1))){
                        $other[] = $this->styles[$i];
                    }
                }
            }
            if(count($other) > 0){
                foreach($other as $v){
                    if(is_array($v)){
                        $ret .= "\t\t".'<link rel="stylesheet" type="text/css" href="'.$v['var'].'" media="'.$v['media'].'"/>'."\n";
                    } else {
                        $ret .= "\t\t".'<link rel="stylesheet" type="text/css" href="'.$v.'"/>'."\n";
                    }
                }
            }
            
            return $ret;
        }
        $name = '';
        
        for($i = 0; $i < $l; $i++){
            $file_name = reduce_double_slashes($_SERVER['DOCUMENT_ROOT'] . '/' . $this->styles[$i]);
            if($this->style_no_compile_for_mask != ''){
                if(preg_match($this->style_no_compile_for_mask, substr($file_name, strrpos($file_name, '/') + 1))){
                    $other[] = $this->styles[$i];
                    continue;
                }
            }
            if(file_exists($file_name)){
                $s = file_get_contents($file_name);
                /////////
                // Вот здесь можно вставить код минимизации
                /////////
                file_put_contents($file, "\n/* style table from ". $this->styles[$i] . " \n" . $s, FILE_APPEND);
            }
        }
        $ret = "\t\t".'<link rel="stylesheet" type="text/css" href="'.$web_file.'"/>'."\n";
        if(count($other) > 0){
            foreach($other as $v){
                if(is_array($v)){
                    $ret .= "\t\t".'<link rel="stylesheet" type="text/css" href="'.$v['var'].'" media="'.$v['media'].'"/>'."\n";
                } else {
                    $ret .= "\t\t".'<link rel="stylesheet" type="text/css" href="'.$v.'"/>'."\n";
                }
            }
        }

        return $ret;
    }

    public function getHeader($styles = null, $scripts = null, $links = null){
        $theme = reduce_double_slashes($this->theme_patch . '/' . $this->theme);
        if(!$this->view_system_init){
            if(!file_exists($theme)){
                log_message('ERROR', 'theme not find');
                die('Theme not find '.$theme);
            }
            $conf['styles'] = array();
            $conf['scripts'] = array();
            $conf['link'] = array();
            
            @include($theme . '/' .'conf.php');

            if(isset($conf)){
                if(isset($conf['__body_header']) && count($conf['__body_header']) > 0){
                    $this->__body_header = '';
                    for($i = 0; $i < count($conf['__body_header']); $i++){
                        $this->__body_header .= $this->render($this->theme . '/' .$conf['__body_header'][$i], null, true);
                    }
                }
                if(isset($conf['styles']) && count($conf['styles']) > 0){
                    for($i = 0; $i < count($conf['styles']); $i++){
                        $s = reduce_double_slashes($_SERVER['DOCUMENT_ROOT'] . '/' . $this->rc_folder . '/' . $conf['styles'][$i]);

                        if(!file_exists($s)){
                            log_message('ERROR', 'style not find: ' . $conf['styles'][$i]);
                        } else {
                            $s = reduce_double_slashes($conf['styles'][$i]);
                            $this->addStyle($s);
                        }
                    }
                }
                if(isset($conf['script_jQuery_main'])){
                    $this->set_jQ_main($conf['script_jQuery_main']);
                }
                if(isset($conf['scripts']) && count($conf['scripts']) > 0){
                    for($i = 0; $i < count($conf['scripts']); $i++){
                        $s = reduce_double_slashes($_SERVER['DOCUMENT_ROOT'] . '/' . $this->rc_folder . '/' . $conf['scripts'][$i]);
                        if(!file_exists($s)){
                            log_message('ERROR', 'script not find: ' . $conf['scripts'][$i]);
                        } else {
                            $s = reduce_double_slashes($conf['scripts'][$i]);
                            $this->addScript($s);
                        }
                    }
                }

                if(isset($conf['jQ_onReady']) && count($conf['jQ_onReady']) > 0){
                    for($i = 0; $i < count($conf['jQ_onReady']); $i++){
                        $this->addjQ_OnReady($conf['jQ_onReady'][$i]);
                    }
                }

                if(isset($conf['jQ_WinLoad']) && count($conf['jQ_WinLoad']) > 0){
                    for($i = 0; $i < count($conf['jQ_WinLoad']); $i++){
                        $this->addjQ_WinLoad($conf['jQ_WinLoad'][$i]);
                    }
                }

                if(isset($conf['raw_script']) && count($conf['raw_script']) > 0){
                    for($i = 0; $i < count($conf['raw_script']); $i++){
                        $this->addRawScript($conf['raw_script'][$i]);
                    }
                }

                if(isset($conf['link']) && count($conf['link']) > 0){
                    for($i = 0; $i < count($conf['link']); $i++){
                        $this->addLinks($conf['link'][$i]);
                    }
                }
                if(isset($conf['title'])){
                    $this->addToParam('title', $conf['title']['value'], $conf['title']['method']);
                }
                if(isset($conf['keywords'])){
                    $this->addToParam('keywords', $conf['keywords']['value'], $conf['keywords']['method']);
                }
                if(isset($conf['description'])){
                    $this->addToParam('description', $conf['description']['value'], $conf['description']['method']);
                }
            }
            $this->view_system_init = true;
        }
        $out_s = '';
        
        $out_s .= doctype($this->doctype);
        $out_s .= "\n<html>\n\t<head>\n";
        $out_s .= "\t\t".'<meta http-equiv="Content-Type" content="text/html; charset='.$this->encode.'" />'."\n";

        $this->meta = array_merge($this->meta, array('description' => $this->description, 'keywords' => $this->keywords, 'robots' => $this->robots));

        foreach($this->meta as $k => $v){
            $out_s .= "\t\t".meta($k, $v)."\n";
        }

        $out_s .= "\t\t".'<title>'.$this->title.'</title>'."\n";

        if($styles != null){
            $this->addStyle($styles['path'], $style['media']);
        }

        if(isset($this->raw_headers[self::PRE_STYLES])){
            $mas = $this->raw_headers[self::PRE_STYLES];
            $l = count($m);
            for($i = 0; $i < $l; ++$i){
                $out_s .= "\t\t" . $mas[$i] . "\n";
            }
        }

        if(count($this->styles) > 0){
            $this->styles = array_unique($this->styles);
            if($this->compile_styles){
                $out_s .= $this->CompileStyles();
            } else {
                foreach($this->styles as $v){
                    if(is_array($v)){
                        if(isset($v['raw'])){
                            $out_s .= "\t\t" . $v['raw'] . "\n";
                        } else {
                            $out_s .= "\t\t".'<link rel="stylesheet" type="text/css" href="'.$v['var'].'" media="'.$v['media'].'"/>'."\n";
                        }
                    } else {
                        $out_s .= "\t\t".'<link rel="stylesheet" type="text/css" href="'.$v.'"/>'."\n";
                    }
                }
            }
        }

        if(isset($this->raw_headers[self::AFTER_STYLES])){
            $mas = $this->raw_headers[self::AFTER_STYLES];
            $l = count($m);
            for($i = 0; $i < $l; ++$i){
                $out_s .= "\t\t" . $mas[$i] . "\n";
            }
        }

        if($links != null){
            $this->addLinks($links);
        }
        
        if(count($this->links) > 0){
            //$this->links = array_unique($this->links);
            foreach($this->links as $k => $v){
                if(is_array($v)){
                    $s = "\t\t<link";
                    foreach($v as $k2=>$v2){
                        $s .= ' '.$k2.'="'.$v2.'"';
                    }
                    $s .= "/>\n";
                    $out_s .= $s;
                }
            }
        }
        
        if($scripts != null){
            $this->addScript($scripts['src'], $scripts['language'], $scripts['type']);
        }
        if(!empty($this->script_jQuery_main)){
            $out_s .= "\t\t".'<script language="javascript" type="text/javascript" src="'.$this->script_jQuery_main.'" ></script>'."\n";
        }

        if(isset($this->raw_headers[self::PRE_SCRIPTS])){
            $mas = $this->raw_headers[self::PRE_SCRIPTS];
            $l = count($m);
            for($i = 0; $i < $l; ++$i){
                $out_s .= "\t\t" . $mas[$i] . "\n";
            }
        }

        if(count($this->scripts) > 0){
            //$this->scripts = array_unique($this->scripts);
            $this->scripts = array_key_multi_sort($this->scripts, 'pr');
            foreach($this->scripts as $v){
                $out_s .= "\t\t".'<script language="'.$v['language'].'" type="'.$v['type'].'" src="'.$v['src'].'" ></script>'."\n";
                if(isset($v['raw'])){
                    $l = count($v['raw']);
                    for($i = 0; $i < $l; ++$i){
                        $out_s .= "\t\t" . $v['raw'][$i] . "\n";
                    }
                }
            }
        }

        if(isset($this->raw_headers[self::AFTER_SCRIPTS])){
            $mas = $this->raw_headers[self::AFTER_SCRIPTS];
            $l = count($m);
            for($i = 0; $i < $l; ++$i){
                $out_s .= "\t\t" . $mas[$i] . "\n";
            }
        }

        if(isset($this->raw_script))
        if(count($this->raw_script) > 0){
            foreach($this->raw_script as $v){
                $out_s .= "\t\t".'<script language="'.$v['language'].'" type="'.$v['type'].'">'."\n".$v['code']."\n".'</script>'."\n";
            }
        }

        if(count($this->jQ_onReady) > 0){
            $out_s .= '<script language="javascript" type="text/javascript">'."\n\t".'$(document).ready(function(){'."\n";
            foreach($this->jQ_onReady as $v){
                $out_s .= "\t\t".$v."\n";
            }
            $out_s .= "});\n</script>";
        }

        if(count($this->jQ_onWindowsLoad) > 0){
            $out_s .= '<script language="javascript" type="text/javascript">'."\n\t".'$(window).load(function(){'."\n";
            foreach($this->jQ_onWindowsLoad as $v){
                $out_s .= "\t\t".$v."\n";
            }
            $out_s .= "});\n</script>";
        }

        $out_s .= "</head>\n";
	//$out_s .= "<body topmargin=\"0\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\">\n";
        if($this->__body_header != ''){
            $out_s .= $this->__body_header;
        }
        return $out_s;
    }

    protected function preParse($theme, $data, $template = 'index')
    {
        if(!file_exists($theme . '/' . $template)){
            $template = 'index';
            if(!file_exists($theme . '/' . $template)){
                log_message('ERROR', 'template not find');
                die('system error');
            }
        }
    }

    public function render($file, $data = null, $return = true){
        if($this->no_final_output && !$return){
            return;
        }
        $main_render = false;
        if(!$return){
            $return = true;
            $main_render = true;
        }
        switch ($this->render_engine){
            case self::RENDER_PARSER:{
                if($this->render_loaded != 'parser'){
                    $this->CI->load->library('parser');
                }
                $this->render_loaded = $this->render_engine;
                $out = $this->CI->parser->parse($file, $data, $return);
                break;
            }
            case self::RENDER_VIEW:{
                $this->render_loaded = $this->render_engine;
                $out = $this->CI->load->base_view($file, $data, $return);
                break;
            }
            default:{
                $this->render_loaded = $this->render_engine;
                $out = $this->CI->load->base_view($file, $data, $return);
            }
        }
        if($main_render){
            
            if($this->CI->config->item('use_system_cache') && $this->CI->config->item('__syscache_can_cached')){
                $this->CI->output->cache($this->CI->config->item('syscache_lifetime'));
            }

            $out = $this->getHeader() . $out;

            if(count($this->begin_html) > 0){
                $out = preg_replace('/(\<body.+?\>)/i', '\1' . implode("\n", $this->begin_html), $out);
            }
            if(count($this->end_html) > 0){
                $out = str_replace('</body>', implode("\n", $this->end_html).'</body>', $out);
            }

            $this->CI->output->set_output($out);
            
            
        } else {
            return $out;
        }
    }

    public function StopOutput(){
        $this->no_final_output = true;
    }

    /**
     * Основной вывод
     *
     * @param $theme string
     * @param $template string
     * @param $data mixed
     * @param $return boolean
     */
    public function show($template, $data = null, $return = false, $renderEngine = null){
        if($renderEngine != null){
            $this->render_engine = $renderEngine;
        }

        
        if($this->banchmark){
            //$CI = & get_instance();
            $s = $this->render(reduce_double_slashes($this->theme . '/' . $template), $data, true);
            $s .= "<!-- Elapsed time: ".$CI->benchmark->elapsed_time()."\n".
                  "     Memory usage: ".$CI->benchmark->memory_usage().
                  "     SQL query count: ".$CI->db->query_count."-->";
            if($return){
                return $s;
            } else {
                echo $s;
            }
        } else {
            return $this->render(reduce_double_slashes($this->theme . '/' . $template), $data, $return);
        }
    }

    protected function _debug_prep_out($view, &$s){
        if($this->CI->config->item('log_threshold') > 1){
            return "\n<!-- START " . $view . '-->' . $s . '<!-- END ' . $view . ' -->';
        } else {
            return $s;
        }
    }

    public function lview($view, $vars = array(), $return = FALSE){
        if($return){
            $ret = $this->render(reduce_double_slashes($this->theme . '/' . $view), $vars, true);

            return $this->_debug_prep_out($view, $ret);

        }
        $this->__out_buff .= $this->_debug_prep_out($view, $this->render(reduce_double_slashes($this->theme . '/' . $view), $vars, true));
        
        return true;
    }

    
    public function get_buff(){
        return $this->__out_buff;
    }
}
?>
