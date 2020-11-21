<?php

function csrf(){

    //Generate a secure token using openssl_random_pseudo_bytes.
    $myToken = bin2hex(openssl_random_pseudo_bytes(24));

    //Store the token as a session variable.
    set_session('token', $myToken);

    echo '<input type="hidden" name="token" value="'.$myToken.'">';

}