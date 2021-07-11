<?php
namespace App\Core;

class Route
{
    public $controller;
    public $middlewares = [];

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function middleware($class)
    {
        $this->middlewares[] = $class;

        return $this;
    }
}
