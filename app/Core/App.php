<?php

namespace App\Core;

use App\Core\Route;
use App\Core\Session;

class App
{
    public static function run()
    {
        // Start session
        Session::start();

        loadEnv(__DIR__ . '/../../.env');
        Route::handleRequest();
    }
}