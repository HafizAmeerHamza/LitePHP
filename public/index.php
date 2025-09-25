<?php

// Composer autoloader (handles App\Core\* and App\Controllers\*)
require_once __DIR__ . '/../vendor/autoload.php';


require_once __DIR__ . '/../app/Helpers/load_env.php';
require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Helpers/functions.php';

// Load routes
require_once '../routes/web.php';

// Define class aliases

use App\Core\App;
use App\Core\View;

class_alias(View::class, 'View');

// Run the application
App::run();
