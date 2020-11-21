<?php

#########################################
# DB_Sync getting all the records from
# the Asana Api
#########################################

function db_sync() {
    global $route_params;
    global $config;

    if ( is_ajax() || (isset($_GET['key']) && $_GET['key'] == $config['sync_key']) ) {

        # Log file location and vars init
        $log_file = PATH_WRITE . 'db-sync.log';
        $pid_file = PATH_WRITE . 'db-pid-sync.log';
        $task_completed_msg = '***Completed***';
        $asana_workspace_gid =  $config['asana']['workspaces_gid'];
        $sync_mode = (isset($route_params[1]) && $route_params[1] == 'full' ? 'full' : 'refresh');
        $start_time = time();
        $start_full_sync_from = '2017-01-01T00:00:00'; // For tasks
        $pid_process = getmypid() ;
        $max_execution_time = '7200';


        switch ($route_params[0]) {
            case 'status':

                if ( is_file($log_file) ) {

                    # Reading log file
                    $message = file_get_contents($log_file);

                    # Output the DB-Sync as status
                    header('Content-Type: application/json');

                    if (strstr($message, $task_completed_msg) === false) {
                        echo json_encode([
                            'status' => 'IN PROGRESS',
                            'message' => $message,
                        ]);
                    }
                    else {
                        echo json_encode([
                            'status' => 'COMPLETED',
                            'message' => $message,
                        ]);
                    }
                    exit;

                }
                else {

                    # No data to show in the screen log
                    $message = '';

                    # Output the DB-Sync as status
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'OK',
                        'message' => $message,
                    ]);
                    exit;

                }


                break;

            case 'reset':

                # Resetting the DB_Sync process
                if ( is_file($log_file) ) {
                    unlink($log_file);
                }

                # Stops to the current process ID
                $pid_process = getmypid();
                //system("kill $pid_process");
                posix_kill ( $pid_process , SIGKILL );


                # Removing PID file
                if ( is_file($pid_file) ) {
                    unlink($pid_file);
                }

                # Output the DB-Sync as status
                header('Content-Type: application/json');
                echo json_encode(['status' => 'RESET OK']);
                exit;

                break;


            case 'start':

                # Checking if exists a running process
                if ( is_file($log_file) ) {

                    # Reading log file
                    $message = file_get_contents($log_file);

                    # Check if is completed
                    if ( strstr($message, $task_completed_msg) === false ) {

                        # Output the DB-Sync as status
                        header('Content-Type: application/json');
                        echo json_encode(['status' => 'IN PROGRESS']);
                        exit;

                    }
                    else {

                        # Output the DB-Sync as status
                        header('Content-Type: application/json');
                        echo json_encode(['status' => 'OK']);

                    }

                }
                else {

                    # Output the DB-Sync as status
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'OK']);

                }
                // end if


                # Set the maximum execution time to 120 minutes
                ini_set('max_execution_time', $max_execution_time);
                ini_set('max_input_time', $max_execution_time);

                # Init vars
                $updated_at = date('Y-m-d H:i:s');
                file_put_contents($pid_file, $pid_process);

                # Log
                $log[0] = '<ul>';
                $log[1] = '<li>==========================================================================================</li>';
                $log[2] = '<li><strong>Table:</strong> Workspace | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[3] = '<li>==========================================================================================</li>';
                $log[4] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting DB-Sync...</li>';
                $log[5] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                #####################
                # 1. Workspace sync #
                #####################

                # Init vars
                $workspaces_gid = [];

                # Get info from the API
                $response = asana_api_request('get', [], 'workspaces');

                # Mark as null all the records before sync
                $mode = 'UPDATE';
                $db = new DB_Model('workspace');
                $db->set(['updated_at' => null ], '1 = 1', $mode);
                unset($mode);

                # Run a cycle over all the workspace registered
                foreach ($response->data as $record) {

                    # Extract only the workspace specified in the configuration
                    if ($asana_workspace_gid == $record->gid) {

                        # Inserting the workspace into the database
                        $db->set([
                            'gid' => $record->gid,
                            'resource_type' => $record->resource_type,
                            'name' => $record->name,
                            'updated_at' => $updated_at,
                            'status' => '1'
                        ],'gid = "'.$record->gid.'"', $mode, $update_id);
                        $workspaces_gid[] = $record->gid;

                    } // if
                }

                # Mark as deleted (status=0) all the records with updated_at as NULL
                $mode = 'UPDATE';
                $db->set(['status' => '0'], 'updated_at IS NULL', $mode);

                # Writing Log file with the advance of the sync
                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Workspace | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Workspace table updated successfully</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                ################
                # 2. Team sync #
                ################

                # Writing Log file with the advance of the sync
                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Workspace | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting sync of table Team</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                # Init vars
                $teams_gid = [];

                # Mark all the updated_at field as NULL before sync
                $mode = 'UPDATE';
                $db = new DB_Model('team');
                $db->set(['updated_at' => null ], '1 = 1', $mode);
                unset($mode);

                # Based on the records collect at the end of the Workspace cycle run a foreach
                foreach ($workspaces_gid as $workspace_gid) {


                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Team | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Getting the teams from Asana</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Get info from the API
                    $response = asana_api_request('get', [], 'organizations/' . $workspace_gid . '/teams');

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Team | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Completed request of teams from Asana - '.count($response->data).' records</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Team | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting insert of teams on the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));


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
                        $teams_gid[] = $record->gid;

                    } // end foreach teams information

                } // end foreach $workspaces_gid

                # Mark status 0 (deleted) when updated_at is NULL (not processed)
                $mode = 'UPDATE';
                $db->set(['status' => '0'], 'updated_at IS NULL', $mode);

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Team | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Team table updated successfully</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                ####################
                # 3. Projects sync #
                ####################

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Project | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting with the table Project sync</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                # Clean the update_at value before sync
                $mode = 'UPDATE';
                $db = new DB_Model('project');
                $db->set(['updated_at' => null ], '1 = 1', $mode);
                unset($mode);

                # Running in the cycle of teams
                foreach ($teams_gid as $team_gid) {

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Project | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to get the Projects by Team from Asana</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Get project info from the API
                    $response = asana_api_request('get', [], 'teams/'.$team_gid.'/projects');

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Project | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Projects information extracted from Asana - '.count($response->data).' records</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Project | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to store/update the projects into the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Storing/Updating the Project information
                    foreach ($response->data as $record) {

                        # Save the info in the Project table
                        $db->set([
                            'gid' => $record->gid,
                            'resource_type' => $record->resource_type,
                            'name' => $record->name,
                            'updated_at' => $updated_at,
                            'team_gid' => $team_gid,
                            'status' => '1'
                        ], 'gid = "' . $record->gid . '"');
                        $projects_gid[] = $record->gid;

                    } # foreach project info

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Project | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Projects information has been stored successfully</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                } // end foreach $teams_gid

                # Mark as deleted the out-dated records
                $mode = 'UPDATE';
                $db->set(['status' => '0'], 'updated_at IS NULL', $mode);

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Project | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Projects - All process completed</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                ####################
                # 4. Sections sync #
                ####################

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Section | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting with the table Section...</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                # Clean the update_at value before sync
                $mode = 'UPDATE';
                $db = new DB_Model('section');
                $db->set(['updated_at' => null ], '1 = 1', $mode);
                unset($mode);

                foreach ($projects_gid as $project_gid) {

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Section | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to request the Sections related with every project - '.$project_gid.'</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Get the sections' info from the API
                    $response = asana_api_request('get', [], 'projects/'.$project_gid.'/sections');

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Section | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Sections was extracted from Asana - '.count($response->data).' records</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Section | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to store the Sections in the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Storing/Updating the Section information
                    foreach ($response->data as $record) {

                        // Save the info in the table Section
                        $db->set([
                            'gid' => $record->gid,
                            'resource_type' => $record->resource_type,
                            'name' => $record->name,
                            'updated_at' => $updated_at,
                            'project_gid' => $project_gid,
                            'status' => '1'
                        ], 'gid = "' . $record->gid . '"');
                        $sections_gid[] = $record->gid;

                    } // foreach with section information

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Section | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Sections was stored successfully into the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));


                } // end foreach $projects_gid

                # Mark as deleted the out-dated records
                $mode = 'UPDATE';
                $db->set(['status' => '0'], 'updated_at IS NULL', $mode);

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Section | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> The sync process was completed successfully for the table Section</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                #################
                # 5. Users sync #
                #################

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> User | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting sync process of User</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                # Clean the update_at value before sync
                $mode = 'UPDATE';
                $db = new DB_Model('user');
                $db->set(['updated_at' => null ], '1 = 1', $mode);
                unset($mode);

                # Run the cycle of active workspaces in order to get the users
                foreach ($workspaces_gid as $workspace_gid) {

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> User | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to get the Users from Asana according with the workspace - '.$workspace_gid.'</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Get the user info from the API
                    $response = asana_api_request(
                        'get',
                        ['opt_fields' => 'gid,resource_type,name,email,photo'],
                        'workspaces/' . $workspace_gid . '/users');

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> User | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Users were extracted from Asana successfully - '.count($response->data).' records</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> User | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to store the Users in the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Storing/Updating the Team information
                    foreach ($response->data as $record) {

                        # Save the user information
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
                        $users[$record->gid] = $record->name;

                    } // foreach user information

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> User | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> The Users was stored into the database successfully</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                } // end foreach $workspaces_gid

                # Mark as deleted the out-dated records
                $mode = 'UPDATE';
                $db->set(['status' => '0'], 'updated_at IS NULL', $mode);

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> User | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> All the process were completed in the table Users</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                ################
                # 6. Tags sync #
                ################

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Tags | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting with the Tags sync</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                # Clean the update_at value before sync
                $mode = 'UPDATE';
                $db = new DB_Model('tag');
                $db->set(['updated_at' => null ], '1 = 1', $mode);
                unset($mode);

                # Run the cycle of active workspaces
                foreach ($workspaces_gid as $workspace_gid) {

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Tags | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to get the Tags information from Asana for workspace - '.$workspace_gid.'</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Get the tags info from the API
                    $response = asana_api_request(
                        'get',
                        ['opt_fields' => 'gid,resource_type,created_at,followers,name,color'],
                        'workspaces/' . $workspace_gid . '/tags');

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Tags | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Tags were extracted successfully from Asana - '.count($response->data).' records</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Tags | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to store the Tags into the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

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
                        $tags[$record->gid] = $record->name;

                    }  # endforeach tag information

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Tags | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> The Tags has been stored successfully into the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));


                } # endforeach $workspaces_gid

                # Mark as deleted the out-dated records
                $mode = 'UPDATE';
                $db->set(['status' => '0'], 'updated_at IS NULL', $mode);

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Tags | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> All the process with the Tags sync has been completed</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                #################
                # 7. Tasks sync #
                #################

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting the Task synchronization - Sync mode = '.$sync_mode.'</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                # Init vars
                $tasks_gid = [];
                $db = new DB_Model('task');

                # Sync mode Full parameters
                if ($sync_mode == 'full') {

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Sync mode full - Cleaning updates_at to null</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Clean the update_at value before sync
                    $mode = 'UPDATE';
                    $db->set(['updated_at' => null], '1 = 1', $mode);
                    unset($mode);

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Sync mode full - Updates_at has been cleaned</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                }
                else {

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Sync mode refresh - Looking for record modified most recently in the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Sync mode refresh
                    $result = $db->get('status = 1', ['max(modified_at) AS modified_date']); // SQL OK
                    $last_modification_at = str_replace(' ','T', $result[0]['modified_date']);
                    unset($result);

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Query completed, last modification date is '.$last_modification_at.'</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                }

                # Running the cycle of active Workspace
                foreach ($workspaces_gid as $workspace_gid) {

                    # Navigate throught Section GID array
                    foreach ($sections_gid as $section_gid) {

                        # Params for the task extraction
                        $params = [
                            'opt_fields' => 'permalink_url,completed_by,approval_status,gid,resource_type,resource_subtype,assignee,assignee_status,created_at,completed,completed_at,custom_fields,due_on,due_at,followers,memberships,modified_at,name,notes,html_notes,num_subtasks,parent,projects,start_on,workspace,tags',
                            'sort_ascending' => 'true',
                            'sections.all' => $section_gid,
                        ];

                        # Sync mode full params
                        if ($sync_mode == 'full') {

                            # Full mode params
                            $params['sort_by'] = 'created_at';
                            $params['created_at.after'] = $start_full_sync_from;

                        }
                        else {

                            # Refresh mode params
                            $params['sort_by'] = 'modified_at';
                            $params['modified_at.after'] = $last_modification_at;

                        }

                        array_pop($log);
                        $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                        $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to request the Task records to Asana for the section: '.$section_gid.'</li>';
                        $log[] = '</ul>';
                        file_put_contents($log_file, implode("\n", $log));

                        # Get info from the API
                        $response = asana_api_request(
                            'get',
                            $params,
                            'workspaces/'.$workspace_gid.'/tasks/search');

                        array_pop($log);
                        $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                        $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Task records were extracted from Asana - '.count($response->data).' records from Section '.$section_gid.'</li>';
                        $log[] = '</ul>';
                        file_put_contents($log_file, implode("\n", $log));

                        array_pop($log);
                        $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                        $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Prepared to store the Task records into the database</li>';
                        $log[] = '</ul>';
                        file_put_contents($log_file, implode("\n", $log));

                        # Storing/Updating the Task information
                        foreach ($response->data as $record) {

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
                                'custom_task_type' => get_custom_field('task type',$record->custom_fields),
                                'custom_status' => get_custom_field('status',$record->custom_fields),
                                'custom_dev_1' => get_custom_field('dev 1',$record->custom_fields),
                                'custom_dev_2' => get_custom_field('dev 2',$record->custom_fields),
                                'custom_qa_1' => get_custom_field('qa',$record->custom_fields),
                                'custom_qa_2' => get_custom_field('qa 2',$record->custom_fields),
                                'custom_peer_review' => get_custom_field('peer review',$record->custom_fields),
                                'custom_qa_miss' => get_custom_field('qa miss',$record->custom_fields),
                                'custom_corrections_from_qa' => get_custom_field('corrections from qa',$record->custom_fields),
                                'custom_corrections_from_cs' => get_custom_field('corrections from cs',$record->custom_fields),
                                'custom_corrections_from_fc' => get_custom_field('corrections from fc',$record->custom_fields),
                                'custom_cs_edits' => get_custom_field('cs edits',$record->custom_fields),
                                'custom_fc_edits' => get_custom_field('fc edits',$record->custom_fields),
                                'custom_corrections' => get_custom_field('corrections',$record->custom_fields),
                                'custom_modification_notes' => get_custom_field('modification notes',$record->custom_fields),
                                'custom_group' => get_custom_field('group',$record->custom_fields),
                                'custom_hours' => get_custom_field('hours',$record->custom_fields),
                                'dependencies_gid' => convert_to_commas($record->dependencies,'gid'),
                                'dependents_gid' => convert_to_commas($record->dependents,'gid'),
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
                                'tags_gid' => convert_to_commas($record->tags,'gid'),
                                'tags_name' => convert_to_commas($record->tags,'gid', $tags),
                                'resource_subtype' => $record->resource_subtype,
                                'updated_at' => $updated_at,
                                'project_gid' => convert_to_commas($record->projects,'gid'),
                                'workspace_gid' => $workspace_gid,
                                'section_gid' => $section_gid,
                                'status' => '1'
                            ], 'gid = "' . $record->gid . '"');

                            # Collect all the records inserted or modified
                            $tasks_gid[] = $record->gid;

                        } # endforeach tasks information

                        array_pop($log);
                        $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                        $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Tasks have been stored for the section '.$section_gid.'</li>';
                        $log[] = '</ul>';
                        file_put_contents($log_file, implode("\n", $log));

                    } // end foreach section_gid information

                } // end foreach $projects_gid

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> All Tasks of every section have been inserted successfully into the database</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                if ($sync_mode == 'full') {

                    # Mark as deleted the out-dated records
                    $mode = 'UPDATE';
                    $db->set(['status' => '0'], 'updated_at IS NULL', $mode);

                }

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Task | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Task sync process was completed successfully</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                unset($response, $mode, $db, $record);


                ###################
                # 8. Stories sync #
                ###################

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Story | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Starting the Story synchronization process</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                # Init vars
                $mode = 'INSERT';
                $db = new DB_Model('story');
                unset($params);
                $params = [
                    'opt_fields' => 'gid,resource_type,resource_subtype,created_at,created_by,text,html_text,target,source,story,project,task',
                ];

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Story | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Preparing to request the story information from Asana - '.count($tasks_gid).'</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                foreach ($tasks_gid as $idx => $task_gid) {

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Story | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Request stories related with task '.$task_gid.' - '.$idx.' of '.count($tasks_gid).' ('.($idx * 100 / count($tasks_gid)).'%)</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Get info from the API
                    $response = asana_api_request('get', $params, 'tasks/' . $task_gid . '/stories');

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Story | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Stories extracted from Asana - '.count($response->data).' records for the task '.$task_gid.'</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Story | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> Prepare to save the Stories into the database</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                    # Storing/Updating the Task information
                    foreach ($response->data as $record) {

                        # Save the Story information into the database
                        $db->set([
                            'gid' => $record->gid,
                            'created_at' => date_converter($record->created_at),
                            'created_by' => $record->created_by->gid,
                            'html_text' => $record->html_text,
                            'text' => $record->text,
                            'resource_subtype' => $record->resource_subtype,
                            'resource_type' => $record->resource_type,
                            'source' => $record->source,
                            'target' => $record->target->gid,
                            'project_gid' => $record->project->gid,
                            'task_gid' => $task_gid,
                            'workspace_gid' => $asana_workspace_gid,
                            'updated_at' => $updated_at,
                            'status' => '1'
                        ],  'gid = "' . $record->gid . '"', $mode);


                    } // endforeach story information

                    array_pop($log);
                    $log[2] = '<li><strong>Table:</strong> Story | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                    $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> The Stories for the task '.$task_gid.' were completed</li>';
                    $log[] = '</ul>';
                    file_put_contents($log_file, implode("\n", $log));

                } // end foreach $tasks_gid information

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Story | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> All the tasks were processed and the Stories has been stored into the database</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));

                # Removing
                unset($mode);

                array_pop($log);
                $log[2] = '<li><strong>Table:</strong> Story | <strong>Time:</strong> '.(time() - $start_time).' seconds | <strong>Execution time remaining:</strong> '.($max_execution_time - (time() - $start_time)).' | <strong>Process PID:</strong> '.($pid_process).'</li>';
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> All the sync process related with the table Story has been completed</li>';
                $log[] = '</ul>';
                file_put_contents($log_file, implode("\n", $log));


                # Closing content in log file
                array_pop($log);
                $log[] = '<li><strong>' . date('Y-m-d H:i:s') . ':</strong> ***Completed***</li>';
                $log[] = '</ul>';

                # Storing advance log
                file_put_contents($log_file, implode("\n", $log));

                # Final output
                echo 'OK';

                break;
            default:
                error('Mode not defined on DB-Sync', 'Page not found');
                exit;

        } // endswitch
    }
    else {

        # Page content output
        $body = '';
        $content = view('pages/dashboard/settings/db-sync', ['content' => $body], true);
        view('templates/asana-dashboard', ['content_title' => 'DB-Sync', 'tpl_content' => $content]);

    }
}




