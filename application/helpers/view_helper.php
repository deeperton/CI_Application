<?php
/**
 * @author Artyuh Anton <deeperton@gmail.com>
 * @copyright MID 2009
 */

function v_setParam($key, $value, $stop = false){
    return get_instance()->view->setParam($key, $value, $stop);
}

function v_addToParam($key, $value, $method = View::APPEND){
    return get_instance()->view->addToParam($key, $value, $method);
}

function v_addStyle($path, $media = ''){
    return get_instance()->view->addStyle($path, $media);
}

function v_addScript($path, $language = 'javascript', $type ='text/javascript'){
    return get_instance()->view->addScript($path, $language, $type);
}

function v_addRawScript($code, $language = 'javascript', $type = 'text/javascript'){
    return get_instance()->view->addRawScript($code, $language, $type);
}

function v_addHtml($str, $begin = true){
    return get_instance()->view->addHtml($str, $begin);
}

function v_addjQ_OnReady($code){
    return get_instance()->view->addjQ_OnReady($code);
}

function v_addjQ_WinLoad($code){
    return get_instance()->view->addjQ_WinLoad($code);
}

function v_set_jQ_main($path, $owerwrite = false){
    return get_instance()->view->set_jQ_main($path, $owerwrite);
}

function v_getRcFolder(){
    return get_instance()->view->getRcFolder();
}

function v_show($template, $data = null, $return = true, $renderEngine = null){
    return get_instance()->view->show($template, $data, $return, $renderEngine);
}

