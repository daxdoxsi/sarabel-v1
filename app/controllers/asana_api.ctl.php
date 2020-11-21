<?php

function authorize()
{
    global $config;

    $state = rand(1, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
    set_session('state', $state);

    $params = [
        'client_id'                 => $config['asana']['client_id'],
        'redirect_uri'              => $config['asana']['redirect_uri'],
        'response_type'             => 'code',
        'state'                     => $state,
        'code_challenge_method'     => 'S256',
        'code_challenge'            => pkce(),
        'scope'                     => 'default',
    ];

    $url = $config['asana']['url_authorize'].'?'.http_build_query($params);
    header('Location: '.$url);
    exit;

}


function callback()
{
    global $config;

    # Vars GET received
    $code = $_GET['code'];
    $state = $_GET['state'];

    # Validation the state value
    if ($state != get_session('state')){
        error('OAuth state variable validation fails','OAuth Validation Error','development');
        error('We cannot validate the session with Asana.','System Error','production');
        exit;
    }

    # Preparing data to sent by POST
    $postData = [
        'grant_type'            => 'authorization_code',
        'client_id'             => $config['asana']['client_id'],
        'client_secret'         => $config['asana']['client_secret'],
        'redirect_uri'          => $config['asana']['redirect_uri'],
        'code'                  => $code,
        'code_verifier'         => get_session('code_verifier'),
    ];

    # Sending POST request to Asana Server
    $auth = asana_api_request('token', $postData);

    # Obtain the user_id from the last request
    $user_gid = $auth->data->gid;

    # Preparing information to store in session and database
    $user_data = [
        'unique_id'                         => md5($user_gid),
        'gid'                               => $user_gid,
        'email'                             => $auth->data->email,
        'name'                              => $auth->data->name,
        'access_token'                      => $auth->access_token,
        'expires_in'                        => date('Y-m-d H:i:s', time() + $auth->expires_in - 600),
        'refresh_token'                     => $auth->refresh_token,
        'token_type'                        => $auth->token_type,
        'code'                              => $code,
        'code_verifier'                     => get_session('code_verifier'),
        'state'                             => $state,
    ];

    # Storing information in the app session
    set_session($user_data);
    set_session('status','active');

    # Getting information the photo of current user connected
    $user_info = asana_api_request('get', ['opt_fields'=>'photo'],'users/'.$user_gid);

    # Getting information workspace info
    $workspaces_info = asana_api_request('get', ['gid','name','resource_type'],'workspaces');

    # Init DB_Model class
    $db = new DB_Model('workspace');


    foreach ( $workspaces_info->data as $workspace ){


        # Allow to add more workspace id if need it
        if ( in_array($workspace->gid, $config['asana']['workspaces_gid']) ) {

            # Selecting the table workspace
            $db->table('workspace');

            # Saving workspace info into the database
            $resp = $db->set([
                'gid' => $workspace->gid,
                'name' => $workspace->name,
                'resource_type' => $workspace->resource_type,
            ], 'gid = "'.$workspace->gid.'"');

            # Getting information team info
            $teams_info = asana_api_request('get', ['gid','name','resource_type'],'organizations/'.$workspace->gid.'/teams');

            # Selecting the table team
            $db->table('team');

            foreach ( $teams_info->data as $team ) {

                # Saving team info into the database
                $db->set([
                    'gid' => $team->gid,
                    'name' => $team->name,
                    'resource_type' => $team->resource_type,
                    'workspace_gid' => $workspace->gid,
                ], 'gid = "'.$team->gid.'"');

            } // foreach

        } // if

    } // foreach

    // Storing user photo profile in session
    $photos = json_encode($user_info->data[0]->photo);
    set_session('photo', $photos);
    $user_data['photo'] = $photos;

    # Storing the information into the database
    $db->table('user');
    $db->set($user_data, 'gid = "'.$user_gid.'"');

    # Get the workspace and team from the database
    $unique_id = get_cookie(md5('unique_id'));
    $user_info = $db->get(
        'unique_id = "'.$unique_id.'"',
        ['ws.gid as workspace_gid','ws.name as workspace_name','tm.gid as team_gid','tm.name as team_name'],
        'INNER JOIN workspace AS ws ON user.workspace_gid = ws.gid '.
        'INNER JOIN team AS tm ON user.team_gid = tm.gid'
    );

    # Verify if the workspace and team parameters were set
    if ( is_array($user_info) && count($user_info) == 1 ) {
        $user_info = $user_info[0];
        set_session('workspace_gid', $user_info["workspace_gid"]);
        set_session('workspace_name', $user_info["workspace_name"]);
        set_session('team_gid', $user_info["team_gid"]);
        set_session('team_name', $user_info["team_name"]);
    }

    # Creating a new cookie as ID
    set_cookie( md5('unique_id'), md5($user_gid) );

    # Redirect to the main dashboard page
    header('Location: /dashboard');

}

function parameters() {

    global $config;

    # Respond the AJAX request from the parameters form
    if ( is_ajax() && isset($_GET['workspace']) ) {

        # Getting parameters received
        $workspace = $_GET['workspace']; // $config['asana']['workspace_gid'];

        # Getting a list of workspaces
        $response = asana_api_request('get', ['opt_fields' => 'gid,name'],'organizations/'.$workspace.'/teams');

        # Formatting the workspace list
        foreach($response->data as $team) {
            $teams[$team->gid] = $team->name;
        }

        header('Content-Type: application/json');
        echo json_encode($teams);
        exit;

    }

    if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ){

        # Getting a list of workspaces
        $response = asana_api_request('get', ['opt_fields' => 'gid,name'], 'workspaces');

        # Formatting the workspace list
        foreach($response->data as $workspace) {
            if ( in_array($workspace->gid, $config['asana']['workspaces_gid']) ) {
                $workspaces[$workspace->gid] = $workspace->name;
            }
        }

        # Template vars
        $photo = json_decode(get_session('photo'))->image_60x60;
        $username = get_session('name','friend');

        # Loading content template
        $content = view('pages/main/parameters',
            [
                'workspaces' => $workspaces,
                'tpl_profile_picture' => $photo,
                'tpl_username' => $username,
            ], true);

        # Loading main template
        view('templates/asana-login', [
            'page_title'    => 'Parameters selection',
            'content'       => $content,
        ]);

    }
    else {

        # Processing form
        $workspace  = $_POST['workspace'];// $config['asana']['workspaces_gid'];
        $team       = $_POST['team'];
        $unique_id  = get_cookie( md5('unique_id') );

        # Getting workspace info
        $req_workspace = asana_api_request( 'get', ['opt_fields' => 'gid,name'],'workspaces/'.$workspace);

        # Getting Team Info
        $req_team = asana_api_request('get', [ 'opt_fields' => 'gid,name' ], 'teams/'.$team);

        # Getting Projects Info
        $req_project = asana_api_request('get', [ 'opt_fields' => 'gid,name' ], 'teams/'.$team.'/projects' );

        # Formatting Projects
        if ( is_array($req_project->data) && count($req_project->data) > 0 ) {

            foreach($req_project->data as $project) {

                # Getting Project' Sections
                $req_section = asana_api_request('get', [ 'opt_fields' => 'gid,name' ],'projects/'.$project->gid.'/sections');
                $projects[] = [ 'gid' => $project->gid, 'name' => $project->name, 'sections' => $req_section->data ];

            }
        }

        # Storing the information in the database and session
        $db = new DB_Model('workspace');
        $db->set(
            ['gid' => $req_workspace->data[0]->gid, 'name' => $req_workspace->data[0]->name],
            'gid = "'.$req_workspace->data[0]->gid.'"');
        $workspace_gid = $req_workspace->data[0]->gid;

        $db->table('team');
        $db->set(
            ['gid' => $req_team->data[0]->gid, 'name' => $req_team->data[0]->name, 'workspace_gid' => $workspace_gid],
            'gid = "'.$req_team->data[0]->gid.'"');
        $team_gid = $req_team->data[0]->gid;

        $db->table('user');
        $db->set(['workspace_gid' => $workspace_gid, 'team_gid' => $team_gid ], 'unique_id = "'.$unique_id.'"');

        if ( is_array($projects) && count($projects) > 0 ) {
            foreach ( $projects as $project ) {

                # Insert / Update project table
                $db->table('project');
                $project_gid = $db->set(['gid' => $project['gid'],'name' => $project['name'],'team_gid' => $team_gid],
                    'gid = "'.$project['gid'].'"');

                # Insert / Update section table
                if ( is_array($project['sections']) && count($project['sections']) > 0 ) {

                    foreach( $project['sections'] as $section) {

                        # Insert / Update project table
                        $db->table('section');
                        $db->set(['gid' => $section->gid,'name' => $section->name, 'project_gid' => $project_gid],
                            'gid = "'.$section->gid.'"'
                        );

                    }
                }

            }

        }

        # Storing in session
        $info = [
            'workspace_gid' => $req_workspace->data[0]->gid,
            'team_gid' => $req_team->data[0]->gid,
            'workspace_name' => $req_workspace->data[0]->name,
            'team_name' => $req_team->data[0]->name,
        ];
        set_session($info);

        # Redirect to the dashboard
        header('Location: /dashboard');

    }

}

