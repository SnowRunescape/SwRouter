<?php

use App\Core\Application;
use App\Middlewares\ApplicationMiddleware;

return Application::configure()
    ->withRouting([
        "web" => __DIR__ . "/../routes/routes.php",
    ])
    ->withMiddleware([
        "web" => [
            //
        ],
        "api" => [
            //
        ],
    ])->create();
