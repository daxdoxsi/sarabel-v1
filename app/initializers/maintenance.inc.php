<?php


# Checking if parameters has been set
if (
        !is_maintenance() && (get_session('workspace_gid') === false ||
        get_session('team_gid') === false ) && request_uri() != 'asana/parameters'
) {

    header('Location: /asana/parameters');
    exit;

}

