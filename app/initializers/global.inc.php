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



# Handling maintenance mode
if ( !is_maintenance() ) {

    # Checking Database integrity verification
    $db = new DB_Model('app_tables');
    foreach ($config['database']['integrity_check'] as $table) {

        # Select a table from database.conf.php to validate if has records
        $db->table($table);
        $result = $db->get('1 = 1 LIMIT 1', ['*']);

        # if the table is empty then active maintenance mode
        if (count($result) == 0) {

            # Session handling
            session_start();
            session_destroy();

            # Create a file in order to active the maintenance mode
            db_var('maintenance_mode', 1 );

            # Redirects to maintenance mode page
            header('Location: /maintenance');
            exit;

        }

    } // foreach

    unset($db, $result);

}
else {

    # Validating if the website is in maintenance mode
    if (
        # Omit those URI Paths
        strstr(request_uri(),'dashboard/settings' ) === false &&
        strstr(request_uri(),'asana/authorize' ) === false &&
        strstr(request_uri(),'asana/callback' ) === false &&
        strstr(request_uri(),'maintenance' ) === false &&
        request_uri() != '' # Homepage

    ) {

        header('Location: /maintenance');
        exit;

    }
}
