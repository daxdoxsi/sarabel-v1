<?php

function db_query($sql) {

    static $link;
    global $config;

    # Database connection
    $db = $config['database']['mysql'];
    if (!isset($link)) {
        $link = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);

        if (!$link) {
            error('Unable to connect to MySQL. Error: #'.mysqli_connect_errno().' - '.mysqli_connect_error(),
                'Database Connection error',
                'development');
            error('We are having some issues with the system. Please try again later.',
                'System Error',
                'production');
            exit;
        }
    }

    if ($result = mysqli_query($link, $sql)) {

        # SELECT statement
        if (strtoupper(substr($sql, 0, 6)) == 'SELECT') {
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            mysqli_free_result($result);
            return $rows;
        }

        # INSERT INTO statement
        elseif (strtoupper(substr($sql, 0, 6)) == 'INSERT') {
            return mysqli_insert_id($link);
        }

        # EXPLAIN, SHOW TABLES, SHOW DATABASE, etc.
        elseif (strtoupper(substr($sql, 0, 6)) != 'UPDATE') {
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            mysqli_free_result($result);
            return $rows;
        }
        else {
            return mysqli_affected_rows($link);
        }
    }
    else {
        error("SQL query: ".$sql.' - Error: '.mysqli_error($link),'SQL Error','development');
        error("SQL error: ".$sql,"System Error", 'production');
        exit;
    }

}

function db_var($name, $value = null) {

    # Init vars
    static $save;
    $db = new DB_Model('variable');

    # Getting the var value from memory
    if ( isset($save[$name]) && $value === null ) {
        return $save[$name];
    }

    # Search the variable
    // $row = db_query('SELECT * FROM `variable` WHERE name = "'.$name.'"');
    $row = $db->get('name = "'.$name.'"',['*']);

    if ( count($row) == 1 ) {

        # If the variable already exists in the table
        $row = $row[0];

        if( $value === null ) {

            # Save and Return the value from the database
            $save[$name] = $row['value'];
            return $row['value'];

        }
        else {

            # Save and Update the existing variable with the new value
            $save[$name] = $value;
            //db_query('UPDATE `variable` SET value = "'.$value.'" WHERE name = "'.$name.'"');
            $db->set(['value' => $value], 'name = "'.$name.'"');
            return $value;

        }
    }
    else {

        if ( $value === null ) {

            # If variable not exists
            $save[$name] = null;
            return null;

        }
        else {

            # Creating a new record in the variable table
            //db_query('INSERT INTO `variable` SET name = "'.$name.'", value = "'.$value.'"');
            $db->set(['value' => $value, 'name' => $name], 'name = "'.$name.'"');

            # Return the stored value
            $save[$name] = $value;
            return $value;

        }

    }
}

# Format the table name to accept the table alias
function table_alias($name) {
    list($table, $as, $alias) = explode(' ', trim($name));
    return (trim(strtoupper($as)) == 'AS' ? $table.'` AS `'.$alias : $table);
}