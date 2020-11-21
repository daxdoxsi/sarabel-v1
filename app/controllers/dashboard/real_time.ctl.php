<?php

function real_time() {
    global $route_params;
    switch ($route_params[0]) {
        case 'tasks':
            $content_title = 'Tasks';
            break;
        case 'stories':
            $content_title = 'Stories';
            break;
        case 'users':
            $content_title = 'Users';
            break;
        default:
            header('Location: /page-not-found');
            exit;
            break;
    }
    $body = '<h3>Current '.$content_title.':</h3>';
    $content = view('pages/dashboard/real-time', [ 'content_title' => $content_title, 'content' => $body], true);
    view('templates/asana-dashboard', ['tpl_content' => $content]);
}
