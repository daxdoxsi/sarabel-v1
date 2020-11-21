<?php

function account() {
    global $config;

    $user = asana_api_request( 'get', ['opt_fields'=>'gid,email,name,photo'], 'users/'.get_session('gid'));
    $team = asana_api_request( 'get', [], 'organizations/'.get_session('workspace_gid').'/teams');

    $body = '<img style="float: left; margin-right: 35px;" alt="Profile Image" src="'.$user->data->photo->image_128x128.'" />';
    $body .= '<h3>'.$user->data->name.'</h3>';
    $body .= '<p><strong>E-mail:</strong> '.$user->data->email.'<br>';
    $body .= '<strong>Team:</strong> '.$team->data[0]->name.'</p>';

    view('templates/asana-dashboard', [
        'page_title' => 'Account',
        'tpl_content' => view('pages/dashboard/account', ['body' => $body], true),
    ]);
}
