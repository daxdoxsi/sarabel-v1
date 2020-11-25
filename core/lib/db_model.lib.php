<?php

class DB_Model {

    private $table = null;

    /**
     * Model_Base constructor.
     * @param string $table
     */
    public function __construct($table_name)
    {
        $this->table = $table_name;
    }

    public function table($table_name) {
        $this->table = $table_name;
    }

    public function set( $data, $search = null, &$mode = null ) {

        # Init vars
        global $config;
        static $primary_key;
        static $auto_increment;

        # Type definition
        if ( !isset($primary_key) ) {
            $primary_key = [];
            $auto_increment = false;
        }

        # Detecting the field with the primary key and if it has auto_increment
        if ( !isset($primary_key[$this->table]) ) {

            # Getting the primary key
            $resp = db_query("SHOW COLUMNS FROM `".$config['database']['mysql']['name'].'`.`'.table_alias($this->table)."` WHERE `key` = 'PRI'");
            $primary_key[$this->table] = $resp[0]['Field'];

            # If the primary key is not auto_increment then the value should be the info provided
            if ( $resp[0]['Extra'] == 'auto_increment' ) {
                $auto_increment = true;
            }

        } // if

        # Searching for existing records
        if ($search != null){

            # Looking for the $search condition
            $sql = 'SELECT '.$primary_key[$this->table].' FROM `'.$config['database']['mysql']['name'].'`.`'.table_alias($this->table).'` WHERE '.$search;
            $result = db_query($sql);

            if ( count($result) == 1 ) {

                # If the record already exists and is force mode INSERT by param then return the value of primary key
                # field and stop the process
                if ( $mode == 'INSERT' ) {
                    return $result[0][$primary_key[$this->table]];
                }

                # Default mode UPDATE
                $mode = 'UPDATE';

            }
            elseif ( count($result) == 0 ) {

                # If not is forced the mode UPDATE then set the mode to INSERT since none record exists
                if (strtoupper($mode) != 'UPDATE') {

                    # Not record found then Insert the data
                    $mode = 'INSERT';

                }

            }
            else {

                # Multiple records result
                $mode = 'UPDATE';

            }

        }
        else {

            # If not is set the parameter 'condition' then INSERT directly
            $mode = 'INSERT';

        }

        # Formatting SQL fields
        if (is_array($data) && count($data) > 0){

            # Init vars
            $field_list_update = '';
            $field_list_column = '(';
            $field_list_value  = '(';

            foreach($data as $field => $value){

                # Adding comma to the field list
                $field_list_update .= (strlen($field_list_update) > 0 ? ', ' : '' );
                $field_list_column .= (strlen($field_list_column) > 1 ? ', ' : '' );
                $field_list_value  .= (strlen($field_list_value ) > 1 ? ', ' : '' );

                # Insert fields
                $field_list_column .= $field;
                if ($value !== null) {
                    # $value  =  str_replace('\"','"', $value);
                    $value  = addslashes($value);
                    # $field_list_value  .= '"'.str_replace('"','\"', $value).'"';
                    $field_list_value  .= '"'.$value.'"';
                }
                else {
                    $field_list_value  .= 'NULL';
                }

                # Update fields
                $field_list_update .= $field.' = ';
                if ($value != null) {
                    # $value  = str_replace('\"','"', $value);
                    $value  = addslashes($value);
                    $field_list_update .= '"'.$value.'"';
                }
                else {
                    $field_list_update .= 'NULL';
                }
            }
            $field_list_column .= ')';
            $field_list_value  .= ')';
            $field_list_insert  = $field_list_column.' VALUES '.$field_list_value;
        }
        else {
            error('You should provide an array with at least 1 field on the first argument','DB Model Error','Development');
            error('Sorry, there are some issues with the data process', 'System error');
            exit;
        }

        # Storing the information into the database
        switch ($mode) {

            case "INSERT":

                $sql = 'INSERT INTO `'.$config['database']['mysql']['name'].'`.`'.table_alias($this->table).'` '.$field_list_insert;
                $insert_id = db_query($sql);

                if ( $auto_increment === true ) {
                    return $insert_id;
                }
                else {
                    return $data[$primary_key[$this->table]];
                }
                break;

            case "UPDATE":
                $sql = 'UPDATE `'.$config['database']['mysql']['name'].'`.`'.table_alias($this->table).'` SET '.$field_list_update.' WHERE '.$search;
                return db_query($sql);
                break;

        } // switch

    }


    public function get($search = '', $fields = ['*'], $joins = '') {

        # Init vars
        global $config;
        $output = [];

        $sql  = 'SELECT '.implode(',', $fields).' FROM `'.$config['database']['mysql']['name'].'`.`'.table_alias($this->table).'`';
        $sql .= ($joins === '' ? '' : ' '.$joins.' ').( $search === '' ? '' : ' WHERE '.$search);

        # Executing SQL for data extraction
        $return = db_query($sql);

        # Processing output array
        foreach($return as $id => $row) {

            foreach($row as $field => $value) {

                # Remove the slashes previously added with db->set
                $output[$id][$field] = stripslashes($value);

            } # foreach $row

        } # foreach $return

        # Return an updated array
        return $output;

    }

    public function delete($search){

        # Init var
        global $config;

        $sql = 'DELETE FROM `'.$config['database']['mysql']['name'].'`.`'.table_alias($this->table).'` WHERE $search';
        return db_query($sql);

    }

}