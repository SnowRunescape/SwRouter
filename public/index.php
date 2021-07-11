<?php
require '../vendor/autoload.php';

use App\Core\Router;

use App\Controllers\BaseController;

define("APPLICATION_PATH", __DIR__ . '/../app/');

BaseController::setTemplate("templates/default");

Router::dispatch();
