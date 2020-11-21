<?php

function main_screen() {

    # Init vars
    global $config;
    $webhooks_info = [];

    if ( is_post() ) {

        if ( isset($_POST['gids']) ) {

            # Delete the hooks from Asana
            $gids = explode(',', $_POST['gids']);

            # Init vars
            $db = new DB_Model('webhook');

            # Run the cycle of gids in order to delete the webhooks selected
            foreach ($gids as $gid) {
                asana_api_request('delete', [], 'webhooks/'.$gid );
                $db->set([
                    'modified_at' => date('Y-m-d H:i:s'),
                    'modified_by' => get_session('user_gid'),
                    'status' => '0'
                ], "gid = '$gid'");
            }

            # Setting the message to display to the user
            set_flash('The hook'.( count($gids) > 1 ? 's' : '' ).' had been deleted from the Asana System');

            # Redirects to the webhooks main screen and finish the script
            header('Location: /dashboard/settings/webhooks');
            exit;

        }

        # Getting the POST vars values
        $name = $_POST['name'];
        $action = $_POST['action'];
        $fields = $_POST['fields'];
        $resource_type = $_POST['resource_type'];
        $project_gid = $_POST['project_gid'];

        # Init vars
        $body = new stdClass();
        $filter = new stdClass();
        $filter->action = $action;
        $filter->fields = explode(',', $fields);
        $filter->resource_type = $resource_type;
        $filter->resource_subtype = 'default_task';
        $body->data = new stdClass();
        $body->data->filters = [];
        $body->data->filters[] = $filter;
        $body->data->resource = $project_gid;
        $body->data->target = $config['asana']['webhooks_target_url'];
        $body = json_encode($body);

        # Creating the new webhook in Asana
        $webhook = asana_api_request('post',[ 'body' => $body ],'webhooks');
        $webhook = $webhook->data;

        # Selecting the webhook table
        $db = new DB_Model('webhook');

        # Storing the webhook in the database
        $db->set([
            'gid' => $webhook->gid,
            'name' => $name,
            'active' => ($webhook->active ? 1 : 0 ),
            'resource_type' => $webhook->resource_type,
            'resource_gid' => $webhook->resource->gid,
            'resource_name' => $webhook->resource->name,
            'created_at' => date_converter($webhook->created_at),
            'created_by' => get_session('user_gid'),
            'filters' => json_encode($webhook->filters),
            'last_failure_at' => date_converter($webhook->last_failure_at),
            'last_failure_content' => $webhook->last_failure_content,
            'last_success_at' => date_converter($webhook->last_success_at),
            'status' => '1',
        ], 'gid = "'.$webhook->gid.'"' );

        # Flash message
        set_flash('The Webhook '.$name.' has been created successfully');

        # Redirects to the webhooks page
        header('Location: /dashboard/settings/webhooks');
        exit;

    }

    # Looking for active webhooks
    $webhooks = asana_api_request('get',['workspace' => $config['asana']['workspace_gid']],'webhooks');

    # Selecting the webhook table
    $db = new DB_Model('webhook');

    # Sync the information from Asana API
    $mode = "UPDATE";
    $db->set(['status' => '0'],'1 = 1', $mode);
    unset($mode);

    # Save the webhooks information in the database
    foreach($webhooks->data as $webhook) {

        $db->set([
            'gid' => $webhook->gid,
            'active' => ($webhook->active ? 1 : 0 ),
            'resource_type' => $webhook->resource_type,
            'resource_gid' => $webhook->resource->gid,
            'resource_name' => $webhook->resource->name,
            'created_at' => date_converter($webhook->created_at),
            'filters' => json_encode($webhook->filters),
            'last_failure_at' => date_converter($webhook->last_failure_at),
            'last_failure_content' => $webhook->last_failure_content ?? null,
            'last_success_at' => date_converter($webhook->last_success_at),
            'status' => 1
        ], 'gid = "'.$webhook->gid.'"' );

        # Getting the full webhook information
        $response = $db->get('gid = "'.$webhook->gid.'" AND status = 1', ['*'] );

        # Select project table
        $db = new DB_Model('project');

        if (count($response) == 1) {
            $webhooks_info[] = $response[0];
        }

    }

    # Listing all the projects for combobox
    $team_gid = get_session('team_gid');
    $db = new DB_Model('project');
    $projects = $db->get('team_gid = "'.$team_gid.'" AND status = 1 ORDER BY name', ['gid','name']);

    view('templates/asana-dashboard',[
        'tpl_content' => view('pages/dashboard/settings/web_hooks', ['webhooks' => $webhooks_info, 'projects' => $projects], true),
        'page_title' => 'Settings - Webhooks'
    ]);

}

function asana_ping() {

    # Init vars
    global $routes;
    $headers = getallheaders();

    # Save or update the variable in the database
    if ( is_post() && isset($headers['X-Hook-Secret']) ) {

        # Check if the variable already exists in the database
        if ( $headers['X-Hook-Secret'] !== db_var('X-Hook-Secret') ) {

            $code = db_var('X-Hook-Secret', $headers['X-Hook-Secret']);

        }
        else {

            $code = $headers['X-Hook-Secret'];

        }

        # Respond to the request with the secret code
        header('X-Hook-Secret: '.$code );
        http_response_code(200);

    }
    else {

        # Page not found
        controller($routes['page-not-found']);

    }

}