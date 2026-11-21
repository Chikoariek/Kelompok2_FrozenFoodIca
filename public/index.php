<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Support both standard structure and cPanel subfolder structure
$corePath = file_exists(__DIR__.'/../icafrozenfood/bootstrap/app.php') 
    ? __DIR__.'/../icafrozenfood' 
    : __DIR__.'/..';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $corePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $corePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $corePath.'/bootstrap/app.php';

if ($corePath !== __DIR__.'/..') {
    $app->usePublicPath(__DIR__);
}

$app->handleRequest(Request::capture());
