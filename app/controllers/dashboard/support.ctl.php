<?php

function support() {

    $body = '<h3>Documentation</h3>';
    view('templates/asana-dashboard', [
        'page_title' => 'Support',
        'tpl_content' => view('pages/dashboard/support', ['body' => $body], true),
    ]);

}
