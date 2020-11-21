<?php

# Import all the information of every object in the Asana API
function db_sync_direct_import($full_import = true)
{

    # Init vars
    global $config;
    $updated_at = date('Y-m-d H:i:s');
    $max_exec = ini_get('max_execution_time');
    $top_limit = 100;
    $mode_task_search = true;
    $base_url = $config['asana']['url_base'];
    $struct = new stdClass();

    # Preparing the structure of the counter
    $records_by_tbl = new stdClass();
    $records_by_tbl->workspace = 0;
    $records_by_tbl->tag = 0;
    $records_by_tbl->team = 0;
    $records_by_tbl->user = 0;
    $records_by_tbl->project = 0;
    $records_by_tbl->section = 0;
    $records_by_tbl->task = 0;
    $records_by_tbl->story = 0;

    # Checking if there is another process running at the same time
    if ( db_var('db_sync_lock_expiration') !== null ) {

        # Reading lock expiration
        $lock_expiration = db_var('db_sync_lock_expiration');

        # If the lock file still active stop the process immediately
        if ($lock_expiration > date('Y-m-d H:i:s')) {

            echo "ERROR: ANOTHER DB_SYNC PROCESS IS ALREADY RUNNING";
            exit;

        } else {

            # Update lock date expiration
            $lock_expiration = date('Y-m-d H:i:s', time() + $max_exec);
            db_var('db_sync_lock_expiration', $lock_expiration);

            # Cleaning used variables
            unset($lock_expiration);

        }

    }
    else {

        # Creating a lock file
        $lock_expiration = date('Y-m-d H:i:s', time() + $max_exec);
        db_var('db_sync_lock_expiration', $lock_expiration);

        # Cleaning used variables
        unset($lock_expiration);

    }

    syslogmsg('Starting DB Sync process', null, $records_by_tbl);


    ////////////////////
    ///  'workspace': //
    ////////////////////

    $table = 'workspace';
    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);
    syslogmsg('Mark as null all the records before sync', $table, $records_by_tbl);

    # Mark as null all the records before sync
    $mode = 'UPDATE';
    $db = new DB_Model($table);
    $db->set(['updated_at' => null ], '1 = 1', $mode);
    unset($mode);


    do {

        syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);

        # Request parameters
        $params = [
            'opt_fields' => 'gid,resource_type,name',
            'opt_pretty' => 'false',
            'limit' => $top_limit
        ];

        # Loads the next page
        if ( isset($response->next_page->offset) ) {
            $params['offset'] = $response->next_page->offset;
        }

        # Loading object from the Asana API
        $response = curl_request_get(
            $base_url.'workspaces',
            $params,
            ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
        );

        # Keep track of the records to be processed
        syslogmsg('Information extracted from Asana', $table, $records_by_tbl);
        $records_by_tbl->$table += count($response->data);

        foreach ($response->data as $record) {

            if ( in_array($record->gid, $config['asana']['workspaces_gid']) ) {

                # Inserting the workspace into the database
                $db->set([
                    'gid' => $record->gid,
                    'resource_type' => $record->resource_type,
                    'name' => $record->name,
                    'updated_at' => $updated_at,
                    'status' => '1'
                ], 'gid = "' . $record->gid . '"');

                // Saving the workspace gid for the other tables
                if (!isset($struct->workspace)) {
                    $struct->workspace = [];
                    $struct->workspace[$record->gid] = new stdClass();
                    $struct->workspace[$record->gid]->team = [];
                }
                else {
                    $struct->workspace[$record->gid]->team = [];
                }

            }

            syslogmsg('Information stored in the database', $table, $records_by_tbl);

        } // foreach

    }
    while (
        ( $response->next_page !== null )
    );

    syslogmsg('Mark with status 0 all the NULL records', $table, $records_by_tbl);

    # Mark as deleted (status=0) all the records with updated_at as NULL
    $mode = 'UPDATE';
    $db->set(['status' => '0'], 'updated_at IS NULL', $mode);
    unset($mode);

    syslogmsg('Sync process completed successfully', $table, $records_by_tbl);



    //////////////////
    /// case 'tag': //
    //////////////////

    $table = 'tag';
    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);
    syslogmsg('Mark as null all the records before sync', $table, $records_by_tbl);

    # Mark as null all the records before sync
    $mode = 'UPDATE';
    $db->table($table);
    $db->set(['updated_at' => null ], '1 = 1', $mode);
    unset($mode);


    foreach ( $struct->workspace as $workspace_gid => $teams ) {

        do {

            syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);

            # Request parameters
            $params = [
                'opt_fields' => 'gid,resource_type,created_at,followers,name,color',
                'opt_pretty' => 'false',
                'limit' => $top_limit
            ];

            # Loads the next page
            if ( isset($response->next_page->offset) ) {
                $params['offset'] = $response->next_page->offset;
            }

            # Get the tags info from the API
            $response = curl_request_get(
                $base_url . 'workspaces/' . $workspace_gid . '/tags',
                $params,
                ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
            );

            # Keep track of the records to be processed
            syslogmsg('Information extracted from Asana', $table, $records_by_tbl);
            $records_by_tbl->$table += count($response->data);

            # Storing/Updating the Tags information
            foreach ($response->data as $record) {

                // Save the tag information
                $db->set([
                    'gid' => $record->gid,
                    'color' => $record->color,
                    'created_at' => date_converter($record->created_at),
                    'name' => $record->name,
                    'resource_type' => $record->resource_type,
                    'updated_at' => $updated_at,
                    'status' => '1'
                ], 'gid = "' . $record->gid . '"');

            } // foreach

            syslogmsg('Information stored in the database', $table, $records_by_tbl);

        }
        while(
            ( $response->next_page !== null )
        );

    } // foreach

    syslogmsg('Mark with status 0 all the NULL records', $table, $records_by_tbl);

    # Mark as deleted (status=0) all the records with updated_at as NULL
    $mode = 'UPDATE';
    $db->set(['status' => '0'], 'updated_at IS NULL', $mode);
    unset($mode);

    syslogmsg('Sync process completed successfully', $table, $records_by_tbl);



    ///////////////
    ///  'team': //
    ///////////////

    $table = 'team';
    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);
    syslogmsg('Mark as null all the records before sync', $table, $records_by_tbl);

    # Mark all the updated_at field as NULL before sync
    $mode = 'UPDATE';
    $db->table($table);
    $db->set(['updated_at' => null ], '1 = 1', $mode);
    unset($mode);

    # Workspace_gid cycle
    foreach($struct->workspace as $workspace_gid => $teams ) {

        do {

            syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);

            # Request parameters
            $params = [
                'opt_fields' => 'gid,resource_type,name',
                'opt_pretty' => 'false',
                'limit' => $top_limit
            ];

            # Loads the next page
            if ( isset($response->next_page->offset) ) {
                $params['offset'] = $response->next_page->offset;
            }

            # Get info from the API
            $response = curl_request_get($base_url . 'organizations/'.$workspace_gid.'/teams',
                $params,
                ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
            );

            # Keep track of the records to be processed
            syslogmsg('Information extracted from Asana', $table, $records_by_tbl);
            $records_by_tbl->$table += count($response->data);

            # Storing/Updating the Team information
            foreach ($response->data as $record) {

                # Store a Team record
                $db->set([
                    'gid' => $record->gid,
                    'resource_type' => $record->resource_type,
                    'name' => $record->name,
                    'updated_at' => $updated_at,
                    'workspace_gid' => $workspace_gid,
                    'status' => '1'
                ], 'gid = "' . $record->gid . '"');

                # Recording the team gid
                $struct->workspace[$workspace_gid]->team[$record->gid] = new stdClass();
                $struct->workspace[$workspace_gid]->team[$record->gid]->project = [];



            } // foreach

            syslogmsg('Information stored in the database', $table, $records_by_tbl);

        } // do
        while (
            ( $response->next_page !== null )
        );

    } // foreach workspaces_gid

    syslogmsg('Mark with status 0 all the NULL records', $table, $records_by_tbl);

    # Mark status 0 (deleted) when updated_at is NULL (not processed)
    $mode = 'UPDATE';
    $db->set(['status' => '0'], 'updated_at IS NULL', $mode);
    unset($mode);

    syslogmsg('Sync process completed successfully', $table, $records_by_tbl);


    ////////////////
    ///  'user':  //
    ////////////////

    $table = 'user';
    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);
    syslogmsg('Mark as null all the records before sync', $table, $records_by_tbl);

    # Mark as null all the records before sync
    $mode = 'UPDATE';
    $db->table($table);
    $db->set(['updated_at' => null ], '1 = 1', $mode);
    unset($mode);


    foreach( $struct->workspace as $workspace_gid => $teams ) {

        do {

            syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);

            # Request parameters
            $params = [
                'opt_fields' => 'gid,resource_type,name,email,photo',
                'opt_pretty' => 'false',
                'limit' => $top_limit
            ];

            # Loads the next page
            if ( isset($response->next_page->offset) ) {
                $params['offset'] = $response->next_page->offset;
            }

            $response = curl_request_get(
                $base_url.'workspaces/'.$workspace_gid.'/users',
                $params,
                ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
            );

            # Keep track of the records to be processed
            syslogmsg('Information extracted from Asana', $table, $records_by_tbl);
            $records_by_tbl->$table += count($response->data);

            foreach ($response->data as $record) {

                $db->set([
                    'gid' => $record->gid,
                    'unique_id' => md5($record->gid),
                    'email' => $record->email,
                    'name' => $record->name,
                    'photo' => json_encode($record->photo),
                    'resource_type' => $record->resource_type,
                    'updated_at' => $updated_at,
                    'status' => '1'
                ], 'gid = "' . $record->gid . '"');

            } // foreach

            syslogmsg('Information stored in the database', $table, $records_by_tbl);

        }
        while (
            ( $response->next_page !== null )
        );

    } // foreach workspace_gid

    syslogmsg('Mark with status 0 all the NULL records', $table, $records_by_tbl);

    # Mark as deleted (status=0) all the records with updated_at as NULL
    $mode = 'UPDATE';
    $db->set(['status' => '0'], 'updated_at IS NULL', $mode);
    unset($mode);

    syslogmsg('Sync process completed successfully', $table, $records_by_tbl);



    //////////////////
    ///  'project': //
    //////////////////

    $table = 'project';
    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);
    syslogmsg('Mark as null all the records before sync', $table, $records_by_tbl);

    # DB_Model instance
    $db->table($table);

    # Mark all the updated_at field as NULL before sync
    $mode = 'UPDATE';
    $db->set(['updated_at' => null ], '1 = 1', $mode);
    unset($mode);

    # Workspace_gid cycle
    foreach( $struct->workspace as $workspace_gid => $teams ) {

        foreach ( $teams->team as $team_gid => $projects ) {

            do {

                syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);

                # Request parameters
                $params = [
                    'opt_fields' => 'gid,resource_type,name,archived,created_at,due_on,html_notes,notes,modified_at,public,start_on,followers,owner',
                    'opt_pretty' => 'false',
                    'limit' => $top_limit
                ];

                # Loads the next page
                if ( isset($response->next_page->offset) ) {
                    $params['offset'] = $response->next_page->offset;
                }

                # Get info from the API
                $response = curl_request_get($base_url . 'teams/' . $team_gid . '/projects',
                    $params,
                    ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
                );

                # Keep track of the records to be processed
                syslogmsg('Information extracted from Asana', $table, $records_by_tbl);
                $records_by_tbl->$table += count($response->data);

                # Storing/Updating the Project information
                foreach ($response->data as $record) {

                    # Store Asana information in the database
                    $db->set([
                        'gid' => $record->gid,
                        'resource_type' => $record->resource_type,
                        'name' => $record->name,
                        'archived' => ( $record->archived == false ? 0 : 1 ),
                        'created_at' => date_converter($record->created_at),
                        'due_on' => date_converter($record->due_on),
                        'html_notes' => $record->html_notes,
                        'notes' => $record->notes,
                        'modified_at' => date_converter($record->modified_at),
                        'public' => ($record->public === true ? 1 : 0),
                        'start_on' => date_converter($record->start_on),
                        'followers' => convert_to_commas($record->followers, 'gid'),
                        'owner' => $record->owner->gid,
                        'updated_at' => $updated_at,
                        'workspace_gid' => $workspace_gid,
                        'team_gid' => $team_gid,
                        'status' => '1'
                    ], 'gid = "' . $record->gid . '"');

                    # Storing the project gid
                    $struct->workspace[$workspace_gid]->team[$team_gid]->project[$record->gid] = new stdClass();
                    $struct->workspace[$workspace_gid]->team[$team_gid]->project[$record->gid]->section = [];


                } // endforeach;

                syslogmsg('Information stored in the database', $table, $records_by_tbl);

            } while (
                ( $response->next_page !== null )
            );

        } // endforeach;

    } // endforeach;

    syslogmsg('Mark with status 0 all the NULL records', $table, $records_by_tbl);

    # Mark status 0 (deleted) when updated_at is NULL (not processed)
    $mode = 'UPDATE';
    $db->set(['status' => '0'], 'updated_at IS NULL', $mode);
    unset($mode);

    syslogmsg('Sync process completed successfully', $table, $records_by_tbl);


    ///////////////////
    ///  'section':  //
    ///////////////////

    $table = 'section';
    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);
    syslogmsg('Mark as null all the records before sync', $table, $records_by_tbl);

    # DB_Model instance
    $db->table($table);

    # Mark all the updated_at field as NULL before sync
    $mode = 'UPDATE';
    $db->set(['updated_at' => null ], '1 = 1', $mode);
    unset($mode, $response);

    # Workspace_gid cycle
    foreach( $struct->workspace as $workspace_gid => $teams ) {

        foreach( $teams->team as $team_gid => $projects ) {

            foreach ( $projects->project as $project_gid => $sections ) {


                do {

                    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);

                    # Request parameters
                    $params = [
                        'opt_fields' => 'gid,resource_type,name',
                        'opt_pretty' => 'false',
                        'limit' => $top_limit
                    ];

                    # Loads the next page
                    if (isset($response->next_page->offset)) {
                        $params['offset'] = $response->next_page->offset;
                    }


                    # Get info from the API
                    unset($response);
                    $response = curl_request_get($base_url . 'projects/' . $project_gid . '/sections',
                        $params,
                        ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
                    );

                    # Keep track of the records to be processed
                    syslogmsg('Information extracted from Asana', $table, $records_by_tbl);
                    $records_by_tbl->$table += count($response->data);

                    # Storing/Updating the Team information
                    foreach ($response->data as $record) {

                        # Store the Asana info in the database
                        $db->set([
                            'gid' => $record->gid,
                            'resource_type' => $record->resource_type,
                            'name' => $record->name,
                            'updated_at' => $updated_at,
                            'workspace_gid' => $workspace_gid,
                            'team_gid' => $team_gid,
                            'project_gid' => $project_gid,
                            'visible' => '1',
                            'status' => '1'
                        ], 'gid = "' . $record->gid . '"');

                        # Saving the sections gid
                        $struct->workspace[$workspace_gid]->team[$team_gid]->project[$project_gid]->section[$record->gid]= new stdClass();
                        $struct->workspace[$workspace_gid]->team[$team_gid]->project[$project_gid]->section[$record->gid]->task = [];

                    } // foreach

                    syslogmsg('Information stored in the database', $table, $records_by_tbl);

                } while (
                    ( $response->next_page !== null )
                );

            } // foreach

        } // foreach

    } // foreach

    syslogmsg('Mark with status 0 all the NULL records', $table, $records_by_tbl);

    # Mark status 0 (deleted) when updated_at is NULL (not processed)
    $mode = 'UPDATE';
    $db->set(['status' => '0'], 'updated_at IS NULL', $mode);
    unset($mode);

    syslogmsg('Sync process completed successfully', $table, $records_by_tbl);


    ////////////////
    ///  'task':  //
    ////////////////

    $table = 'task';
    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);
    // syslogmsg('Mark as null all the records before sync', $table, $records_by_tbl);

    # Select the table
    $db->table($table);

    if ( $full_import === true ) {

        /*# Mark all the updated_at field as NULL before sync
        $mode = 'UPDATE';
        $db->set(['updated_at' => null], '1 = 1', $mode);
        unset($mode);*/

    }


    foreach ( $struct->workspace as $workspace_gid => $teams ) {

        foreach ( $teams->team as $team_gid => $projects ) {

            foreach ( $projects->project as $project_gid => $sections ) {

                foreach ( $sections->section as $section_gid => $tasks ) {

                    do {

                        syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);

                        # Preparing to extract the Asana API Information
                        syslogmsg('Preparing to extract the Asana API Information', $table, $records_by_tbl);

                        if ($mode_task_search) {

                            # Params for the task extraction
                            $params = [
                                'opt_fields' => 'permalink_url,completed_by,approval_status,gid,resource_type,resource_subtype,assignee,assignee_status,created_at,completed,completed_at,custom_fields,due_on,due_at,followers,memberships,modified_at,name,notes,html_notes,num_subtasks,parent,projects,start_on,workspace,tags',
                                'opt_pretty' => 'false',
                                'sort_ascending' => 'true',
                                'sections.all' => $section_gid,
                                'limit' => $top_limit,
                            ];

                            # Sync mode full params
                            if ($full_import === true) {

                                # Sort by created_at since it is a full import
                                $params['sort_by'] = 'created_at';

                                if ( isset($response->data[$id_record] ) ) {

                                    # Take the created date-time from previous page
                                    $params['created_at.after'] = date(DATE_ISO8601, strtotime($response->data[$id_record]->created_at ));


                                }
                                else {

                                    # Set the default initial creation date
                                    $params['created_at.after'] = date(DATE_ISO8601, strtotime( $config['asana']['data_since'] ));

                                }

                            } else {

                                # Sorting by modified date-time
                                $params['sort_by'] = 'modified_at';

                                if ( isset($response->data[$id_record]) ) {

                                    # Take the max previous modified date
                                    $params['modified_at.after'] = date(DATE_ISO8601, strtotime($response->data[$id_record]->modified_at));

                                }
                                else {

                                    # Searching the maximum modified date stored in the task table
                                    $result = $db->get('status = 1', ['max(modified_at) AS modified_date']); // SQL OK
                                    $last_modification_at = date(DATE_ISO8601, strtotime($result[0]['modified_date']));
                                    unset($result);

                                    # Refresh mode params
                                    $params['modified_at.after'] = $last_modification_at;

                                }

                            }

                            # Get the info from Asana
                            unset($response);
                            $response = curl_request_get(
                                $base_url . 'workspaces/'.$workspace_gid.'/tasks/search',
                                $params,
                                ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
                            );


                        } // $mode_task_search
                        else {

                            # $mode_task_search === false

                            $params = [
                                'opt_fields' => 'tags,workspace,start_on,projects,parent,num_subtasks,num_likes,html_notes,notes,name,modified_at,memberships,likes,liked,is_rendered_as_separator,followers,external,due_at,due_on,dependents,dependencies,custom_fields,completed_at,completed,created_at,gid,resource_type,resource_subtype,assignee,assignee_status',
                                'limit' => $top_limit,
                                'opt_pretty' => 'false',
                                'project' => $project_gid,
                            ];

                            if ($full_import === true) {

                                $param['modified_since'] = date(DATE_ISO8601, strtotime( $config['asana']['data_since'] ));

                            } else {

                                # Full import === false

                                # Searching the maximum modified date stored in the task table
                                $result = $db->get('status = 1', ['max(modified_at) AS modified_date']); // SQL OK
                                $last_modification_at = date(DATE_ISO8601, strtotime($result[0]['modified_date']));
                                unset($result);
                                $param['modified_since'] = $last_modification_at;

                            }

                            if ($response->next_page !== null) {
                                $params['offset'] = $response->next_page->offset;
                            }

                            # Get the info from Asana
                            unset($response);
                            $response = curl_request_get(
                                $base_url . 'tasks',
                                $params,
                                ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
                            );

                        } // $mode_task_search




                        # Keep track of the records to be processed
                        syslogmsg('Information extracted from Asana', $table, $records_by_tbl);
                        $records_by_tbl->$table += count($response->data);

                        # Storing/Updating the Task information
                        foreach ($response->data as $id_record => $record) {

                            # Save the task information
                            $db->set([
                                'gid' => $record->gid,
                                'approval_status' => $record->approval_status,
                                'completed_by' => $record->completed_by->gid,
                                'permalink_url' => $record->permalink_url,
                                'parent_gid' => $record->parent->gid,
                                'assignee_gid' => $record->assignee->gid,
                                'assignee_status' => $record->assignee->status,
                                'completed' => $record->completed,
                                'completed_at' => date_converter($record->completed_at),
                                'created_at' => date_converter($record->created_at),
                                'custom_task_type' => get_custom_field('task type', $record->custom_fields),
                                'custom_status' => get_custom_field('status', $record->custom_fields),
                                'custom_dev_1' => get_custom_field('dev 1', $record->custom_fields),
                                'custom_dev_2' => get_custom_field('dev 2', $record->custom_fields),
                                'custom_qa_1' => get_custom_field('qa', $record->custom_fields),
                                'custom_qa_2' => get_custom_field('qa 2', $record->custom_fields),
                                'custom_peer_review' => get_custom_field('peer review', $record->custom_fields),
                                'custom_qa_miss' => get_custom_field('qa miss', $record->custom_fields),
                                'custom_corrections_from_qa' => get_custom_field('corrections from qa', $record->custom_fields),
                                'custom_corrections_from_cs' => get_custom_field('corrections from cs', $record->custom_fields),
                                'custom_corrections_from_fc' => get_custom_field('corrections from fc', $record->custom_fields),
                                'custom_cs_edits' => get_custom_field('cs edits', $record->custom_fields),
                                'custom_fc_edits' => get_custom_field('fc edits', $record->custom_fields),
                                'custom_corrections' => get_custom_field('corrections', $record->custom_fields),
                                'custom_modification_notes' => get_custom_field('modification notes', $record->custom_fields),
                                'custom_group' => get_custom_field('group', $record->custom_fields),
                                'custom_hours' => get_custom_field('hours', $record->custom_fields),
                                'dependencies_gid' => convert_to_commas($record->dependencies, 'gid'),
                                'dependents_gid' => convert_to_commas($record->dependents, 'gid'),
                                'due_at' => date_converter($record->due_at),
                                'due_on' => $record->due_on,
                                'followers_gid' => convert_to_commas($record->followers, 'gid'),
                                'followers_name' => convert_to_commas($record->followers, 'gid', $users),
                                'html_notes' => $record->html_notes,
                                'resource_type' => $record->resource_type,
                                'modified_at' => date_converter($record->modified_at),
                                'name' => $record->name,
                                'notes' => $record->notes,
                                'num_subtasks' => $record->num_subtasks,
                                'start_on' => $record->start_on,
                                'tags_gid' => convert_to_commas($record->tags, 'gid'),
                                'tags_name' => convert_to_commas($record->tags, 'gid', $tags),
                                'resource_subtype' => $record->resource_subtype,
                                'updated_at' => $updated_at,
                                'workspace_gid' => $workspace_gid,
                                'team_gid' => $team_gid,
                                'project_gid' => $project_gid,
                                'section_gid' => $section_gid,
                                'status' => '1'
                            ], 'gid = "' . $record->gid . '"');

                            # Saving all the tasks gid
                            $struct->workspace[$workspace_gid]->team[$team_gid]->project[$project_gid]->section[$section_gid]->task[$record->gid] = new stdClass();
                            $struct->workspace[$workspace_gid]->team[$team_gid]->project[$project_gid]->section[$section_gid]->task[$record->gid]->story = [];

                        } // foreach task

                    } while (
                        ( $mode_task_search && count($response->data) === $top_limit) ||
                        (!$mode_task_search && $response->next_page !== null )
                    );

                } // foreach section

            } // foreach project

        } // foreach team

    } // foreach workspace

    file_put_contents(PATH_WRITE.'api_asana.log', "\n\n".date('Y-m-d H:i:s')."=TASK===================\n\n\n".var_dump($response)."\n\n\n=================\n\n", FILE_APPEND);

    syslogmsg('Information stored in the database', $table, $records_by_tbl);
    // syslogmsg('Mark with status 0 all the NULL records', $table, $records_by_tbl);

    if ($full_import === true) {

        /* # Mark status 0 (deleted) when updated_at is NULL (not processed)
        $db->table($table);
        $mode = 'UPDATE';
        $db->set(['status' => '0'], 'updated_at IS NULL', $mode);
        unset($mode);
        */

    }

    syslogmsg('Sync process completed successfully', $table, $records_by_tbl);



    /////////////////
    ///  'story':  //
    /////////////////

    $table = 'story';
    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);
    syslogmsg('Mark as null all the records before sync', $table, $records_by_tbl);

    # DB_Model instance
    $db->table($table);

    /* # Mark all the updated_at field as NULL before sync
    $mode = 'UPDATE';
    $db->set(['updated_at' => null], '1 = 1', $mode);
    unset($mode, $response);
    */

    syslogmsg('Starting the data extraction from Asana', $table, $records_by_tbl);

    foreach ( $struct->workspace as $workspace_gid => $teams ) {

        foreach ( $teams->team as $team_gid => $projects ) {

            foreach ( $projects->project as $project_gid => $sections ) {

                foreach ( $sections->section as $section_gid => $tasks ) {

                    foreach ( $tasks->task as $task_gid => $stories ) {

                        do {

                            # Init vars
                            $params = [
                                'opt_fields' => 'gid,resource_type,created_at,html_text,is_pinned,resource_subtype,text,assignee,created_by,is_edited,project,source,target,task',
                                'opt_pretty' => 'false',
                                'limit' => $top_limit,
                            ];

                            if (isset($response->next_page->offset)) {
                                $params['offset'] = $response->next_page->offset;
                            }

                            # Get info from the API
                            unset($response);
                            syslogmsg('Preparing to extract the information from Asana', $table, $records_by_tbl);
                            $response = curl_request_get(
                                $base_url . 'tasks/' . $task_gid . '/stories',
                                $params,
                                ['Accept: application/json', 'Authorization: Bearer ' . get_session('access_token')],
                            );

                            # Keep track of the records to be processed
                            syslogmsg('Information extracted from Asana', $table, $records_by_tbl);
                            $records_by_tbl->$table += count($response->data);

                            # Storing/Updating the Task information
                            foreach ($response->data as $id_record => $record) {

                                # Save the Story information into the database
                                $db->set([
                                    'gid' => $record->gid,
                                    'created_at' => date_converter($record->created_at),
                                    'created_by' => $record->created_by->gid,
                                    'html_text' => $record->html_text,
                                    'is_pinned' => ($record->is_pinned === true ? 1 : 0),
                                    'is_edited' => ($record->is_edited === true ? 1 : 0),
                                    'text' => $record->text,
                                    'resource_subtype' => $record->resource_subtype,
                                    'resource_type' => $record->resource_type,
                                    'source' => $record->source,
                                    'target' => $record->target->gid,
                                    'workspace_gid' => $workspace_gid,
                                    'team_gid' => $team_gid,
                                    'project_gid' => $project_gid,
                                    'section_gid' => $section_gid,
                                    'task_gid' => $task_gid,
                                    'updated_at' => $updated_at,
                                    'status' => '1'
                                ], 'gid = "' . $record->gid . '"');

                            } // foreach story

                        }
                        while ( $response->next_page !== null );

                    } // foreach task

                } // foreach section

            } // foreach project

        } // foreach team

    } // foreach workspace

    file_put_contents(PATH_WRITE.'api_asana.log', "\n\n".date('Y-m-d H:i:s')."===STORY===================\n\n\n".var_dump($response)."\n\n\n=================\n\n", FILE_APPEND);

    syslogmsg('Information stored in the database', $table, $records_by_tbl);
    syslogmsg('Mark with status 0 all the NULL records', $table, $records_by_tbl);

    /*# Mark status 0 (deleted) when updated_at is NULL (not processed)
    $mode = 'UPDATE';
    $db->set(['status' => '0'], 'updated_at IS NULL', $mode);
    unset($mode, $db);
    */

    syslogmsg('Sync process completed successfully', $table, $records_by_tbl);

    # Update lock date expiration
    $lock_expiration = date('Y-m-d H:i:s', time());
    db_var('db_sync_lock_expiration', $lock_expiration);
    syslogmsg('Lock expiration updated', null, $records_by_tbl);

    syslogmsg('All the DB Tables have been sync successfully with the information from Asana', null, $records_by_tbl);

} // end function

