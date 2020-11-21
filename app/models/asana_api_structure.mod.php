<?php


///////////////////////////////////
// ONLY Getting Data Structure from DB
///////////////////////////////////

function model_api_structure_get(){

    # Init vars
    global $config;
    $struct = new stdClass();
    $struct->workspace = [];

    # DB configurations
    $db = new DB_Model('workspace');
    $resp = $db->get('1 = 1', ['gid']);

    foreach($resp as $rowA){

        # Restrict workspace gid
        if ($config['asana']['workspace_gid'] == $rowA['gid']){

            $struct->workspace[$rowA['gid']] = new stdClass();
            $struct->workspace[$rowA['gid']]->gid = $rowA['gid'];

            # Getting Teams
            $db = new DB_Model('team');
            $resp = $db->get('workspace_gid = "'.$struct->workspace[$rowA['gid']]->gid.'"', ['gid' /*, 'name' */]);
            $struct->workspace[$rowA['gid']]->team = [];

            foreach($resp as $rowB){

                $struct->workspace[$rowA['gid']]->team[$rowB['gid']] = new stdClass();
                $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->gid = $rowB['gid'];
                // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->name = $rowB['name'];

                # Getting Projects
                $db = new DB_Model('project');
                $resp = $db->get('team_gid = "'.$struct->workspace[$rowA['gid']]->team[$rowB['gid']]->gid.'"', ['gid' /*, 'name' */]);
                $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project = [];

                foreach($resp as $rowC){

                    $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']] = new stdClass();
                    $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->gid = $rowC['gid'];
                    // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->name = $rowC['name'];

                    # Getting Sections
                    $db = new DB_Model('section');
                    $resp = $db->get('project_gid = "'.$struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->gid.'"', ['gid' /*, 'name' */ ]);
                    $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section = [];

                    foreach($resp as $rowD){

                        $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']] = new stdClass();
                        $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->gid = $rowD['gid'];
                        // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->name = $rowD['name'];

                        # Getting Tasks
                        $db = new DB_Model('task');
                        $resp = $db->get('section_gid = "'.$struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->gid.'"', ['gid' /*, 'name' */ ]);
                        $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task = [];

                        foreach($resp as $rowE){

                            $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']] = new stdClass();
                            $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->gid = $rowE['gid'];
                            // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->name = $rowE['name'];

                            # Getting Story
                            $db = new DB_Model('story');
                            $resp = $db->get('task_gid = "'.$struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->gid.'"', ['gid' /*, 'text' */ ]);
                            $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->story = [];

                            foreach($resp as $rowF){

                                $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->story[$rowF['gid']] = new stdClass();
                                $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->story[$rowF['gid']]->gid = $rowF['gid'];
                                // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->story[$rowF['gid']]->text = $rowF['text'];

                            } // Story

                        } // Task

                    } // Section

                } // Project

            } // Team

        } // if filter

    } // Workspace

    # Return a class with the whole structure
    return $struct;

} // Function





##############################################
## Extract DB Tables structure only using the
## the GID of every record
##############################################

function extract_db_structure_with_gid() {

    # Init vars
    global $config;

    $struct = new stdClass();
    $struct->workspace = [];

    # DB configurations
    $db = new DB_Model('workspace');
    $resp = $db->get('1 = 1', ['gid']);

    foreach($resp as $rowA){

        # Restrict workspace gid
        if ($config['asana']['workspace_gid'] == $rowA['gid']){

            $struct->workspace[$rowA['gid']] = new stdClass();
            $struct->workspace[$rowA['gid']]->gid = $rowA['gid'];

            # Getting Teams
            $db = new DB_Model('team');
            $resp = $db->get('workspace_gid = "'.$struct->workspace[$rowA['gid']]->gid.'"', ['gid' /*, 'name' */]);
            $struct->workspace[$rowA['gid']]->team = [];

            foreach($resp as $rowB){

                $struct->workspace[$rowA['gid']]->team[$rowB['gid']] = new stdClass();
                $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->gid = $rowB['gid'];
                // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->name = $rowB['name'];

                # Getting Projects
                $db = new DB_Model('project');
                $resp = $db->get('team_gid = "'.$struct->workspace[$rowA['gid']]->team[$rowB['gid']]->gid.'"', ['gid' /*, 'name' */]);
                $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project = [];

                foreach($resp as $rowC){

                    $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']] = new stdClass();
                    $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->gid = $rowC['gid'];
                    // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->name = $rowC['name'];

                    # Getting Sections
                    $db = new DB_Model('section');
                    $resp = $db->get('project_gid = "'.$struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->gid.'"', ['gid' /*, 'name' */ ]);
                    $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section = [];

                    foreach($resp as $rowD){

                        $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']] = new stdClass();
                        $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->gid = $rowD['gid'];
                        // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->name = $rowD['name'];

                        # Getting Tasks
                        $db = new DB_Model('task');
                        $resp = $db->get('section_gid = "'.$struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->gid.'"', ['gid' /*, 'name' */ ]);
                        $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task = [];

                        foreach($resp as $rowE){

                            $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']] = new stdClass();
                            $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->gid = $rowE['gid'];
                            // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->name = $rowE['name'];

                            # Getting Story
                            $db = new DB_Model('story');
                            $resp = $db->get('task_gid = "'.$struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->gid.'"', ['gid' /*, 'text' */ ]);
                            $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->story = [];

                            foreach($resp as $rowF){

                                $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->story[$rowF['gid']] = new stdClass();
                                $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->story[$rowF['gid']]->gid = $rowF['gid'];
                                // $struct->workspace[$rowA['gid']]->team[$rowB['gid']]->project[$rowC['gid']]->section[$rowD['gid']]->task[$rowE['gid']]->story[$rowF['gid']]->text = $rowF['text'];

                            } // Story

                        } // Task

                    } // Section

                } // Project

            } // Team

        } // if filter

    } // Workspace

    # Return a class with the whole structure
    return $struct;

} // Function


