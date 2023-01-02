<?php

use App\Core\Router;

use App\Controllers\IndexController;

Router::any("/", [IndexController::class]);
