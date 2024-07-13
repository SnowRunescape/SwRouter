<?php

require_once "../vendor/autoload.php";

use App\Core\Bootstrap;
use Illuminate\Container\Container;
use Illuminate\Http\Request;

Bootstrap::init();

$container = Container::getInstance();
$request = Request::capture();

$router = $container->make("router");

$router->dispatch($request)->send();
