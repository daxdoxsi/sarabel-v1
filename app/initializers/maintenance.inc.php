<?php

# Handling maintenance mode
if ( !is_maintenance() ) {

    # Checking Database integrity verification
    $db = new DB_Model('app_tables');
    foreach ($config['database']['integrity_check'] as $table) {

        # Select a table from database.conf.php to validate if has records
        $db->table($table);
        $result = $db->get('1 = 1 LIMIT 1', ['*']);

        # if the table is empty then active maintenance mode
        if (count($result) == 0) {

            # Session handling
            session_start();
            session_destroy();

            # Create a file in order to active the maintenance mode
            db_var('maintenance_mode', 1 );

            # Redirects to maintenance mode page
            header('Location: /maintenance');
            exit;

        }

    } // foreach

    unset($db, $result);

}
else {

    # Validating if the website is in maintenance mode
    if (

        # Omit these URI Paths to avoid the maintenance mode screen
        strstr(request_uri(),'inicio' ) === false &&
        request_uri() != '' # Homepage

    ) {

        header('Location: /maintenance');
        exit;

    }
}
