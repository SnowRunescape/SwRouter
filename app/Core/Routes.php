<?php
use App\Core\Router;

use App\Controllers\IndexController;

use App\Middlewares\AuthMiddleware;
use App\Middlewares\StorePermissionMiddleware;

Router::get("/", [IndexController::class]);
