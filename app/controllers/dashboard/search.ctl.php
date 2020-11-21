<?php

function search() {
    global $route_params;
    switch ($route_params[0]) {
        case 'team':
            $content_title = 'Global Team';
            break;
        case 'projects':
            $content_title = 'Projects';
            break;
        case 'sections':
            $content_title = 'Sections';
            break;
        case 'tasks':
            $content_title = 'Tasks';
            break;
        case 'stories':
            $content_title = 'Stories';
            break;
        case 'advanced':
            $content_title = 'Advanced';
            break;
        default:
            header('Location: /page-not-found');
            exit;
            break;
    }
    $body = '<h3>Search results:</h3>';
    $content = view('pages/dashboard/search', [ 'content_title' => $content_title, 'content' => $body], true);
    view('templates/asana-dashboard', ['tpl_content' => $content]);
}
