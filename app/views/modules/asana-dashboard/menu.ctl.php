<?php

# Get the team_id value
$db = new DB_Model('project AS prj');
$projects = $db->get('prj.status = 1 AND tm.gid = '.get_session('team_gid'),
    ['prj.name AS project_name','prj.gid AS project_gid'],
    'INNER JOIN team AS tm on prj.team_gid = tm.gid');

if (is_array($projects) && count($projects) > 0 ) {
    foreach ($projects as $project) {

        $project_name = str_replace('Creative Drive - ', '', $project['project_name']);
        $project_name = substr($project_name, 0, 18);
        $project_name .= (strlen($project_name) == 18 ? '...' : '');

        $tpl_projects[] = [
            'url' => '/dashboard/graph/' . $project['project_gid'],
            'name' => $project_name,
            'icon' => 'simple-icon-pie-chart'
        ];
    }
}
unset($db, $projects, $project, $project_name);