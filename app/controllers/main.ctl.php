<?php


function index(){

    view('templates/default', [
        'page_title'    => 'Sarabel v1.0',
        'tpl_content'       => view('pages/main/index',[], true),
    ]);

}

