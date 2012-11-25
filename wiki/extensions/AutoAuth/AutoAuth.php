<?php

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['ApiAutoAuth'] = $dir . 'ApiAutoAuth.php';
$wgAPIModules['autoauth'] = 'ApiAutoAuth';
