<?php

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['SpecialMixedLogin'] = $dir . 'SpecialMixedLogin.php';
$wgSpecialPages['MixedLogin'] = 'SpecialMixedLogin';
