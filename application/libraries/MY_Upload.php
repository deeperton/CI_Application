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
class MY_Upload extends CI_Upload {

    function do_upload($field = 'userfile', $file_name = null, $resize_rects = null){

        if($file_name != null){
            if(strpos($file_name, '/')){
                $path = explode('/', $file_name);
                $file_name = array_pop($path);
                $path = implode('/', $path);
                $path = trim($path, ' /');
                if($this->upload_path[strlen($this->upload_path) - 1] != '/'){
                    $this->upload_path .= '/';
                }
                if(!mkdir($this->upload_path . $path, 0755, true)){
                    $this->set_error('Can\'t create folder');
                }
                $this->upload_path = $this->upload_path . $path;
            }
            $_FILES[$field]['name'] = $file_name;
        }
        
        parent::do_upload($field);


        if($resize_rects != null && is_array($resize_rects)){
            $ci =& get_instance();
            $ci->load->library('image_lib', null, 'img');

            $config['image_library'] = 'gd2';
            $config['source_image']	= $this->upload_path.$this->file_name;
            $config['create_thumb'] = FALSE;
            $config['maintain_ratio'] = TRUE;
            $l = count($resize_rects);
            $out = array();
            for($i = 0; $i < $l; ++$i){
                $config['width']     = $resize_rects[$i]['width'];
                $config['height']    = $resize_rects[$i]['height'];
                if(!isset($resize_rects[$i]['patern'])){
                    $resize_rects[$i]['patern'] = '{file}_{width}x{height}.{ext}';
                }
                $out[] = $config['new_image'] = $this->upload_path.$this->_make_image_name($resize_rects[$i]['patern'], $resize_rects[$i]);
                $ci->img->initialize($config);
                $ci->img->resize();
            }
            return $out;
        }
        return $this->file_name;
    }

    function _make_image_name($patern, $values){
        $s = $patern;
        $values['file'] = $this->file_name;
        $values['ext'] = $this->file_ext;

        foreach($values as $k=>$v){
            $s = str_replace('{'.$k.'}', $v, $s);
        }
        return $s;
    }
}
?>
