<?php

$tpl_username = get_session('name');
$tpl_profile_picture = json_decode(get_session('photo'))->image_128x128;

# Notifications
$tpl_notifications_count = '0';
