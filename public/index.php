<?php

require_once '../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;
use App\Controllers\BaseController;

\Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->safeLoad();

define("APPLICATION_START", microtime(true));

define("APPLICATION_PATH", __DIR__ . '/../app/');

BaseController::setTemplate("templates/default");

Database::init();
Router::dispatch();
