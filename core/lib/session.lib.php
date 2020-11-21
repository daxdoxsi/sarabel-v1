<?php

function get_session($var = null, $default = null){

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION[$var])){
        return $_SESSION[$var];
    }
    else if ($default != null) {
        return $default;
    }
    else if ($var == null) {
        return $_SESSION;
    }
    else {
        return false;
    }

}

function set_session($var, $value = null){

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (is_array($var) && count($var) > 0 ){
        foreach($var as $key => $value){
            $_SESSION[$key] = $value;
        }
    }
    else {
        $_SESSION[$var] = $value;
    }

}

function set_flash($msg){
    set_session('flash_msg', $msg);
}

function get_flash(){
    $msg = get_session('flash_msg');
    set_session('flash_msg', '');
    return $msg;
}

function status_flash(){
    if (get_session('flash_msg') !== ''){
        return true;
    }
    return false;
}

class EncryptedSessionHandler extends SessionHandler
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function read($id)
    {
        $data = parent::read($id);

        if (!$data) {
            return "";
        } else {
            return decrypt($data, $this->key);
        }
    }

    public function write($id, $data)
    {
        $data = encrypt($data, $this->key);

        return parent::write($id, $data);
    }
}

