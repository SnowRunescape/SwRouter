<?php
use App\Core\Router;

use App\Controllers\IndexController;

Router::get("/", [IndexController::class]);
