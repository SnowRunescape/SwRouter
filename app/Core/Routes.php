<?php

use App\Controllers\IndexController;
use App\Middlewares\TestMiddleware;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => [TestMiddleware::class]], function () {
    Route::any("/", [IndexController::class, "index"]);
});
