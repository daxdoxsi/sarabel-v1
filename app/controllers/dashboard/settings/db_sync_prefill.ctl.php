<?php


// Getting Data Structure (Only basic structure without details)
// FROM THE ASANA API
function asana_api_structure_scanner()
{

    # Init vars
    global $config;
    $count = 0;
    $db = new DB_Model('workspace');
    #$struct = new stdClass();


    # Workspaces
    $workspaces = asana_api_request('get', ['opt_fields' => 'gid'], 'workspaces');
    $workspaces = $workspaces->data;
    foreach ($workspaces as $workspace) :

        $count++;

        # Allow to preselect multiples workspaces
        if ( in_array($workspace->gid, [ $config['asana']['workspaces_gid'] ] ) ) {

            #$struct->workspace = [];
            #$struct->workspace[$workspace->gid] = new stdClass();
            #$struct->workspace[$workspace->gid]->gid = $workspace->gid;

            $db->table('workspace');
            $db->set(['gid' => $workspace->gid], 'gid = "' . $workspace->gid . '"');

            # Tags
            $tags = asana_api_request('get', ['opt_fields' => 'gid'], 'workspaces/'.$workspace->gid.'/tags');
            $tags = $tags->data;
            foreach ($tags as $tag) :

                $count++;

                #$struct->tag = [];
                #$struct->tag[$tag->gid] = new stdClass();
                #$struct->tag[$tag->gid]->gid = $tag->gid;

                $db->table('tag');
                $db->set([
                    'gid' => $tag->gid,
                ], 'gid = "' . $tag->gid . '"');

            endforeach;
            unset($tags, $tag);

            # Team
            $teams = asana_api_request('get', ['opt_fields' => 'gid'], 'organizations/' . $workspace->gid . '/teams');
            $teams = $teams->data;
            foreach ($teams as $team) :

                $count++;

                #$struct->workspace[$workspace->gid]->team = [];
                #$struct->workspace[$workspace->gid]->team[$team->gid] = new stdClass();
                #$struct->workspace[$workspace->gid]->team[$team->gid]->gid = $team->gid;
                #$struct->workspace[$workspace->gid]->team[$team->gid]->workspace_gid = $workspace->gid;

                $db->table('team');
                $db->set(['gid' => $team->gid, 'workspace_gid' => $workspace->gid], 'gid = "' . $team->gid . '"');

                # Users
                $users = asana_api_request('get', ['opt_fields' => 'gid'], 'teams/' . $team->gid . '/users');
                $users = $users->data;
                foreach ($users as $user) :

                    $count++;

                    #$struct->user = [];
                    #$struct->user[$user->gid] = new stdClass();
                    #$struct->user[$user->gid]->gid = $user->gid;
                    #$struct->user[$user->gid]->unique_id = md5($user->gid);
                    #$struct->user[$user->gid]->workspace_gid = $workspace->gid;
                    #$struct->user[$user->gid]->team_gid = $team->gid;

                    $db->table('user');
                    $db->set([
                        'gid' => $user->gid,
                        'unique_id' => md5($user->gid),
                        'workspace_gid' => $workspace->gid,
                        'team_gid' => $team->gid,
                    ], 'gid = "' . $user->gid . '"');

                endforeach;
                unset($users, $user);


                # Project
                $projects = asana_api_request('get', ['opt_fields' => 'gid'], 'teams/'.$team->gid.'/projects');
                $projects = $projects->data;
                foreach ($projects as $project) :

                    $count++;

                    #$struct->workspace[$workspace->gid]->team[$team->gid]->project = [];
                    #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid] = new stdClass();
                    #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->gid = $project->gid;
                    #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->team_gid = $team->gid;

                    $db->table('project');
                    $db->set(['gid' => $project->gid, 'team_gid' => $team->gid], 'gid = "' . $project->gid . '"');

                    # Section
                    $sections = asana_api_request('get', ['opt_fields' => 'gid'], 'projects/' . $project->gid . '/sections');
                    $sections = $sections->data;
                    foreach ($sections as $section) :

                        $count++;

                        #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section = [];
                        #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid] = new stdClass();
                        #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->gid = $section->gid;
                        #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->project_gid = $project->gid;

                        $db->table('section');
                        $db->set(['gid' => $section->gid, 'project_gid' => $project->gid], 'gid = "' . $section->gid . '"');

                        # Task
                        $tasks = asana_api_request('get', ['opt_fields' => 'gid'], 'sections/' . $section->gid . '/tasks');
                        $tasks = $tasks->data;
                        foreach ($tasks as $task) :

                            $count++;

                            #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task = [];
                            #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid] = new stdClass();
                            #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->gid = $task->gid;
                            #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->project_gid = $project->gid;
                            #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->section_gid = $section->gid;
                            #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->team_gid = $team->gid;
                            #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->workspace_gid = $workspace->gid;

                            $db->table('task');
                            $db->set([
                                'gid' => $task->gid,
                                'project_gid' => $project->gid,
                                'section_gid' => $section->gid,
                                'team_gid' => $team->gid,
                                'workspace_gid' => $workspace->gid,
                            ], 'gid = "' . $task->gid . '"');

                            # Story
                            $stories = asana_api_request('get', ['opt_fields' => 'gid'], 'tasks/' . $task->gid . '/stories');
                            $stories = $stories->data;
                            foreach ($stories as $story) :

                                $count++;

                                #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->story = [];
                                #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->story[$story->gid] = new stdClass();
                                #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->story[$story->gid]->gid = $story->gid;

                                #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->story[$story->gid]->project_gid = $project->gid;
                                #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->story[$story->gid]->task_gid = $task->gid;
                                #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->story[$story->gid]->workspace_gid = $workspace->gid;
                                #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->story[$story->gid]->team_gid = $team->gid;
                                #$struct->workspace[$workspace->gid]->team[$team->gid]->project[$project->gid]->section[$section->gid]->task[$task->gid]->story[$story->gid]->section_gid = $section->gid;

                                $db->table('story');
                                $db->set([
                                    'gid' => $story->gid,
                                    'project_gid' => $project->gid,
                                    'task_gid' => $task->gid,
                                    'workspace_gid' => $workspace->gid,
                                    'team_gid' => $team->gid,
                                    'section_gid' => $section->gid,
                                ], 'gid = "' . $story->gid . '"');

                            endforeach; // story
                            unset($stories, $story);

                        endforeach; // task
                        unset($tasks, $task);

                    endforeach; // section
                    unset($sections, $section);

                endforeach; // project
                unset($projects, $project);

            endforeach; // team
            unset($teams, $team);

        } // if workspace filter

    endforeach; // workspace

    echo "<h3>Total of records: $count</h3>";

    return $count;

}

