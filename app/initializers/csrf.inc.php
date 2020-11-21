<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Make sure that the token POST variable exists.
    if (!isset($_POST['token'])) {
        error('No token found!', 'Form Error');
        error('Something happen with the form submission', 'Form Error', 'production');
    }

    //It exists, so compare the token we received against the
    //token that we have stored as a session variable.
    if (hash_equals($_POST['token'], get_session('token')) === false) {
        error('Token mismatch!', 'Security issue', 'development');
        error('Please make sure that you are using the forms correctly', 'Form problem', 'production');
    }

}
