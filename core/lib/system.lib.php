<?php

function view($tpl, $vars = [], $return_string = false) {

    # Files to require
    $file = PATH_VIEWS.$tpl.'.tpl.php';
    $controller = PATH_VIEWS.$tpl.'.ctl.php';

    # Checking if template file exists
    if (!is_file($file)){
        error("The View '$tpl' not found","View not found", 'development');
        exit;
    }

    # Start recording the output in memory
    if($return_string){
        ob_start();
    }

    # Adding variables to the runtime environment
    extract($vars);

    # Include controller if exists (same path of tpl file)
    if (is_file($controller)){
        require $controller;
    }

    # Loading template file
    require $file;

    # Save the output and return to the main controller
    if($return_string){
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}

function controller($ctl){
    $file = PATH_CONTROLLERS.$ctl['controller'].'.ctl.php';
    if (!is_file($file)){
        error("The Controller '{$ctl['controller']}' not found", "Controller not found", 'development');
        exit;
    }
    require $file;
    call_user_func($ctl["function"]);
}

function code_verifier(){
    $code = '';
    for ($c = 0; $c < 46; $c++){
        $method = rand(0,3);
        switch($method){
            case 0:
                $code .= rand(0,9);
                break;
            case 1:
                $code .= chr(rand(65,90));
                break;
            case 2:
                $code .= chr(rand(97,122));
                break;
            case 3:
                $code .= '-';
                break;
        }
    }
    return $code;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function pkce(){
    set_session('code_verifier', code_verifier());
    $hash = hash('sha256', get_session('code_verifier'));
    return base64url_encode(pack('H*', $hash));
}

function request_uri($real = true){
    global $routes;
    global $route_params;
    $real_request = explode('?',trim($_SERVER["REQUEST_URI"],'/'))[0];

    # Returns the full URI
    if ( $real === true ) {
        return $real_request;
    }

    if ( isset($routes[$real_request]) ) {
        return $real_request;
    }
    else {
        $parts = explode('/', $real_request);
        for ( $i = count($parts); $i > 0 ; $i-- ) {
            if ( isset( $routes[implode('/', $parts)] ) ) {
                $route_params = array_reverse($route_params);
                return implode('/', $parts);
            }
            else {
                $route_params[] = array_pop($parts);
            }
        }

        # URI doesn't match with any route
        $route_params = [];
        return $real_request;

    }

}

function sanitize_xss($value) {
    return htmlspecialchars(strip_tags($value));
}

function date_converter($datetime) {
    if ($datetime == null) {
        return null;
    }
    $datetime = str_replace('T',' ', $datetime);
    $datetime = explode('.', $datetime);
    return $datetime[0];
}

function convert_to_commas($data, $property, $tags = null){
    if ( is_array($data) && count($data) > 0 ) {
        $info = [];
        foreach ($data as $record) {
            if ($tags !== null) {
                $info[] = $tags[$record->$property];
            }
            else {
                $info[] = $record->$property;
            }
        }
        return implode('|',$info);
    }
    else {
        return '';
    }
}

function get_custom_field($fieldname, $custom_fields) {
    if ( is_array($custom_fields) && count($custom_fields) > 0 ) {
        foreach ($custom_fields as $field) {
            if ( trim(strtoupper($field->name)) == trim(strtoupper($fieldname)) ) {
                switch ($field->resource_subtype) {
                    case 'text':
                        return $field->text_value;
                        break;
                    case 'number':
                        return $field->number_value;
                        break;
                    case 'enum':
                        return $field->enum_value->name ?? null;
                        break;
                } // Switch
            } // if
        } // foreach
    } // if

    // Custom field not found
    return null;
}


function timeago($date) {
    $timestamp = strtotime($date);

    $strTime = array("second", "minute", "hour", "day", "month", "year");
    $length = array("60","60","24","30","12","10");

    $currentTime = time();
    if($currentTime >= $timestamp) {
        $diff     = time()- $timestamp;
        for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
            $diff = $diff / $length[$i];
        }

        $diff = round($diff);
        return $diff . " " . $strTime[$i] . "(s) ago ";
    }
}

function syslogmsg($message, $table, $records_table) {

    # Init var
    global $config;

    # Static values
    static $log;
    static $initial_time;
    static $max_exc;
    static $ip_address;
    static $http_user_agent;
    static $server_protocol;
    static $server_signature;
    static $server_software;

    # Set the static var max exec for first time
    if ( !isset($max_exc) ) {
        $max_exc = ini_get('max_execution_time');
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $initial_time = time();
        $log = [];
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];
        $server_protocol = $_SERVER['SERVER_PROTOCOL'];
        $server_signature = $_SERVER['SERVER_SIGNATURE'];
        $server_software = $_SERVER['SERVER_SOFTWARE'];
    }

    # Preparing info
    $info = new stdClass();
    $info->date_time = date('Y-m-d H:i:s');
    $info->created_by = get_session('user_gid');
    $info->table = $table;
    $info->initiated_by = get_session('name');
    $info->source = 'Web';
    $info->ip_address = $ip_address;
    $info->http_user_agent = $http_user_agent;
    $info->server_protocol = $server_protocol;
    $info->server_signature = $server_signature;
    $info->server_software = $server_software;

    # Preparing statistics
    $stats = new stdClass();
    $stats->time = new stdClass();
    $stats->time->initial = $initial_time;
    $stats->time->initial_datetime = date('Y-m-d H:i:s', $initial_time);
    $stats->time->max_execution = $max_exc;
    $stats->time->elapsed = time() - $initial_time;
    $stats->time->remain = $max_exc - $stats->time_elapsed;
    $stats->time->progress_percent = number_format(($stats->time_elapsed * 100) / $max_exc, 2);
    $stats->memory = new stdClass();
    $stats->memory->available = number_format(ini_get('memory_limit') * 1024,0);
    $stats->memory->used = number_format(memory_get_usage(false) / 1024, 0);
    $stats->memory->used_percent = number_format((memory_get_usage()  * 100) / ini_get('memory_limit'), 2);

    # Preparing log message
    $msg = new stdClass();
    $msg->datetime = date('Y-m-d H:i:s');
    $msg->table = $table;
    $msg->message = $message;

    # Assign the new message to the log
    $log[] = $msg;

    # Preparing output data structure
    $output = new stdClass();
    $output->info = $info;
    $output->tables = $records_table;
    $output->stats = $stats;
    $output->log = $log;

    # Writing Log file into the file system
    db_var('db_sync_log_file', json_encode($output));

}

function sys_backtrace(){
    echo '<pre>';
    var_dump(debug_backtrace());
    echo '</pre>';
}

// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }

    if ( strstr(request_uri(),'db-sync') !== false ) {
        db_var('db_sync_lock_expiration', date('Y-m-d H:i:s') );
    }

    // $errstr may need to be escaped:
    $errstr = htmlspecialchars($errstr);

    switch ($errno) {
        case E_USER_ERROR:
            echo "<b>ERROR</b> [$errno] $errstr<br />\n";
            echo "  Fatal error on line $errline in file $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Aborting...<br />\n";
            exit(1);

        case E_USER_WARNING:
            // echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            // echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
            break;

        default:
            echo "Unknown error type: [$errno] $errstr<br />\n";
            break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

