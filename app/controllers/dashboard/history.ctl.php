<?php

function history() {

    $body = '<h3>Display history of events</h3>';
    view('templates/asana-dashboard', [
        'page_title' => 'History',
        'tpl_content' => view('pages/dashboard/history', ['body' => $body], true),
    ]);

}
