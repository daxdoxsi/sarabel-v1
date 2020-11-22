<?php

# Init vars
global $config;

# Sanitize user inputs

# GET
if(isset($_GET) && count($_GET) > 0){
    foreach($_GET as $key => $value){
        $tmp_key = sanitize_xss($key);
        if ($key !== $tmp_key) {
            unset($_GET[$key]);
        }
        if ( !in_array($key, $config['exclude_sanitize']['get']) ) {
            $_GET[$tmp_key] = sanitize_xss($value);
        }
    }
}

# POST
if(isset($_POST) && count($_POST) > 0){
    foreach($_POST as $key => $value){
        if ( !in_array($key, $config['exclude_sanitize']['post']) ) {
            $tmp_key = sanitize_xss($key);
            if ($key !== $tmp_key) {
                unset($_POST[$key]);
            }
            $_POST[$tmp_key] = sanitize_xss($value);
        }
    }
}

# COOKIE
if(isset($_COOKIE) && count($_COOKIE) > 0){
    foreach($_COOKIE as $key => $value){
        $tmp_key = sanitize_xss($key);
        if ($key !== $tmp_key) {
            unset($_COOKIE[$key]);
        }
        if ( !in_array($key, $config['exclude_sanitize']['cookie']) ) {
            $_COOKIE[$tmp_key] = sanitize_xss($value);
        }
    }
}

# Cleaning memory
unset($tmp_key, $key, $value);


# First visit of the user
if (get_cookie(md5('unique_id')) === false && request_uri() != '') {
    set_flash('Welcome to the Asana Reports');
    header('Location: /');
    exit;
}

