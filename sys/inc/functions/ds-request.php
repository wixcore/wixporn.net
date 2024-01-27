<?php 

function is_ajax()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    }
    return false; 
}

/**
* Является ли страница главной
*/
function is_home() 
{
    if (preg_match('/^(\/|\/\?.*|\/index\.php\??.*)$/m', $_SERVER['REQUEST_URI'])) {
        return true; 
    }
    return false; 
}

function get_query_vars($key, $default = false) {
    if (isset($_GET[$key])) {
        return $_GET[$key]; 
    }

    return $default; 
}

function get_site_url($pathuri = '') 
{
    /**
    * Если указан серверный путь, то вырезаем его до корня сайта
    */ 
    if (strpos($pathuri, ROOTPATH) === 0) {
        $pathuri = str_replace(ROOTPATH, '', $pathuri); 
    }

    // Устанавливаем HTTPS или HTTP протокол 
    $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http'); 

    return use_filters('ds_get_site_url', $http . '://' . $_SERVER['HTTP_HOST'] . $pathuri); 
}

/**
* Добавляет и заменяет get параметры в строке url
* @return string
*/ 
function get_query_url($get = array(), $url = '') 
{
    if (!$url) $url = get_current_url(); 

    $parse = parse_url(str_replace('&amp;', '&', $url));

    $query = array();
    if (!empty($parse['query'])) {
        parse_str($parse['query'], $query);
    }

    if (is_string($get)) {
        if (strpos($get, '?') === 0) {
            $get = str_replace('?', '', $get); 
        }
        parse_str($get, $array); 
        $get = $array; 
    }

    $query = array_replace($query, $get); 
    foreach($query AS $k => $v) {
        if ($v == 'unset') {
            unset($query[$k]); 
        }
    } 
    
    $parse['query'] = use_filters('ds_get_query_url', http_build_query($query)); 

    $url_build = array(); 
    if (!empty($parse['scheme']) && !empty($parse['host'])) {
        $url_build[] = $parse['scheme'] . '://' . $parse['host'];
    }

    if ($parse['path']) {
        $url_build[] = $parse['path']; 
    }

    if ($parse['query']) {
        $url_build[] = '?' . $parse['query']; 
    }

    $url = str_replace('&amp;', '&', join('', $url_build)); 

    return $url; 
}

function get_current_url() 
{
    $current_url = get_site_url($_SERVER['REQUEST_URI']); 
    return text($current_url); 
}

function uploads_uri() 
{
    return get_site_url('/sys/uploads'); 
}

/**
* Создает ссылку с хешем для подтверждения дейчтвия
* Защищает от подстановки url другим пользователем
* @return string
*/

function get_confirm_url($url = '', $key = 'confirm', $uniquie = '0') 
{
    $hash = md5(SALT_FORMS_FIELDS . ':' . get_ip_address() . ':' . $uniquie); 
    return get_query_url(array(
        $key => $hash, 
    ), $url); 
}

/**
* Сверяет хеш созданный функцией get_confirm_url() 
* @return bolean 
*/ 
function is_confirmed_valid($key, $uniquie = '0') 
{
    $hash = md5(SALT_FORMS_FIELDS . ':' . get_ip_address() . ':' . $uniquie); 

    if (isset($_GET[$key]) && $_GET[$key] == $hash) {
        return true; 
    }

    return false; 
}

/**
* Ищет ссылки в тексте и возвращает массивом
* @return array
*/ 
function ds_export_links( $text ) 
{
    preg_match_all(
        "#([\"']?)("
            . '(?:([\w-]+:)?//?)'
            . '[^\s()<>]+'
            . '[.]'
            . '(?:'
                . '\([\w\d]+\)|'
                . '(?:'
                    . "[^`!()\[\]{};:'\".,<>«»“”‘’\s]|"
                    . '(?:[:]\d+)?/?'
                . ')+'
            . ')'
        . ")\\1#",
        $text,
        $links
    );

    $links = array_unique( array_map( 'html_entity_decode', $links[2] ) );

    return array_values( $links );
}

function ds_rewrite_rule($regexp, $callback, $params = '') 
{
    global $DS_REWRITE_RULES; 

    $DS_REWRITE_RULES[] = use_filters('ds_rewrite_rule', array(
        'regex' => $regexp, 
        'callback' => $callback, 
        'params' => $params, 
    )); 
}

function ds_rewrite_rule_start() 
{
    global $DS_REWRITE_RULES, $user, $set, $ftime, $time, $conf, $webbrowser, $num; 

    $request = (!empty($_GET['route']) ? $_GET['route'] : 'index.php'); 

    foreach($DS_REWRITE_RULES AS $key => $rules) {
        if (preg_match('/^' . $rules['regex'] . '$/uim', $request, $matches)) {
            foreach($matches AS $key => $value) {
                $rules['params'] = str_replace('$'.$key, $value, $rules['params']); 

                // Замена в пути файла или callback 
                if (is_string($rules['callback']) && strpos($rules['callback'], '$') !== false) {
                    $rules['callback'] = str_replace('$'.$key, $value, $rules['callback']); 
                }
            }

            parse_str($rules['params'], $route_request); 
            if (is_array($route_request)) {
                ds_set('route_request', $route_request); 
            }

            if (is_callable($rules['callback'])) {
                add_event('pre_include_file', $rules['callback']); 
                do_event('pre_include_file', array($matches)); 
            }

            else {
                if (is_file($rules['callback'])) { 
                    require $rules['callback']; 
                    exit; 
                }                
            }
        }
    }

    do_event('page_not_found'); 

    //header("HTTP/1.0 404 Not Found");
    //exit;
}

function p404() 
{
    header("HTTP/1.0 404 Not Found");
    exit;
}

/**
* Функция перенаправляет пользователя 
*/ 
function ds_redirect($url, $status = 302, $redirect_by = 'DCMS-Social') 
{
    $url = use_filters( 'ds_redirect', $url, $status );
    $status = use_filters( 'ds_redirect_status', $status, $url );

    if (!$url) {
        return false;
    }

    do_event( 'ds_pre_redirect', $url, $status ); 

    $redirect_by = use_filters( 'ds_redirect_by', $redirect_by, $status, $url );
    if (is_string( $redirect_by)) {
        header( "X-Redirect-By: $redirect_by" );
    }

    header("Location: $url", true, $status);
    exit;
}

/**
* Регистрируем стандартный список Ajax запросов
*/ 
function ds_default_ajax() 
{
    $action = 'plugins_search_api'; 
    add_event('ajax_' . $action . '_callback', function() {

        $list = get_http_content('https://cms-social.ru/api/v1/plugins/list', array(
            's' => 'Тест', 
            'page' => '2', 
        )); 

        $array = json_decode($list, 1); 

        foreach($array['list'] AS $plugin) {
            if (plugin_exists($plugin['slug'])) {
                echo $plugin['title'] . ': true'; 
            } else {
                echo $plugin['title'] . ': false'; 
            }
        }
        die();  

    }); 
}

function get_http_content($url, $get = array(), $timeout = 3) 
{
    $url = get_query_url($get, $url); 

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $content = curl_exec($ch);
        curl_close($ch);        
    } else {
        $content = file_get_contents($url); 
    }

    return $content; 
}

// Хук после инициализации функций rewrite rules 
do_event('functions_rewrite_loaded'); 