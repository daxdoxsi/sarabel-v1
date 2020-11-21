<?php


function reports(){

    global $route_params;
    global $config;

    switch ($route_params[0]) {
        case 'email':
            $content_title = 'Email Dev';
            break;
        case 'dev':
            $content_title = 'Web Developers';
            break;
        case 'custom':
            $content_title = 'Custom Projects';
            break;
        default:

            break;
    }

    switch ($route_params[1]) {
        case 'capacity-analysis':
            $content_subtitle = 'Capacity Analysis';
            break;
        case 'summary':
            $content_subtitle = 'Summary';
            break;
        case 'analysis':
            $content_subtitle = 'Analysis';
            break;
        default:
            header('Location: /page-not-found');
            exit;
            break;
    }

    switch ($route_params[0].' - '.$route_params[1]){
        case 'email - capacity-analysis':

            # Init vars
            $content_title = date('M').' Summary - Email Team ';
            $date_start = (isset($_GET['date_start']) ? $_GET['date_start'] : date('Y-m-01 00:00:00') );
            $date_end = (isset($_GET['date_end']) ? $_GET['date_end'] : date('Y-m-d 23:59:59'));

            # Search for project gid
            $db = new DB_Model('project');
            $resp = $db->get('name like "%email%" and status = 1',['gid', 'name']);
            if (count($resp) == 1) {
                $project_gid = $resp[0]['gid'];
                $tpl_project_name = $resp[0]['name'];
            }

            # Global condition for all the queries
            $condition = 'status = 1 AND project_gid = "'.$project_gid.'" AND created_at between "'.$date_start.'" AND "'.$date_end.'"';

            # Looking for task counts
            $db = new DB_Model('task');
            $resp = $db->get($condition,['count(*) AS total']);
            $tpl_total = $resp[0]["total"];

            # Rush Approved
            $resp = $db->get($condition.' AND tags_gid LIKE "%rush approved%"',['count(*) AS rush']);
            $tpl_rush = $resp[0]["rush"];

            # Qty task completed
            $resp = $db->get($condition.' AND completed = 1',['count(*) AS completed']);
            $tpl_completed = $resp[0]["completed"];

            # Qty Corrections
            $resp = $db->get($condition.' AND custom_corrections <> NULL',['count(*) AS corrections']);
            $tpl_corrections = $resp[0]["corrections"];

            # Qty Updates
            $resp = $db->get($condition.' AND (custom_cs_edits <> NULL OR custom_fc_edits <> NULL)',['count(*) AS updates']);
            $tpl_updates = $resp[0]["updates"];

            # Qty QA miss
            $resp = $db->get($condition.' AND custom_qa_miss <> NULL',['count(*) AS qa_miss']);
            $tpl_qa_miss = $resp[0]["qa_miss"];

            # Tasks by Developer 1
            $resp = $db->get(
                $condition.' AND (custom_dev_1 IS NOT NULL)  GROUP BY custom_dev_1 ORDER BY qty DESC',
                ['count(custom_dev_1) AS qty, custom_dev_1 as name']
            );
            $tpl_developers_total = 0;
            foreach($resp as $dev){
                $tpl_developers_total += $dev['qty'];
            }
            $dev_1 = $resp;

            # Tasks by Developer 2
            $resp = $db->get(
                $condition.' AND (custom_dev_2 IS NOT NULL)  GROUP BY custom_dev_2 ORDER BY qty DESC',
                ['count(custom_dev_2) AS qty, custom_dev_2 as name']
            );
            foreach($resp as $dev){
                $tpl_developers_total += $dev['qty'];
            }
            $dev_2 = $resp;

            # Merge Dev1 and Dev2
            $devs = [];
            foreach($dev_1 as $dev) {
                $devs[$dev['name']] = $dev['qty'];
            }
            foreach($dev_2 as $dev) {
                $devs[$dev['name']] += $dev['qty'];
            }
            foreach($devs as $dev => $qty){
                $tpl_developers[] = [
                    'qty' => $qty,
                    'name' => $dev,
                ];
            }

            # Assigned Tasks
            $resp = $db->get(
                $condition.' AND assignee_gid IS NOT NULL GROUP BY assignee_gid ORDER BY qty DESC',
                ['count(assignee_gid) AS qty, assignee_gid']
            );
            $tpl_assigned_total = 0;
            foreach($resp as $idx => $dev){

                # Search for user id
                $db_user = new DB_Model('user');
                $cons = $db_user->get('gid = "'.$dev['assignee_gid'].'"',['name', 'user_role_id']);

                // if ($cons[0]['user_role_id'] == 2) {

                    # Replace gid with user name
                    $resp[$idx]['name'] = $cons[0]['name'];

                    # Sum the quantity of task
                    $tpl_assigned_total += $dev['qty'];

                // }
                // else {
                //     unset($resp[$idx]);
                // }

            }
            $tpl_assigned = $resp;


            # Tasks by Type
            $resp = $db->get(
                $condition.' AND custom_task_type IS NOT NULL GROUP BY custom_task_type ORDER BY qty DESC',
                ['count(custom_task_type) AS qty, custom_task_type as name']
            );
            $tpl_type_total = 0;
            foreach($resp as $type) {
                $tpl_type_total += $type['qty'];
            }
            $tpl_task_types = $resp;

            # Looking for project' sections
            $db = new DB_Model('section');
            $sections = $db->get('status = 1 and project_gid = "'.$project_gid.'"');

            # Counting for tasks classified by section
            $db = new DB_Model('task');
            foreach($sections as $section) {
                $tasks = $db->get(
                    $condition.' AND section_gid = "'.$section['gid'].'"',
                    ['count(*) AS qty']
                );
                $tpl_sections[$section['name']] = $tasks[0]['qty'];
            }

            break;
    }

    view('templates/asana-dashboard', [
        'tpl_content' => view('pages/dashboard/reports',
            [
                'content_title' => 'Report '.$content_title,
                'tpl_total' => $tpl_total,
                'tpl_project_name' => $tpl_project_name,
                'tpl_date_start' => $date_start,
                'tpl_date_end' => $date_end,
                'tpl_sections' => $tpl_sections,
                'tpl_completed' => $tpl_completed,
                'tpl_corrections' => $tpl_corrections,
                'tpl_qa_miss' => $tpl_qa_miss,
                'tpl_updates' => $tpl_updates,
                'tpl_developers' => $tpl_developers,
                'tpl_task_types' => $tpl_task_types,
                'tpl_rush' => $tpl_rush,
                'tpl_developers_total' => $tpl_developers_total,
                'tpl_type_total' => $tpl_type_total,
                'tpl_assigned' => $tpl_assigned,
                'tpl_assigned_total' => $tpl_assigned_total,

            ],
            true),
    ]);

}
