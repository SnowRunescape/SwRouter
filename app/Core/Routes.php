<?php

use App\Core\Router;

use App\Controllers\IndexController;
use App\Middlewares\TesteMiddleware;

Router::group(["middleware" => [TesteMiddleware::class]], function () {
    Router::any("/", [IndexController::class]);
});
