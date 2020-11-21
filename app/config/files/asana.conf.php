<?php

$config['asana'] = [
    'url_base'              => env('asana.url_base'),
    'client_id'             => env('asana.client_id'),
    'client_secret'         => env('asana.client_secret'),
    'redirect_uri'          => env('asana.redirect_uri'),
    'url_authorize'         => env('asana.url_authorize'),
    'url_token'             => env('asana.url_token'),
    'workspaces_gid'        => explode(',', env('asana.workspaces_gid')),
    'webhooks_target_url'   => env('asana.webhooks_target_url'),
    'data_since'            => env('asana.data_since'),
    'api_request_delay'     => env('asana.api_request_delay'),
];
