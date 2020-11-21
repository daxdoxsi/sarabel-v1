<?php

function error($description, $title = 'Error', $mode = 'development') {
    global $config;

    if ($config['environment'] == $mode) {
        view('system/error_message', ['description' => $description, 'title' => $title]);
    }

    if($mode == 'development') {
        # Info to store in the log file
        $data = date('Y-m-d H:i:s') . '|' . $title . '|' . $description . '|' . $mode . '|' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;

        # Debug Backtrace
        sys_backtrace();

        # Storing the error in a log file
        file_put_contents(PATH_LOGS . 'error_logs_' . date('m-Y') . '.txt', $data, FILE_APPEND | LOCK_EX);
    }
}
