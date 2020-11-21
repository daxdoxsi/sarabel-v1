<?php


function page_not_found(){
    header("HTTP/1.0 404 Not Found");
    $content = view('system/page_not_found', [], true);
    view('templates/asana-404', ['content' => $content]);
}

function maintenance() {

    # If file exists the maintenance mode is active
    if ( !is_maintenance() ) {

        # Redirects to main page
        header('Location: /dashboard');
        exit;

    }

    // Maintenance mode headers
    header("HTTP/1.1 503 Service Unavailable");
    header("Status: 503 Service Unavailable");
    header("Retry-After: 3600");

    view('system/maintenance_mode', ['page_title' => 'Maintenance Mode']);

}

function logout() {

    # Clear the current session
    session_destroy();

    # Redirects to the homepage
    header('Location: /');

}
