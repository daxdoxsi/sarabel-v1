<?php


function dd($var) {
    echo '<pre>'.print_r($var, true).'</pre>';
    exit;
}

function ddd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    exit;
}


function dm($var) {
    echo '<pre>'.print_r($var, true).'</pre>';
}


function env($var) {

    static $file;
    $filename = '../.env';

    if (!isset($file) && is_file($filename))
    {
        $file = parse_ini_file($filename);
    }

    if ( !is_file($filename) )
    {
        error('Please check that the .env configuration file was created.', 'System Configuration', 'development');
        exit;
    }

    if ( !isset($file[$var]) ) {
        error('The parameter "'.$var.'" does not exist in the .env file','System Configuration', 'development');
        exit;
    }

    return $file[$var];

}

function page_title($name = null) {
    global $config;
    if ($name !== null) {
        return $name.' | '.$config['site_name'];
    }
    return $config['site_name'];
}

function set_cookie($name, $value, $expire = 31536000 /* one year */){
    global $config;
    setcookie($name, $value, time() + $expire, '/', $config['app_domain'], true, true);
}

function get_cookie($name) {
    return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : false);
}

function is_ajax(){
    if(
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0
    ){
        //Set our $isAjaxRequest to true.
        return true;
    }
    return false;
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function is_maintenance() {
    return db_var('maintenance_mode') == 1;
}
