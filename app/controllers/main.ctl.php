<?php


function index(){

    // Cleaning session before continue
    session_start();
    session_destroy();

    $content = view('pages/main/index',[], true);
    view('templates/asana-login', [
        'page_title' => 'Connect with Asana',
        'content'=>$content
    ]);

}

