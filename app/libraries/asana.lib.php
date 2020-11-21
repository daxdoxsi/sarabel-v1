<?php

function asana_api_request($mode = 'get', $vars = [], $uri = '')
{
    # Init vars
    global $config;
    $headers = ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')];
    $headers_post = ['Content-Type: application/json', 'Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')];
    $vars['opt_pretty'] = 'false';

    switch ($mode) {

        case 'get':

            # Detects if the result should have pagination options
            $pagination = true;
            $segment = explode('/', $uri);
            if ($segment[0] == 'workspaces' && $segment[2] == 'tasks' && $segment[3] = 'search') {
                $pagination = false;
            }

            # Init vars
            $output = new stdClass();
            $output->data = [];
            $real_limit = ( isset($vars['limit']) ? $vars['limit'] : null );
            $top_limit = 100;

            if ( $real_limit === null ) {

                # Result is without limit
                $vars['limit'] = $top_limit;
                $limit_mode = 'unlimited';

            }
            else {

                # Search is limited by parameter
                $vars['limit'] = ( $vars['limit'] > $top_limit ? $top_limit : $vars['limit'] );
                $limit_mode = 'limited';

            }

            do {

                # Performing request to the Asana API
                $response = curl_request_get($config['asana']['url_base'].$uri, $vars, $headers);

                // Just in case the result will be an object
                if (is_object($response->data)) {
                    return $response;
                }

                $count_request = count($response->data);

                if (!isset($total_info)) {
                    $total_info = new stdClass();
                    $total_info->data = [];
                }

                # Grouping all the results
                $total_info->data = array_merge($total_info->data, $response->data);

                # Getting number of records in total
                $count_total = count($total_info->data);

                # If pagination is active then set offset
                if ( $pagination && $limit_mode == 'limited' && $response->next_page !== null ) {
                    $vars['offset'] = $response->next_page->offset;
                    $vars['limit'] = ( ($real_limit - $count_total) > $top_limit ? $top_limit : ($real_limit - $count_total) );
                }

                # Not pagination and limited while $real_limit > $qty_records
                if ( !$pagination && $limit_mode == 'limited' && $real_limit > $count_total ) {
                    $vars['created_at.after'] = $response->data[$count_request - 1]->created_at;
                    $vars['limit'] = ( ($real_limit - $count_total) > $top_limit ? $top_limit : ($real_limit - $count_total) );
                }

                # Pagination enabled and mode unlimited
                if ( $pagination && $limit_mode == 'unlimited' && $response->next_page !== null && $count_request == $top_limit ) {
                    $vars['offset'] = $response->next_page->offset;
                    $vars['limit'] = $top_limit;
                }

                # Not Pagination enabled and mode unlimited
                if ( !$pagination && $limit_mode == 'unlimited' && $count_request == $top_limit) {
                    $vars['created_at.after'] = $response->data[$count_request - 1]->created_at;
                    $vars['limit'] = $top_limit;
                }

            }
            while (
                ( $pagination && $limit_mode == 'unlimited' && $response->next_page !== null ) ||
                ( $pagination && $limit_mode == 'limited' && $count_total < $real_limit) ||

                (!$pagination && $limit_mode == 'unlimited' && $count_request == $top_limit ) ||
                (!$pagination && $limit_mode == 'limited' && $count_total < $real_limit )
            );

            return $total_info;

            break;

        case 'post':
            $resp = curl_request_post($config['asana']['url_base'].$uri, $vars, $headers_post);
            break;

        case 'delete':
            $resp = curl_request_delete($config['asana']['url_base'].$uri, $vars, $headers_post);
            break;

        case 'auth':
            $resp = curl_request_get($config['asana']['url_authorize'], $vars, [], true);
            break;

        case 'token':
            $resp = curl_request_post($config['asana']['url_token'], $vars);
            break;

        default:
            error('Mode not defined on asana_api_request(): '.$mode,'Error in asana_api_request', 'development');
            break;

    };

    $output = json_decode($resp);
    return $output;

}

