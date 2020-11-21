<?php

function graph(){
    global $route_params;

    # Check if it is not the default route
    if ( request_uri() != 'dashboard' ) {
        $db = new DB_Model('project');
        $project = $db->get('gid = "' . $route_params[0] . '"', ['name']);

        if (count($project) === 0) {
            header('Location: /page-not-found');
            exit;
        }

        $content_title = $project[0]['name'];
    }
    else {
        $content_title = 'Global';
    }

    ################
    # Data Section #
    ################

    # Extract a list of 130 clients Acronyms
    $sql = 'SELECT 
                TRIM(SUBSTRING_INDEX(name,":",1)) as client, 
                COUNT(*) AS qty 
            FROM 
                `task` 
            WHERE 
                name LIKE "%:%" 
            GROUP BY client 
            ORDER BY qty DESC 
            LIMIT 130';


    view('templates/asana-dashboard', [
        'page_title' => 'Dashboard',
        'tpl_content' => view('pages/dashboard/graph', [ 'content_title' => $content_title ], true),
    ]);

}
