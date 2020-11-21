<?php

function settings_index() {
    $body = '<h3>Settings main page</h3>';
    $content = view('pages/dashboard/settings/index', ['content' => $body], true);
    view('templates/asana-dashboard', ['content_title' => 'Settings', 'tpl_content' => $content]);
}
