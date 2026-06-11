<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../system_kredit_pensiun/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../system_kredit_pensiun/vendor/autoload.php';

(require_once __DIR__.'/../system_kredit_pensiun/bootstrap/app.php')
    ->handleRequest(Request::capture());