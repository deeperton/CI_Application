<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function site_url($urlname = '', $params = NULL)
{
    $CI = & get_instance();
    if($params !== NULL && method_exists($CI->router, 'buildUrl'))
    {
        return $CI->router->buildUrl($urlname, $params);
    } else
    {
        return $CI->config->site_url($urlname);
    }
}

function redirect($uri = '', $method = 'location', $http_response_code = 302)
{
    //debug_backtrace();
    //die(' ');
    if(is_array($uri)){
        $uri = site_url($uri[0], $uri[1]);
    } else {
        if ( ! preg_match('#^https?://#i', $uri))
        {
            $uri = site_url($uri);
        }
    }

    switch($method)
    {
        case 'refresh'	: header("Refresh:0;url=".$uri);
            break;
        case 'referer'  : header("Location: ".CI::$APP->input->server('HTTP_REFERER'), TRUE, $http_response_code);
            break;
        default         : header("Location: ".$uri, TRUE, $http_response_code);
            break;
    }
    exit;
}

?>
