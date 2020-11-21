<?php


function curl_request_get($url, $gets = [], $headers = [], $body_json_decode = true){

    global $config;
    $original_url = $url;
    $retries = 0;

    // start:
    do {

        # Init Vars
        $url = $original_url;
        $handle = curl_init();

        $url .= (is_array($gets) && count($gets) > 0 ? '?'.http_build_query($gets) : '');

        $curl_opt = [
            CURLOPT_URL                         => $url,
            CURLOPT_HEADER                      => false,
            CURLOPT_RETURNTRANSFER              => true,
            CURLOPT_HTTPHEADER                  => (count($headers)>0?$headers:[]),
        ];

        curl_setopt_array($handle, $curl_opt );

        # Executing the request to the Asana API Server
        $output = curl_exec($handle);

        # Delay for a period of time in order to prevent Too many transaction at time
        usleep($config['asana']['api_request_delay']);

        if ($body_json_decode) {
            $output = json_decode($output, null, 2048);
        }

        # Detecting if response is a object
        if ( is_object($output->data) ) {
            $tmp = [];
            $tmp = new stdClass();
            $tmp->data = [];
            $tmp->data[] = $output->data;
            $tmp->next_page = null;
            unset($output);
            $output = $tmp;
        }

        # Getting response code
        $response_code = curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );

        curl_close($handle);

        if ($response_code == 200 || $response_code == 201) {

            return $output;

        }
        else {

            $gotostart = false;
            $force_refresh_token = false;
            switch ($response_code):

                # Asana Token expired
                case 401:

                    # Counting the intents
                    $retries++;

                    # Running refresh process
                    $force_refresh_token = true;
                    require PATH_INITIALIZERS.'session.inc.php';
                    require PATH_INITIALIZERS.'token.inc.php';

                    # Updating headers
                    $headers = ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')];

                    # Retry the request again
                    $gotostart = true;

                    # More than 10 tries
                    if ($retries >= 10) {

                        $gotostart = false;
                        error("Loop infinity in api get due to Token Expired (Response code: 401)", 'Token Expired');

                        # Update lock date expiration
                        $lock_expiration = date('Y-m-d H:i:s', time());
                        db_var('db_sync_lock_expiration', $lock_expiration);

                        exit;

                    }

                    break;

                # 403: The Asana API is currently unavailable.
                # 429: Too Many Requests
                # 503: The Asana API is down.
                case 403:
                case 429:
                case 503:

                    # Counting the intents
                    $retries++;

                    # Wait for a minute
                    sleep(60);

                    # Try again
                    $gotostart = true;

                    # More than 10 tries
                    if ($retries >= 10) {

                        $gotostart = false;
                        error("Loop infinity in api get due to response code: ".$response_code, 'Token Expired');

                        # Update lock date expiration
                        $lock_expiration = date('Y-m-d H:i:s', time());
                        db_var('db_sync_lock_expiration', $lock_expiration);

                        exit;

                    }

                break;

                default:
                    error('Response code '.$response_code.' in the request to '.$url.' cannot be handled. <pre>'.print_r($output,true).'</pre>','Curl request (GET) error','development');
                    error('There is an issue with data extraction from Asana.', 'API Request Error', 'production');

                    # Update lock date expiration
                    $lock_expiration = date('Y-m-d H:i:s', time());
                    db_var('db_sync_lock_expiration', $lock_expiration);

                    exit;

                    break;

            endswitch;

        } // if
    }
    while ($gotostart);

}


/**
 * @param $url
 * @param array $posts
 * @param array $headers
 * @return mixed
 *
 */

function curl_request_post($url, $posts = [], $headers = []){

    # Init vars
    $retries = 0;

    do {

        global $config;
        $handle = curl_init();

        # Verify if content should be send it
        if (isset($posts['body'])) {
            $posts = $posts['body'];
        }

        $options = [
            CURLOPT_URL                         => $url,
            CURLOPT_HEADER                      => false,
            CURLOPT_POST                        => true,
            CURLOPT_POSTFIELDS                  => $posts,
            CURLOPT_RETURNTRANSFER              => true,
            CURLOPT_HTTPHEADER                  => (count($headers)>0?$headers:[]),
        ];

        // Set the configuration
        curl_setopt_array($handle,$options);

        # Executing the request
        $output = curl_exec($handle);

        # Delay after complete a request
        usleep($config['asana']['api_request_delay']);

        # Getting response code
        $response_code = curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );

        curl_close($handle);

        if ($response_code == 200 || $response_code == 201) {

            # Output the request result
            return $output;

        }
        else {

            $gotostart = false;
            switch ($response_code):

                # Token expired
                case 401:

                    # Counting the number of intents
                    $retries++;

                    require PATH_INITIALIZERS.'session.inc.php';
                    require PATH_INITIALIZERS.'token.inc.php';
                    $gotostart = true;

                    # Updating headers
                    $headers = ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')];

                    if ($retries >= 10) {

                        error('Infinite loop on curl_request_post due to Token expired','Token Expired');

                        # Update lock date expiration
                        $lock_expiration = date('Y-m-d H:i:s', time());
                        db_var('db_sync_lock_expiration', $lock_expiration);

                    }

                break;

                # Too Many Requests
                # The Asana API is currently unavailable.
                case 429:
                case 503:

                    # Wait for a minute
                    sleep(60);

                    # Try again
                    $gotostart = true;

                    break;

                default:

                    # Showing the error since the response code is not known it
                    $response = json_decode($output);
                    error('Response code '.$response_code.' in the request to '.$url.' <br>Response from Asana: '.$response->errors[0]->message,'Error in request (POST)','development');
                    error('There is an issue with data extraction from Asana', 'Data Request Error', 'production');

                    # Update lock date expiration
                    $lock_expiration = date('Y-m-d H:i:s', time());
                    db_var('db_sync_lock_expiration', $lock_expiration);

                    exit;

                    break;

            endswitch;

        } // end if

    }
    while($gotostart);

}


/**
 * @param $url
 * @param array $posts
 * @param array $headers
 * @param boolean $auth_mode
 * @return mixed
 *
 */

function curl_request_delete($url, $posts = [], $headers = []){

    reload:
    global $config;
    $handle = curl_init();

    # Verify if content should be send it
    if (isset($posts['body'])) {
        $posts = $posts['body'];
    }

    $options = [
        CURLOPT_URL                         => $url,
        CURLOPT_HEADER                      => false,
        CURLOPT_POSTFIELDS                  => $posts,
        CURLOPT_CUSTOMREQUEST               => 'DELETE',
        CURLOPT_RETURNTRANSFER              => true,
        CURLOPT_HTTPHEADER                  => (count($headers)>0?$headers:[]),
    ];

    curl_setopt_array($handle, $options);

    $output = curl_exec($handle);
    usleep($config['asana']['api_request_delay']);

    # Getting response code
    $response_code = curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );

    curl_close($handle);

    if ($response_code == 200 || $response_code == 201) {

        # Output the request result
        return $output;

    }
    else {

        switch ( $response_code ):

            # Token expired
            case 401:
                require PATH_INITIALIZERS.'session.inc.php';

            # Too Many Requests
            # The Asana API is currently unavailable.
            case 429:
            case 503:

                # Wait for a minute
                sleep(60);

                # Try again
                goto reload;

                break;

        endswitch;

            $response = json_decode($output);
            error('Response code '.$response_code.' in the request to '.$url.' <br>Response from Asana: '.$response->errors[0]->message,'Error in request (POST)','development');
            error('There is an issue with data extraction from Asana', 'Data Request Error', 'production');
            exit;

    }

}

