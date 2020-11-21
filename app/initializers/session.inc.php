<?php
# Intercepts all the communication with the session
# and encrypt the information
//$key = 'sbxZ2K7jdjdr7485jdj3j2m';
//$handler = new EncryptedSessionHandler($key);
//session_set_save_handler($handler, true);


# Set session vars
if (get_session('status') != 'active') {

    # Retrieve information from the database
    $db = new DB_Model('user');
    $unique_id = get_cookie(md5('unique_id'));
    $user_info = $db->get(
        'unique_id = "' . $unique_id . '"',
        ['user.*', 'ws.gid as workspace_gid', 'ws.name as workspace_name', 'tm.gid as team_gid', 'tm.name as team_name'],
        'INNER JOIN workspace AS ws ON user.workspace_gid = ws.gid ' .
        'INNER JOIN team AS tm ON user.team_gid = tm.gid'
    );

    # Storing information in the session
    if (count($user_info) == 1) {

        # Fill the session with user information
        set_session($user_info[0]);

        # Set the session active
        set_session('status', 'active');

    } else {

        # User is not registered in the system
        set_flash('Welcome again to the Asana API');
        header('Location: /');
        exit;

    }
}

