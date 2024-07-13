<?php

require_once "../vendor/autoload.php";

use App\Core\Bootstrap;
use Illuminate\Container\Container;

Bootstrap::init();

$container = Container::getInstance();

$router = $container->make("router");

$router->dispatch(
    $container->make("Illuminate\Http\Request")
)->send();
