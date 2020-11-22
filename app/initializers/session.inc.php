<?php
# Intercepts all the communication with the session
# and encrypt the information
//$key = 'sbxZ2K7jdjdr7485jdj3j2m';
//$handler = new EncryptedSessionHandler($key);
//session_set_save_handler($handler, true);


# Set session vars
if (get_session('status') != 'active') {

    # User is not registered in the system
    set_flash('Welcome again to the Asana API');
    header('Location: /');
    exit;

}

