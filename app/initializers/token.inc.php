<?php

global $config;

$db = new DB_Model('user');
$user_data = $db->get('gid = "'.get_session('gid').'"');
$user_data = $user_data[0];

# Preparing data to sent by POST
$postData = [
    'grant_type'            => 'refresh_token',
    'client_id'             => $config['asana']['client_id'],
    'client_secret'         => $config['asana']['client_secret'],
    'redirect_uri'          => $config['asana']['redirect_uri'],
    'code'                  => $user_data["code"],
    'refresh_token'         => $user_data["refresh_token"],
    'code_verifier'         => $user_data['code_verifier'],
];

# Sending POST request to Asana Server
$auth = asana_api_request('token', $postData);

# Obtain the user_id from the last request
$user_gid = $auth->data->gid;


# Preparing information to store in session and database
$user_data = [
    'access_token'                      => $auth->access_token,
    'expires_in'                        => date('Y-m-d H:i:s', time() + $auth->expires_in - 600),
    'token_type'                        => $auth->token_type,
];

# Storing information in the app session
set_session($user_data);
set_session('status','active');

# Storing the information into the database
$db->table('user');
$db->set($user_data, 'gid = "'.$user_gid.'"');

