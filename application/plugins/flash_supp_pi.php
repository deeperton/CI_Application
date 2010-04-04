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

if(!function_exists('swfobj')){
    /**
     * подключение флеша
     *
     * @param string $file файл swf
     * @param string $altContent альтернативный контент
     * @param string $div_id id для контенера приемника объекта
     * @param int $width ширина
     * @param int $height высота
     * @param array $flash_vars ассоциативнй массив переменых для swf
     * @param array $params ассоциативнй массив переменных для object
     * @param array $object_attr ассоциативнй массив атрибутов для object
     * @param string $expressInstall путь до альтернативного флеш ролика если версия флеш не удовлетворяет требованиям, если null -- ставиться путь по умолчанию
     * @param bool $dynamicMode способ построения. TRUE - динамическое замещение через  JavaScript
     * @return string html-код. Скрипты подключаються через хелпер View
     */
    function swfobj($file, $altContent, $div_id, $width, $height, $flash_vars = null, $params = null, $object_attr = null, $expressInstall = null, $dynamicMode = TRUE){
        
        $ci =& get_instance();
        if(isset($ci->cache)){
            $__params = func_get_args();
            $__params = "flash_" . md5(print_r($__params, true));
            if($out = $ci->cache->load($__params) && $codetempl = $ci->cache->load('code_' . $__params)){
                v_addScript('js/swfobject/swfobject.js');
                v_addjQ_OnReady($codetempl);
                return $out;
            }
        }
        
        $rc_folder = v_getRcFolder();
        if(is_null($expressInstall) || $expressInstall == 0){
            $expressInstall = '/' . $rc_folder . '/js/swfobject/expressInstall.swf';
        } else {
            $s = WEB_HTDOCS . '/' . $expressInstall;
            $s = preg_replace("#([^:])//+#", "\\1/", $s);
            if(!file_exists($s)){
                $s = WEB_HTDOCS . '/' . $rc_folder . '/' . $expressInstall;
                $s = preg_replace("#([^:])//+#", "\\1/", $s);
                if(!file_exists($s)){
                    $expressInstall = null;
                } else {
                    $expressInstall = '/' . $rc_folder . '/' . $expressInstall;
                }
            }
        }
        //определяем режим работы
        if(!$dynamicMode){
            $template =<<<STR
    <object id="{id}" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{width}" height="{height}">
        <param name="movie" value="{file}" />
        {params}
        <!--[if !IE]>-->
        <object type="application/x-shockwave-flash" data="{file}" width="{width}" height="{height}">
            {params}
        <!--<![endif]-->
            {alt}
        <!--[if !IE]>-->
        </object>
        <!--<![endif]-->

      </object>
STR;
            $codetempl = 'swfobject.registerObject("{id}", "9.0.0", "{express}");';
        } else {
            $template =<<<STR
<div id="{id}">
      {alt}
</div>
STR;
            $codetempl = 'swfobject.embedSWF("{file}", "{id}", "{width}", "{height}", "9.0.0", "{express}", {vars}, {params}, {attr});';
        }
        
        $s = WEB_HTDOCS . '/' . $file;
        $s = preg_replace("#([^:])//+#", "\\1/", $s);
        if(!file_exists($s)){
            $s = WEB_HTDOCS . '/' . $rc_folder . '/' . $file;
            $s = preg_replace("#([^:])//+#", "\\1/", $s);
            if(!file_exists($s)){
                return "\n<!-- file not find $file -->\n".$altContent;
            } else {
                $file = '/' . $rc_folder . '/' . $file;
                $file = preg_replace("#([^:])//+#", "\\1/", $file);
            }
        }

        $out = '';
        $out = str_replace('{width}', $width, $template);
        $out = str_replace('{height}', $height, $out);
        $out = str_replace('{file}', $file, $out);
        $out = str_replace('{id}', $div_id, $out);
        $out = str_replace('{alt}', $altContent, $out);

        if(!is_null($flash_vars) && $flash_vars != 0){
            if(is_array($flash_vars)){
                $s = '{';
                foreach($flash_vars as $k => $v){
                    $s .= "$k : \"$v\",";
                }
                $s = substr($s, 0, strlen($s) - 1);
                $s .= '}';
            } else {
                $s = $flash_vars;
            }
            $codetempl = str_replace('{vars}', $s, $codetempl);
        } else {
            $codetempl = str_replace('{vars}', 'false', $codetempl);
        }
        if(!is_null($params) && $params != 0 || strlen($params) > 0){
            if(is_array($params)){
                $s = '{';
                foreach($params as $k => $v){
                    $s .= "$k : \"$v\",";
                }
                $s = substr($s, 0, strlen($s) - 1);
                $s .= '}';
            } else {
                $s = $params;
            }
            $codetempl = str_replace('{params}', $s, $codetempl);
            if(strpos($out, '{params}')){
                $s = '';
                if(is_array($params)){
                    foreach($params as $k => $v){
                        $s .= "<param name=\"$k\" value=\"$v\" />";
                    }
                } else {
                    $s = $params;
                }
                $out = str_replace('{params}', $s, $out);
            }
        } else {
            $codetempl = str_replace('{params}', 'false', $codetempl);
            $out = str_replace('{params}', '', $out);
        }
        if(!is_null($object_attr) && $object_attr != 0){
            if(is_string($object_attr)){
                $s = $object_attr;
            } else {
                $s = '{';
                foreach($object_attr as $k => $v){
                    if($k == 'class'){
                        $k = 'styleclass';
                    }
                    $s .= "$k : \"$v\",";
                }
                $s = substr($s, 0, strlen($s) - 1);
                $s .= '}';
            }
            $codetempl = str_replace('{attr}', $s, $codetempl);
        } else {
            $codetempl = str_replace('{attr}', 'false', $codetempl);
        }

        $codetempl = str_replace('{width}', $width, $codetempl);
        $codetempl = str_replace('{height}', $height, $codetempl);
        $codetempl = str_replace('{file}', $file, $codetempl);
        $codetempl = str_replace('{id}', $div_id, $codetempl);
        $codetempl = str_replace('{express}', $expressInstall, $codetempl);

        //закончили, подключаем, возвращаем код
        v_addScript('js/swfobject/swfobject.js');
        v_addjQ_OnReady($codetempl);
        
        if(isset($__params)){
            $ci->cache->save($out, $__params, array('media', 'flash'));
            $ci->cache->save($codetempl, 'code_' . $__params, array('media', 'flash'));
        }
        
        return $out;
    }
}
