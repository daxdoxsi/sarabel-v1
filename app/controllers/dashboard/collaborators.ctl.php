<?php

function collaborators() {
    global $route_params;
    switch ($route_params[0]) {
        case 'standard-devs':
            $content_title = 'Standard Devs';
            break;
        case 'custom-devs':
            $content_title = 'Custom Devs';
            break;
        case 'email-devs':
            $content_title = 'Email Devs';
            break;
        case 'qa-standard':
            $content_title = 'QA Standard';
            break;
        case 'qa-custom':
            $content_title = 'QA Custom';
            break;
        case 'project-manager':
            $content_title = 'Project Manager';
            break;
        default:
            header('Location: /page-not-found');
            exit;
            break;
    }
    $body = '<h3>Collaborators list</h3>';
    $content = view('pages/dashboard/collaborators', [ 'content_title' => $content_title, 'content' => $body], true);
    view('templates/asana-dashboard', ['tpl_content' => $content]);
}
