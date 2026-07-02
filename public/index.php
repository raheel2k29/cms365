<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

if (isset($_ENV['VERCEL'])) {
    $storagePath = '/tmp/storage';
    $directories = [
        $storagePath,
        $storagePath . '/app',
        $storagePath . '/framework',
        $storagePath . '/framework/cache',
        $storagePath . '/framework/cache/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/framework/views',
        $storagePath . '/logs'
    ];
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
    $app->useStoragePath($storagePath);
}

$app->handleRequest(Request::capture());
